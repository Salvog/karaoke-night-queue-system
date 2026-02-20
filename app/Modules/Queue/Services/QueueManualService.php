<?php

namespace App\Modules\Queue\Services;

use App\Models\EventNight;
use App\Models\Participant;
use App\Models\SongRequest;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QueueManualService
{
    public function __construct(private readonly RealtimePublisher $publisher)
    {
    }

    public function addParticipantRequest(EventNight $eventNight, string $displayName, int $songId): SongRequest
    {
        $songRequest = DB::transaction(function () use ($eventNight, $displayName, $songId) {
            $participant = Participant::create([
                'event_night_id' => $eventNight->id,
                'device_cookie_id' => (string) Str::uuid(),
                'join_token_hash' => hash('sha256', Str::random(32)),
                'display_name' => $displayName,
            ]);

            $maxPosition = SongRequest::where('event_night_id', $eventNight->id)->max('position');

            return SongRequest::create([
                'event_night_id' => $eventNight->id,
                'participant_id' => $participant->id,
                'song_id' => $songId,
                'status' => SongRequest::STATUS_QUEUED,
                'position' => ($maxPosition ?? 0) + 1,
            ]);
        });

        $this->publisher->publishQueueUpdated($eventNight);

        return $songRequest;
    }

    public function reorderQueuedRequests(EventNight $eventNight, array $orderedSongRequestIds): void
    {
        DB::transaction(function () use ($eventNight, $orderedSongRequestIds) {
            $orderedIds = collect($orderedSongRequestIds)
                ->map(static fn (mixed $id): int => (int) $id)
                ->values();

            if ($orderedIds->count() !== $orderedIds->unique()->count()) {
                throw ValidationException::withMessages([
                    'ordered_song_request_ids' => 'Ordine coda non valido.',
                ]);
            }

            $activeRequests = SongRequest::where('event_night_id', $eventNight->id)
                ->whereIn('status', [SongRequest::STATUS_QUEUED, SongRequest::STATUS_PLAYING])
                ->lockForUpdate()
                ->get();

            $playingRequests = $activeRequests
                ->where('status', SongRequest::STATUS_PLAYING)
                ->values();

            $queuedRequests = $activeRequests
                ->where('status', SongRequest::STATUS_QUEUED)
                ->values();

            $playingIds = $playingRequests->pluck('id')->map(static fn (mixed $id): int => (int) $id);
            if ($orderedIds->intersect($playingIds)->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'ordered_song_request_ids' => 'La canzone in riproduzione non può essere spostata.',
                ]);
            }

            $queuedIds = $queuedRequests->pluck('id')->map(static fn (mixed $id): int => (int) $id);
            if ($orderedIds->diff($queuedIds)->isNotEmpty() || $queuedIds->diff($orderedIds)->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'ordered_song_request_ids' => 'La coda è cambiata. Ricarica la pagina e riprova.',
                ]);
            }

            $nextPosition = $this->resolveStartingPosition($activeRequests);
            $lockedPlayingPositions = $this->resolveLockedPlayingPositions($playingRequests);
            $queuedById = $queuedRequests->keyBy('id');

            foreach ($orderedIds as $songRequestId) {
                while ($lockedPlayingPositions->has($nextPosition)) {
                    $nextPosition++;
                }

                /** @var SongRequest $songRequest */
                $songRequest = $queuedById->get($songRequestId);

                if ($songRequest->position !== $nextPosition) {
                    $songRequest->update([
                        'position' => $nextPosition,
                    ]);
                }

                $nextPosition++;
            }
        });

        $this->publisher->publishQueueUpdated($eventNight);
    }

    private function resolveLockedPlayingPositions(Collection $playingRequests): Collection
    {
        return $playingRequests
            ->pluck('position')
            ->filter(static fn (mixed $position): bool => $position !== null)
            ->map(static fn (mixed $position): int => (int) $position)
            ->unique()
            ->flip();
    }

    private function resolveStartingPosition(Collection $activeRequests): int
    {
        $minPosition = $activeRequests
            ->pluck('position')
            ->filter(static fn (mixed $position): bool => $position !== null)
            ->map(static fn (mixed $position): int => (int) $position)
            ->min();

        return max(1, (int) ($minPosition ?? 1));
    }
}
