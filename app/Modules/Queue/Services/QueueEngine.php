<?php

namespace App\Modules\Queue\Services;

use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\SongRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QueueEngine
{
    public function startNext(EventNight $eventNight, ?Carbon $now = null): ?SongRequest
    {
        $now = $now ?? now();

        return DB::transaction(function () use ($eventNight, $now) {
            $playbackState = $this->lockPlaybackState($eventNight);
            $nextRequest = $this->findNextQueuedRequest($eventNight);

            if (! $nextRequest) {
                $this->setIdle($playbackState);

                return null;
            }

            return $this->startPlaybackForRequest($eventNight, $playbackState, $nextRequest, $now);
        });
    }

    public function advanceIfNeeded(EventNight $eventNight, ?Carbon $now = null): void
    {
        $now = $now ?? now();

        DB::transaction(function () use ($eventNight, $now) {
            $playbackState = $this->lockPlaybackState($eventNight);

            if ($playbackState->state !== PlaybackState::STATE_PLAYING || ! $playbackState->expected_end_at) {
                return;
            }

            if ($now->lt($playbackState->expected_end_at)) {
                return;
            }

            $currentRequest = $this->lockCurrentRequest($playbackState);

            if (! $currentRequest) {
                $this->setIdle($playbackState);

                return;
            }

            $this->markPlayed($currentRequest, $now);

            $nextRequest = $this->findNextQueuedRequest($eventNight);

            if (! $nextRequest) {
                $this->setIdle($playbackState);

                return;
            }

            $this->startPlaybackForRequest($eventNight, $playbackState, $nextRequest, $now);
        });
    }

    public function skip(EventNight $eventNight, SongRequest $songRequest, ?Carbon $now = null): void
    {
        $now = $now ?? now();

        DB::transaction(function () use ($eventNight, $songRequest, $now) {
            $this->assertSameEvent($eventNight, $songRequest);

            $playbackState = $this->lockPlaybackState($eventNight);
            $lockedRequest = SongRequest::whereKey($songRequest->id)->lockForUpdate()->firstOrFail();

            $lockedRequest->update([
                'status' => SongRequest::STATUS_SKIPPED,
                'played_at' => $now,
            ]);

            if ($playbackState->current_request_id === $lockedRequest->id) {
                $nextRequest = $this->findNextQueuedRequest($eventNight);

                if (! $nextRequest) {
                    $this->setIdle($playbackState);

                    return;
                }

                $this->startPlaybackForRequest($eventNight, $playbackState, $nextRequest, $now);
            }
        });
    }

    public function cancel(EventNight $eventNight, SongRequest $songRequest, ?Carbon $now = null): void
    {
        $now = $now ?? now();

        DB::transaction(function () use ($eventNight, $songRequest, $now) {
            $this->assertSameEvent($eventNight, $songRequest);

            $playbackState = $this->lockPlaybackState($eventNight);
            $lockedRequest = SongRequest::whereKey($songRequest->id)->lockForUpdate()->firstOrFail();

            $lockedRequest->update([
                'status' => SongRequest::STATUS_CANCELED,
            ]);

            if ($playbackState->current_request_id === $lockedRequest->id) {
                $this->setIdle($playbackState);
            }
        });
    }

    public function stop(EventNight $eventNight): void
    {
        DB::transaction(function () use ($eventNight) {
            $playbackState = $this->lockPlaybackState($eventNight);

            $playbackState->fill([
                'state' => PlaybackState::STATE_PAUSED,
                'expected_end_at' => null,
            ])->save();
        });
    }

    public function next(EventNight $eventNight, ?Carbon $now = null): ?SongRequest
    {
        $now = $now ?? now();

        return DB::transaction(function () use ($eventNight, $now) {
            $playbackState = $this->lockPlaybackState($eventNight);

            if ($playbackState->current_request_id) {
                $currentRequest = $this->lockCurrentRequest($playbackState);

                if ($currentRequest) {
                    $this->markPlayed($currentRequest, $now);
                }
            }

            $nextRequest = $this->findNextQueuedRequest($eventNight);

            if (! $nextRequest) {
                $this->setIdle($playbackState);

                return null;
            }

            return $this->startPlaybackForRequest($eventNight, $playbackState, $nextRequest, $now);
        });
    }

    private function lockPlaybackState(EventNight $eventNight): PlaybackState
    {
        $playbackState = PlaybackState::where('event_night_id', $eventNight->id)
            ->lockForUpdate()
            ->first();

        if ($playbackState) {
            return $playbackState;
        }

        return PlaybackState::create([
            'event_night_id' => $eventNight->id,
            'current_request_id' => null,
            'state' => PlaybackState::STATE_IDLE,
        ]);
    }

    private function lockCurrentRequest(PlaybackState $playbackState): ?SongRequest
    {
        if (! $playbackState->current_request_id) {
            return null;
        }

        return SongRequest::whereKey($playbackState->current_request_id)
            ->lockForUpdate()
            ->first();
    }

    private function findNextQueuedRequest(EventNight $eventNight): ?SongRequest
    {
        return SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_QUEUED)
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    private function startPlaybackForRequest(
        EventNight $eventNight,
        PlaybackState $playbackState,
        SongRequest $songRequest,
        Carbon $now
    ): SongRequest {
        $songRequest->loadMissing('song');

        if (! $songRequest->song) {
            throw new ModelNotFoundException('Song not found for request.');
        }

        $durationSeconds = $songRequest->song->duration_seconds + $eventNight->break_seconds;

        $songRequest->update([
            'status' => SongRequest::STATUS_PLAYING,
            'played_at' => null,
        ]);

        $playbackState->fill([
            'state' => PlaybackState::STATE_PLAYING,
            'current_request_id' => $songRequest->id,
            'started_at' => $now,
            'expected_end_at' => $now->copy()->addSeconds($durationSeconds),
        ])->save();

        return $songRequest;
    }

    private function markPlayed(SongRequest $songRequest, Carbon $now): void
    {
        $songRequest->update([
            'status' => SongRequest::STATUS_PLAYED,
            'played_at' => $now,
        ]);
    }

    private function setIdle(PlaybackState $playbackState): void
    {
        $playbackState->fill([
            'state' => PlaybackState::STATE_IDLE,
            'current_request_id' => null,
            'started_at' => null,
            'expected_end_at' => null,
        ])->save();
    }

    private function assertSameEvent(EventNight $eventNight, SongRequest $songRequest): void
    {
        if ($songRequest->event_night_id !== $eventNight->id) {
            throw new InvalidArgumentException('Song request does not belong to event night.');
        }
    }
}
