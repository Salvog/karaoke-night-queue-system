<?php

namespace App\Modules\PublicJoin\Services;

use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\SongRequest;
use Carbon\Carbon;

class RequestEtaService
{
    public function calculateSeconds(EventNight $eventNight, ?Carbon $now = null): int
    {
        $now = $now ?? now();
        $eventNight->loadMissing('playbackState');

        $remainingSeconds = 0;
        $playbackState = $eventNight->playbackState;

        if ($playbackState
            && in_array($playbackState->state, [PlaybackState::STATE_PLAYING, PlaybackState::STATE_BREAK], true)
            && $playbackState->expected_end_at
        ) {
            $remainingSeconds = max(0, $now->diffInSeconds($playbackState->expected_end_at, false));
        }

        $queuedRequests = SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->with('song:id,duration_seconds')
            ->get();

        $queueSeconds = $queuedRequests->sum(function (SongRequest $request) use ($eventNight) {
            $duration = $request->song?->duration_seconds ?? 0;

            return $duration + $eventNight->break_seconds;
        });

        return $remainingSeconds + $queueSeconds;
    }
}
