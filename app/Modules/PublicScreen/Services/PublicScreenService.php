<?php

namespace App\Modules\PublicScreen\Services;

use App\Models\AdBanner;
use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\SongRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicScreenService
{
    public function findLiveEvent(string $eventCode): EventNight
    {
        $eventNight = EventNight::where('code', $eventCode)->firstOrFail();

        if ($eventNight->status !== EventNight::STATUS_ACTIVE) {
            throw new AuthorizationException('Event is not active.');
        }

        return $eventNight;
    }

    public function buildState(EventNight $eventNight): array
    {
        $eventNight->loadMissing(['venue', 'theme', 'adBanner']);
        $queueSnapshot = $this->buildQueueSnapshot($eventNight);

        return [
            'event' => [
                'code' => $eventNight->code,
                'venue' => $eventNight->venue?->name,
                'timezone' => $eventNight->venue?->timezone ?? config('app.timezone', 'Europe/Rome'),
                'starts_at' => $eventNight->starts_at?->toIso8601String(),
                'ends_at' => $eventNight->ends_at?->toIso8601String(),
                'join_pin_required' => ! empty($eventNight->join_pin),
                'request_cooldown_seconds' => (int) $eventNight->request_cooldown_seconds,
                'join_url' => route('public.join.show', $eventNight->code),
                'screen_url' => route('public.screen.show', $eventNight->code),
            ],
            'playback' => $this->buildPlaybackPayload($eventNight, $queueSnapshot),
            'queue' => $this->buildQueuePayload($eventNight, $queueSnapshot),
            'theme' => $this->buildThemePayload($eventNight),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function buildPlaybackPayload(EventNight $eventNight, ?array $queueSnapshot = null): array
    {
        $playbackState = $eventNight->playbackState()->with([
            'currentRequest.song',
            'currentRequest.participant',
        ])->first();

        $queueSnapshot = $queueSnapshot ?? $this->buildQueueSnapshot($eventNight, includeRecent: false);
        $nextQueuedRequest = $queueSnapshot['first_queued_request'] ?? null;
        $progress = $this->buildPlaybackProgress($playbackState);
        $intermission = $this->buildIntermissionPayload($eventNight, $playbackState, $progress, $nextQueuedRequest);

        if (! $playbackState || ! $playbackState->currentRequest || ! $playbackState->currentRequest->song) {
            return [
                'state' => $playbackState?->state ?? PlaybackState::STATE_IDLE,
                'started_at' => $playbackState?->started_at?->toIso8601String(),
                'expected_end_at' => $playbackState?->expected_end_at?->toIso8601String(),
                'song' => null,
                'progress' => $progress,
                'intermission' => $intermission,
            ];
        }

        $song = $playbackState->currentRequest->song;

        return [
            'state' => $playbackState->state,
            'started_at' => $playbackState->started_at?->toIso8601String(),
            'expected_end_at' => $playbackState->expected_end_at?->toIso8601String(),
            'song' => [
                'title' => $song->title,
                'artist' => $song->artist,
                'lyrics' => $song->lyrics,
                'requested_by' => $this->resolveSingerName($playbackState->currentRequest),
            ],
            'progress' => $progress,
            'intermission' => $intermission,
        ];
    }

    public function buildQueuePayload(EventNight $eventNight, ?array $queueSnapshot = null): array
    {
        $nextCount = (int) config('public_screen.queue_next_count', 5);
        $queueSnapshot = $queueSnapshot ?? $this->buildQueueSnapshot($eventNight);
        $nextRequests = $queueSnapshot['next_requests'] instanceof Collection
            ? $queueSnapshot['next_requests']
            : collect();
        $recentRequests = $queueSnapshot['recent_requests'] instanceof Collection
            ? $queueSnapshot['recent_requests']
            : collect();
        $totalPending = is_int($queueSnapshot['total_pending'] ?? null)
            ? $queueSnapshot['total_pending']
            : 0;
        $visibleNextRequests = $nextCount > 0
            ? $nextRequests->take($nextCount)->values()
            : collect();

        return [
            'next' => $visibleNextRequests->map(fn (SongRequest $request) => [
                'id' => $request->id,
                'position' => $request->position,
                'title' => $request->song?->title,
                'artist' => $request->song?->artist,
                'requested_by' => $this->resolveSingerName($request),
            ])->all(),
            'recent' => $recentRequests->map(fn (SongRequest $request) => [
                'id' => $request->id,
                'played_at' => $request->played_at?->toIso8601String(),
                'title' => $request->song?->title,
                'artist' => $request->song?->artist,
                'requested_by' => $this->resolveSingerName($request),
            ])->all(),
            'total_pending' => $totalPending,
        ];
    }

    public function buildThemePayload(EventNight $eventNight): array
    {
        $eventNight->loadMissing(['theme', 'adBanner']);
        $sponsorBanners = AdBanner::where('venue_id', $eventNight->venue_id)
            ->where('is_active', true)
            ->orderBy('title')
            ->get()
            ->sortByDesc(fn (AdBanner $banner) => $banner->id === $eventNight->ad_banner_id)
            ->values();

        return [
            'theme' => $eventNight->theme ? [
                'name' => $eventNight->theme->name,
                'config' => $eventNight->theme->config,
            ] : null,
            'banner' => $eventNight->adBanner ? [
                'title' => $eventNight->adBanner->title,
                'subtitle' => $eventNight->adBanner->subtitle,
                'image_url' => $this->resolveMediaUrl($eventNight->adBanner->image_url),
                'logo_url' => $this->resolveMediaUrl($eventNight->adBanner->logo_url),
                'is_active' => (bool) $eventNight->adBanner->is_active,
            ] : null,
            'sponsor_banners' => $sponsorBanners->map(fn (AdBanner $banner) => [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'image_url' => $this->resolveMediaUrl($banner->image_url),
                'logo_url' => $this->resolveMediaUrl($banner->logo_url),
                'is_active' => (bool) $banner->is_active,
            ])->all(),
            'manager_name' => (string) config('public_screen.global_brand.name', config('app.name', 'Karaoke Night')),
            'manager_logo_url' => $this->resolveMediaUrl((string) config('public_screen.global_brand.logo', '')),
            'background_image_url' => $eventNight->background_image_path
                ? $this->resolvePublicDiskPath($eventNight->background_image_path)
                : null,
            'brand_logo_url' => $eventNight->brand_logo_path
                ? $this->resolvePublicDiskPath($eventNight->brand_logo_path)
                : null,
            'overlay_texts' => $eventNight->overlay_texts ?? [],
        ];
    }

    private function resolveSingerName(SongRequest $request): string
    {
        $displayName = $request->participant?->display_name;

        if ($displayName && trim($displayName) !== '') {
            return trim($displayName);
        }

        return 'Cantante in sala';
    }

    private function buildPlaybackProgress(?PlaybackState $playbackState): array
    {
        $startedAt = $playbackState?->started_at;
        $expectedEndAt = $playbackState?->expected_end_at;

        if (! $startedAt || ! $expectedEndAt) {
            return [
                'elapsed_seconds' => null,
                'remaining_seconds' => null,
                'duration_seconds' => null,
                'percent' => 0,
            ];
        }

        $startedTs = $startedAt->getTimestamp();
        $expectedTs = $expectedEndAt->getTimestamp();

        if ($expectedTs <= $startedTs) {
            return [
                'elapsed_seconds' => null,
                'remaining_seconds' => null,
                'duration_seconds' => null,
                'percent' => 0,
            ];
        }

        $duration = $expectedTs - $startedTs;
        $now = now()->getTimestamp();
        $elapsed = max(0, min($duration, $now - $startedTs));
        $remaining = max(0, $expectedTs - $now);
        $percent = (int) round(($elapsed / $duration) * 100);

        return [
            'elapsed_seconds' => $elapsed,
            'remaining_seconds' => $remaining,
            'duration_seconds' => $duration,
            'percent' => $percent,
        ];
    }

    private function buildIntermissionPayload(
        EventNight $eventNight,
        ?PlaybackState $playbackState,
        array $progress,
        ?SongRequest $nextQueuedRequest = null
    ): array {
        $breakSeconds = max(0, (int) $eventNight->break_seconds);

        if (
            ! $playbackState
            || $playbackState->state !== PlaybackState::STATE_PLAYING
            || ! $playbackState->currentRequest
            || ! $playbackState->currentRequest->song
            || $breakSeconds <= 0
        ) {
            return $this->emptyIntermissionPayload();
        }

        $remainingRaw = $progress['remaining_seconds'] ?? null;
        $remainingSeconds = is_numeric($remainingRaw) ? max(0, (int) $remainingRaw) : null;

        if (
            $remainingSeconds === null
            || $remainingSeconds > $breakSeconds
            || ! $playbackState->expected_end_at
        ) {
            return $this->emptyIntermissionPayload();
        }

        $nextRequest = $nextQueuedRequest;

        if (! $nextRequest) {
            $nextRequest = SongRequest::where('event_night_id', $eventNight->id)
                ->where('status', SongRequest::STATUS_QUEUED)
                ->orderByRaw('position is null')
                ->orderBy('position')
                ->orderBy('id')
                ->with(['song', 'participant'])
                ->first();
        }

        $nextSong = null;

        if ($nextRequest && $nextRequest->song) {
            $nextSong = [
                'title' => $nextRequest->song->title,
                'artist' => $nextRequest->song->artist,
                'requested_by' => $this->resolveSingerName($nextRequest),
            ];
        }

        if (! $nextSong) {
            return $this->emptyIntermissionPayload();
        }

        return [
            'is_active' => true,
            'remaining_seconds' => $remainingSeconds,
            'total_seconds' => $breakSeconds,
            'ends_at' => $playbackState->expected_end_at?->toIso8601String(),
            'next_song' => $nextSong,
        ];
    }

    private function emptyIntermissionPayload(): array
    {
        return [
            'is_active' => false,
            'remaining_seconds' => null,
            'total_seconds' => null,
            'ends_at' => null,
            'next_song' => null,
        ];
    }

    private function buildQueueSnapshot(EventNight $eventNight, bool $includeRecent = true): array
    {
        $nextCount = max(0, (int) config('public_screen.queue_next_count', 5));
        // Keep the first queued request available for intermission logic even when next_count is 0.
        $nextLimit = $includeRecent ? max(1, $nextCount) : 1;

        $nextRequests = SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->with(['song', 'participant'])
            ->limit($nextLimit)
            ->get()
            ->values();

        $snapshot = [
            'next_requests' => $nextRequests,
            'first_queued_request' => $nextRequests->first(),
            'recent_requests' => collect(),
            'total_pending' => 0,
        ];

        if (! $includeRecent) {
            return $snapshot;
        }

        $recentCount = max(0, (int) config('public_screen.queue_recent_count', 5));
        $recentRequests = SongRequest::where('event_night_id', $eventNight->id)
            ->whereIn('status', [SongRequest::STATUS_PLAYED, SongRequest::STATUS_SKIPPED])
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->with(['song', 'participant'])
            ->limit($recentCount)
            ->get()
            ->values();

        $snapshot['recent_requests'] = $recentRequests;
        $snapshot['total_pending'] = SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->count();

        return $snapshot;
    }

    private function resolveMediaUrl(?string $url): ?string
    {
        $value = is_string($url) ? trim($url) : '';
        if ($value === '') {
            return null;
        }

        $value = $this->normalizeAppAbsoluteUrl($value);

        if (Str::startsWith($value, ['http://', 'https://', '//', 'data:', 'blob:'])) {
            return $value;
        }

        $storagePrefix = '/storage/';
        if (Str::startsWith($value, $storagePrefix)) {
            $path = ltrim(Str::after($value, $storagePrefix), '/');

            if ($path !== '' && Storage::disk('public')->exists($path)) {
                return $this->resolvePublicDiskPath($path);
            }
        }

        $relative = ltrim($value, '/');
        if ($relative !== '' && Storage::disk('public')->exists($relative)) {
            return $this->resolvePublicDiskPath($relative);
        }

        return Str::startsWith($value, '/') ? $value : '/' . $value;
    }

    private function resolvePublicDiskPath(string $path): string
    {
        $normalized = ltrim($path, '/');
        return route('public.screen.media', ['path' => $normalized], false);
    }

    private function normalizeAppAbsoluteUrl(string $value): string
    {
        if (! Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $parsed = parse_url($value);
        if (! is_array($parsed)) {
            return $value;
        }

        $path = (string) ($parsed['path'] ?? '/');
        if (! Str::startsWith($path, ['/storage/', '/media/'])) {
            return $value;
        }

        $host = isset($parsed['host']) ? strtolower((string) $parsed['host']) : null;
        if ($host === null || $host === '') {
            return $value;
        }

        $isLoopback = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        $appHost = $this->appUrlHost();

        if (! $isLoopback && ($appHost === null || $host !== $appHost)) {
            return $value;
        }

        $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) && $parsed['fragment'] !== '' ? '#' . $parsed['fragment'] : '';

        return ($path !== '' ? $path : '/') . $query . $fragment;
    }

    private function appUrlHost(): ?string
    {
        $configured = (string) config('app.url', '');
        if ($configured === '') {
            return null;
        }

        $parsed = parse_url($configured);
        if (! is_array($parsed)) {
            return null;
        }

        $host = isset($parsed['host']) ? strtolower((string) $parsed['host']) : null;

        return $host !== '' ? $host : null;
    }
}
