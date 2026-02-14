<?php

namespace App\Modules\PublicScreen\Services;

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
                'join_url' => route('public.join.show', $eventNight->code),
            ],
            'playback' => $this->buildPlaybackPayload($eventNight),
            'queue' => $this->buildQueuePayload($eventNight),
            'theme' => $this->buildThemePayload($eventNight),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function buildPlaybackPayload(EventNight $eventNight): array
    {
        $playbackState = $eventNight->playbackState()->with('currentRequest.song')->first();

        if (! $playbackState || ! $playbackState->currentRequest || ! $playbackState->currentRequest->song) {
            return [
                'state' => $playbackState?->state ?? PlaybackState::STATE_IDLE,
                'started_at' => $playbackState?->started_at?->toIso8601String(),
                'expected_end_at' => $playbackState?->expected_end_at?->toIso8601String(),
                'song' => null,
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
            ],
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
            ->with('song')
            ->limit($nextCount)
            ->get();

        $recentRequests = SongRequest::where('event_night_id', $eventNight->id)
            ->whereIn('status', [SongRequest::STATUS_PLAYED, SongRequest::STATUS_SKIPPED])
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->with('song')
            ->limit($recentCount)
            ->get();

        return [
            'next' => $nextRequests->map(fn (SongRequest $request) => [
                'id' => $request->id,
                'position' => $request->position,
                'title' => $request->song?->title,
                'artist' => $request->song?->artist,
            ])->all(),
            'recent' => $recentRequests->map(fn (SongRequest $request) => [
                'id' => $request->id,
                'played_at' => $request->played_at?->toIso8601String(),
                'title' => $request->song?->title,
                'artist' => $request->song?->artist,
            ])->all(),
        ];
    }

    public function buildThemePayload(EventNight $eventNight): array
    {
        $eventNight->loadMissing(['theme', 'adBanner']);

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
            'background_image_url' => $eventNight->background_image_path
                ? Storage::disk('public')->url($eventNight->background_image_path)
                : null,
            'event_logo_url' => $eventNight->event_logo_path
                ? Storage::disk('public')->url($eventNight->event_logo_path)
                : null,
            'overlay_texts' => $eventNight->overlay_texts ?? [],
        ];
    }
}
