<?php

namespace App\Modules\Queue\Services;

use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\SongRequest;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QueueEngine
{
    public function __construct(private readonly RealtimePublisher $publisher) {}

    public function startNext(EventNight $eventNight, ?Carbon $now = null): ?SongRequest
    {
        $now = $now ?? now();

        $result = DB::transaction(function () use ($eventNight, $now) {
            $playbackState = $this->lockPlaybackState($eventNight);
            $currentRequest = $this->normalizePlaybackState($eventNight, $playbackState);

            if ($playbackState->state === PlaybackState::STATE_PLAYING && $currentRequest) {
                return $currentRequest;
            }

            if ($playbackState->state === PlaybackState::STATE_PAUSED) {
                return $this->resumePausedPlayback($eventNight, $playbackState, $currentRequest, $now);
            }

            return $this->startNextQueuedRequest($eventNight, $playbackState, $now);
        });

        $this->publisher->publishPlaybackUpdated($eventNight);
        $this->publisher->publishQueueUpdated($eventNight);

        return $result;
    }

    public function advanceIfNeeded(EventNight $eventNight, ?Carbon $now = null): void
    {
        $now = $now ?? now();

        $updated = false;

        DB::transaction(function () use ($eventNight, $now, &$updated) {
            $playbackState = $this->lockPlaybackState($eventNight);

            if (! in_array($playbackState->state, [PlaybackState::STATE_PLAYING, PlaybackState::STATE_BREAK], true) || ! $playbackState->expected_end_at) {
                return;
            }

            if ($now->lt($playbackState->expected_end_at)) {
                return;
            }

            if ($playbackState->state === PlaybackState::STATE_BREAK) {
                $this->startNextQueuedRequest($eventNight, $playbackState, $now);
                $updated = true;

                return;
            }

            $currentRequest = $this->lockCurrentRequest($playbackState);

            if (! $currentRequest) {
                $this->setIdle($playbackState);
                $updated = true;

                return;
            }

            $this->markPlayed($currentRequest, $now);

            if ($eventNight->break_seconds > 0) {
                $this->startBreak($playbackState, $now, $eventNight->break_seconds);
                $updated = true;

                return;
            }

            $this->startNextQueuedRequest($eventNight, $playbackState, $now);
            $updated = true;
        });

        if ($updated) {
            $this->publisher->publishPlaybackUpdated($eventNight);
            $this->publisher->publishQueueUpdated($eventNight);
        }
    }

    public function skip(EventNight $eventNight, SongRequest $songRequest, ?Carbon $now = null): void
    {
        $now = $now ?? now();

        $playbackUpdated = false;

        DB::transaction(function () use ($eventNight, $songRequest, $now, &$playbackUpdated) {
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
                    $playbackUpdated = true;

                    return;
                }

                $this->startPlaybackForRequest($playbackState, $nextRequest, $now);
                $playbackUpdated = true;
            }
        });

        $this->publisher->publishQueueUpdated($eventNight);

        if ($playbackUpdated) {
            $this->publisher->publishPlaybackUpdated($eventNight);
        }
    }

    public function cancel(EventNight $eventNight, SongRequest $songRequest, ?Carbon $now = null): void
    {
        $now = $now ?? now();

        $playbackUpdated = false;

        DB::transaction(function () use ($eventNight, $songRequest, &$playbackUpdated) {
            $this->assertSameEvent($eventNight, $songRequest);

            $playbackState = $this->lockPlaybackState($eventNight);
            $lockedRequest = SongRequest::whereKey($songRequest->id)->lockForUpdate()->firstOrFail();

            $lockedRequest->update([
                'status' => SongRequest::STATUS_CANCELED,
            ]);

            if ($playbackState->current_request_id === $lockedRequest->id) {
                $this->setIdle($playbackState);
                $playbackUpdated = true;
            }
        });

        $this->publisher->publishQueueUpdated($eventNight);

        if ($playbackUpdated) {
            $this->publisher->publishPlaybackUpdated($eventNight);
        }
    }

    public function stop(EventNight $eventNight, ?Carbon $now = null): void
    {
        $now = $now ?? now();

        DB::transaction(function () use ($eventNight, $now) {
            $playbackState = $this->lockPlaybackState($eventNight);

            if ($playbackState->state !== PlaybackState::STATE_PLAYING) {
                return;
            }

            $playbackState->fill([
                'state' => PlaybackState::STATE_PAUSED,
                'paused_at' => $now,
            ])->save();
        });

        $this->publisher->publishPlaybackUpdated($eventNight);
    }

    public function resume(EventNight $eventNight, ?Carbon $now = null): ?SongRequest
    {
        $now = $now ?? now();

        $result = DB::transaction(function () use ($eventNight, $now) {
            $playbackState = $this->lockPlaybackState($eventNight);
            $currentRequest = $this->normalizePlaybackState($eventNight, $playbackState);

            if ($playbackState->state !== PlaybackState::STATE_PAUSED) {
                return null;
            }

            return $this->resumePausedPlayback($eventNight, $playbackState, $currentRequest, $now);
        });

        $this->publisher->publishPlaybackUpdated($eventNight);
        $this->publisher->publishQueueUpdated($eventNight);

        return $result;
    }

    public function next(EventNight $eventNight, ?Carbon $now = null): ?SongRequest
    {
        $now = $now ?? now();

        $result = DB::transaction(function () use ($eventNight, $now) {
            $playbackState = $this->lockPlaybackState($eventNight);
            $currentRequest = $this->normalizePlaybackState($eventNight, $playbackState);

            if ($currentRequest) {
                $this->markPlayed($currentRequest, $now);
            }

            return $this->startNextQueuedRequest($eventNight, $playbackState, $now);
        });

        $this->publisher->publishPlaybackUpdated($eventNight);
        $this->publisher->publishQueueUpdated($eventNight);

        return $result;
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

    private function normalizePlaybackState(EventNight $eventNight, PlaybackState $playbackState): ?SongRequest
    {
        $currentRequest = $this->lockCurrentRequest($playbackState);

        $orphanedQuery = SongRequest::where('event_night_id', $eventNight->id)
            ->where('status', SongRequest::STATUS_PLAYING);

        if ($currentRequest) {
            $orphanedQuery->where('id', '!=', $currentRequest->id);
        }

        $orphanedQuery->update([
            'status' => SongRequest::STATUS_QUEUED,
            'played_at' => null,
        ]);

        if (! $currentRequest) {
            if (
                $playbackState->state === PlaybackState::STATE_BREAK
                && $playbackState->expected_end_at
                && now()->lt($playbackState->expected_end_at)
            ) {
                return null;
            }

            if ($playbackState->state !== PlaybackState::STATE_IDLE || $playbackState->current_request_id) {
                $this->setIdle($playbackState);
            }

            return null;
        }

        if ($playbackState->state === PlaybackState::STATE_IDLE) {
            if ($currentRequest->status === SongRequest::STATUS_PLAYING) {
                $currentRequest->update([
                    'status' => SongRequest::STATUS_QUEUED,
                    'played_at' => null,
                ]);
            }

            $this->setIdle($playbackState);

            return null;
        }

        if ($currentRequest->status === SongRequest::STATUS_QUEUED) {
            $currentRequest->update([
                'status' => SongRequest::STATUS_PLAYING,
                'played_at' => null,
            ]);
        } elseif ($currentRequest->status !== SongRequest::STATUS_PLAYING) {
            $this->setIdle($playbackState);

            return null;
        }

        return $currentRequest;
    }

    private function startNextQueuedRequest(
        EventNight $eventNight,
        PlaybackState $playbackState,
        Carbon $now
    ): ?SongRequest {
        $nextRequest = $this->findNextQueuedRequest($eventNight);

        if (! $nextRequest) {
            $this->setIdle($playbackState);

            return null;
        }

        return $this->startPlaybackForRequest($playbackState, $nextRequest, $now);
    }

    private function resumePausedPlayback(
        EventNight $eventNight,
        PlaybackState $playbackState,
        ?SongRequest $currentRequest,
        Carbon $now
    ): ?SongRequest {
        if (! $currentRequest || ! $playbackState->expected_end_at || ! $playbackState->paused_at) {
            if ($currentRequest && $currentRequest->status === SongRequest::STATUS_PLAYING) {
                $currentRequest->update([
                    'status' => SongRequest::STATUS_QUEUED,
                    'played_at' => null,
                ]);
            }

            $this->setIdle($playbackState);

            return $this->startNextQueuedRequest($eventNight, $playbackState, $now);
        }

        // Remaining time must be measured from pause instant to expected end.
        $remainingSeconds = $playbackState->paused_at->diffInSeconds($playbackState->expected_end_at, false);

        if ($remainingSeconds <= 0) {
            $this->markPlayed($currentRequest, $now);
            $this->setIdle($playbackState);

            return $this->startNextQueuedRequest($eventNight, $playbackState, $now);
        }

        $playbackState->fill([
            'state' => PlaybackState::STATE_PLAYING,
            'expected_end_at' => $now->copy()->addSeconds($remainingSeconds),
            'paused_at' => null,
        ])->save();

        return $currentRequest;
    }

    private function startPlaybackForRequest(
        PlaybackState $playbackState,
        SongRequest $songRequest,
        Carbon $now
    ): SongRequest {
        $songRequest->loadMissing('song');

        if (! $songRequest->song) {
            throw new ModelNotFoundException('Song not found for request.');
        }

        $durationSeconds = $songRequest->song->duration_seconds;

        $songRequest->update([
            'status' => SongRequest::STATUS_PLAYING,
            'played_at' => null,
        ]);

        $playbackState->fill([
            'state' => PlaybackState::STATE_PLAYING,
            'current_request_id' => $songRequest->id,
            'started_at' => $now,
            'expected_end_at' => $now->copy()->addSeconds($durationSeconds),
            'paused_at' => null,
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

    private function startBreak(PlaybackState $playbackState, Carbon $now, int $breakSeconds): void
    {
        $playbackState->fill([
            'state' => PlaybackState::STATE_BREAK,
            'current_request_id' => null,
            'started_at' => $now,
            'expected_end_at' => $now->copy()->addSeconds($breakSeconds),
            'paused_at' => null,
        ])->save();
    }

    private function setIdle(PlaybackState $playbackState): void
    {
        $playbackState->fill([
            'state' => PlaybackState::STATE_IDLE,
            'current_request_id' => null,
            'started_at' => null,
            'expected_end_at' => null,
            'paused_at' => null,
        ])->save();
    }

    private function assertSameEvent(EventNight $eventNight, SongRequest $songRequest): void
    {
        if ($songRequest->event_night_id !== $eventNight->id) {
            throw new InvalidArgumentException('Song request does not belong to event night.');
        }
    }
}
