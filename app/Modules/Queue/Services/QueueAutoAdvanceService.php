<?php

namespace App\Modules\Queue\Services;

use App\Models\EventNight;
use App\Models\SongRequest;

class QueueAutoAdvanceService
{
    public function __construct(private readonly QueueEngine $queueEngine)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(EventNight $eventNight): array
    {
        $now = now();

        $this->queueEngine->advanceIfNeeded($eventNight, $now);

        $eventNight->load('playbackState.currentRequest.song');

        $playbackState = $eventNight->playbackState;
        $nextRequest = SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->with('song')
            ->first();

        $historyCount = SongRequest::where('event_night_id', $eventNight->id)
            ->whereIn('status', [
                SongRequest::STATUS_PLAYED,
                SongRequest::STATUS_SKIPPED,
                SongRequest::STATUS_CANCELED,
            ])
            ->count();

        return [
            'playback' => [
                'state' => $playbackState?->state,
                'current' => $playbackState?->currentRequest ? [
                    'id' => $playbackState->currentRequest->id,
                    'title' => $playbackState->currentRequest->song?->title,
                ] : null,
                'next' => $nextRequest ? [
                    'id' => $nextRequest->id,
                    'title' => $nextRequest->song?->title,
                ] : null,
            ],
            'counts' => [
                'current' => $playbackState?->currentRequest ? 1 : 0,
                'next' => SongRequest::where('event_night_id', $eventNight->id)
                    ->where('status', SongRequest::STATUS_QUEUED)
                    ->count(),
                'history' => $historyCount,
            ],
            'timestamps' => [
                'server_now' => $now->toIso8601String(),
                'started_at' => $playbackState?->started_at?->toIso8601String(),
                'expected_end_at' => $playbackState?->expected_end_at?->toIso8601String(),
                'paused_at' => $playbackState?->paused_at?->toIso8601String(),
                'updated_at' => $playbackState?->updated_at?->toIso8601String(),
            ],
        ];
    }
}
