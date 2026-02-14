<?php

namespace App\Modules\PublicScreen\Services;

use App\Models\AdBanner;
use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\SongRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

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
            'playback' => $this->buildPlaybackPayload($eventNight),
            'queue' => $this->buildQueuePayload($eventNight),
            'theme' => $this->buildThemePayload($eventNight),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function buildPlaybackPayload(EventNight $eventNight): array
    {
        $playbackState = $eventNight->playbackState()->with([
            'currentRequest.song',
            'currentRequest.participant',
        ])->first();

        $progress = $this->buildPlaybackProgress($playbackState);

        if (! $playbackState || ! $playbackState->currentRequest || ! $playbackState->currentRequest->song) {
            return [
                'state' => $playbackState?->state ?? PlaybackState::STATE_IDLE,
                'started_at' => $playbackState?->started_at?->toIso8601String(),
                'expected_end_at' => $playbackState?->expected_end_at?->toIso8601String(),
                'song' => null,
                'progress' => $progress,
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
        ];
    }

    public function buildQueuePayload(EventNight $eventNight): array
    {
        $nextCount = (int) config('public_screen.queue_next_count', 5);
        $recentCount = (int) config('public_screen.queue_recent_count', 5);

        $nextRequests = SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->with(['song', 'participant'])
            ->limit($nextCount)
            ->get();

        $recentRequests = SongRequest::where('event_night_id', $eventNight->id)
            ->whereIn('status', [SongRequest::STATUS_PLAYED, SongRequest::STATUS_SKIPPED])
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->with(['song', 'participant'])
            ->limit($recentCount)
            ->get();

        $totalPending = SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->count();

        return [
            'next' => $nextRequests->map(fn (SongRequest $request) => [
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
                'image_url' => $eventNight->adBanner->image_url,
                'logo_url' => $eventNight->adBanner->logo_url,
                'is_active' => (bool) $eventNight->adBanner->is_active,
            ] : null,
            'sponsor_banners' => $sponsorBanners->map(fn (AdBanner $banner) => [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'image_url' => $banner->image_url,
                'logo_url' => $banner->logo_url,
                'is_active' => (bool) $banner->is_active,
            ])->all(),
            'background_image_url' => $eventNight->background_image_path
                ? Storage::disk('public')->url($eventNight->background_image_path)
                : null,
            'brand_logo_url' => $eventNight->brand_logo_path
                ? Storage::disk('public')->url($eventNight->brand_logo_path)
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
}
