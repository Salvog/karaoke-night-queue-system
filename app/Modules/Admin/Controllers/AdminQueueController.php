<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\Song;
use App\Models\SongRequest;
use App\Modules\Auth\Actions\LogAdminAction;
use App\Modules\Auth\DTOs\AdminActionData;
use App\Modules\Queue\Services\QueueAutoAdvanceService;
use App\Modules\Queue\Services\QueueEngine;
use App\Modules\Queue\Services\QueueManualService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminQueueController extends Controller
{
    public function show(Request $request, EventNight $eventNight, QueueAutoAdvanceService $autoAdvance): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');
        $autoAdvance->ensureAdvanced($eventNight);
        $eventNight->loadMissing(['venue', 'playbackState.currentRequest.song']);

        [$queue, $history] = $this->loadQueueCollections($eventNight);

        $songs = Song::orderBy('artist')->orderBy('title')->get();

        return view('admin.queue.show', [
            'eventNight' => $eventNight,
            'queue' => $queue,
            'history' => $history,
            'songs' => $songs,
        ]);
    }

    public function state(Request $request, EventNight $eventNight, QueueAutoAdvanceService $autoAdvance): JsonResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');
        $autoAdvance->ensureAdvanced($eventNight);

        [$queue, $history] = $this->loadQueueCollections($eventNight);
        $playbackState = $this->loadPlaybackState($eventNight);
        $timezone = $this->resolveEventTimezone($eventNight);

        return response()->json([
            'playback' => $this->serializePlayback($eventNight, $playbackState),
            'queue' => [
                'total' => $queue->count(),
                'upcoming' => $queue->map(fn (SongRequest $songRequest) => $this->serializeQueueRequest($songRequest))->values()->all(),
            ],
            'history' => $history->map(fn (SongRequest $songRequest) => $this->serializeHistoryRequest($songRequest, $timezone))->values()->all(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function skip(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'song_request_id' => ['nullable', 'integer'],
        ]);

        $songRequestId = $data['song_request_id'] ?? $eventNight->playbackState?->current_request_id;

        if (! $songRequestId) {
            return back()->withErrors(['song_request_id' => 'Select a request to skip.']);
        }

        $songRequest = $eventNight->songRequests()->whereKey($songRequestId)->firstOrFail();

        $queueEngine->skip($eventNight, $songRequest);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.skip',
            subjectType: SongRequest::class,
            subjectId: (string) $songRequest->id,
            metadata: [
                'event_night_id' => $eventNight->id,
                'previous_status' => $songRequest->getOriginal('status'),
            ]
        ));

        return back()->with('status', 'Richiesta canzone saltata.');
    }

    public function cancel(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'song_request_id' => ['required', 'integer'],
        ]);

        $songRequest = $eventNight->songRequests()->whereKey($data['song_request_id'])->firstOrFail();

        $queueEngine->cancel($eventNight, $songRequest);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.cancel',
            subjectType: SongRequest::class,
            subjectId: (string) $songRequest->id,
            metadata: [
                'event_night_id' => $eventNight->id,
                'previous_status' => $songRequest->getOriginal('status'),
            ]
        ));

        return back()->with('status', 'Richiesta canzone annullata.');
    }

    public function stop(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $queueEngine->stop($eventNight);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.stop',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', 'Riproduzione in pausa.');
    }

    public function start(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $queueEngine->startNext($eventNight);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.start',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', 'Riproduzione avviata.');
    }

    public function resume(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $queueEngine->resume($eventNight);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.resume',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', 'Riproduzione ripresa.');
    }

    public function next(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $nextRequest = $queueEngine->next($eventNight);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.next',
            subjectType: SongRequest::class,
            subjectId: $nextRequest ? (string) $nextRequest->id : 'none',
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', 'Passato alla prossima canzone.');
    }

    public function add(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueManualService $queueManualService): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'song_id' => ['required', 'integer', 'exists:songs,id'],
        ]);

        $songRequest = $queueManualService->addParticipantRequest($eventNight, $data['display_name'], $data['song_id']);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.add',
            subjectType: SongRequest::class,
            subjectId: (string) $songRequest->id,
            metadata: [
                'event_night_id' => $eventNight->id,
                'participant_name' => $data['display_name'],
                'song_id' => $data['song_id'],
            ]
        ));

        return back()->with('status', 'Partecipante aggiunto alla coda.');
    }

    public function reorder(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueManualService $queueManualService): RedirectResponse|JsonResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'ordered_song_request_ids' => ['required', 'array'],
            'ordered_song_request_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $orderedIds = array_map(static fn (mixed $id): int => (int) $id, $data['ordered_song_request_ids']);
        $queueManualService->reorderQueuedRequests($eventNight, $orderedIds);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.reorder',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
                'ordered_song_request_ids' => $orderedIds,
            ]
        ));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Ordine della coda aggiornato.',
            ]);
        }

        return back()->with('status', 'Ordine della coda aggiornato.');
    }

    /**
     * @return array{0: Collection<int, SongRequest>, 1: Collection<int, SongRequest>}
     */
    private function loadQueueCollections(EventNight $eventNight): array
    {
        // Target only relevant rows instead of loading the whole event request history on every poll.
        $queue = SongRequest::query()
            ->where('event_night_id', $eventNight->id)
            ->whereIn('status', [SongRequest::STATUS_QUEUED, SongRequest::STATUS_PLAYING])
            ->with(['song', 'participant'])
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->values();

        $history = SongRequest::query()
            ->where('event_night_id', $eventNight->id)
            ->whereIn('status', [SongRequest::STATUS_PLAYED, SongRequest::STATUS_SKIPPED, SongRequest::STATUS_CANCELED])
            ->with(['song', 'participant'])
            ->orderByRaw('COALESCE(played_at, updated_at) desc')
            ->orderBy('id')
            ->get()
            ->values();

        return [$queue, $history];
    }

    private function loadPlaybackState(EventNight $eventNight): ?PlaybackState
    {
        return PlaybackState::query()
            ->where('event_night_id', $eventNight->id)
            ->with(['currentRequest.song'])
            ->first();
    }

    private function serializePlayback(EventNight $eventNight, ?PlaybackState $playbackState): array
    {
        $state = $playbackState?->state ?? PlaybackState::STATE_IDLE;
        $labels = [
            PlaybackState::STATE_IDLE => 'In attesa',
            PlaybackState::STATE_PLAYING => 'In riproduzione',
            PlaybackState::STATE_PAUSED => 'In pausa',
        ];

        $toggleAction = route('admin.queue.start', $eventNight);
        $toggleMode = 'play';
        $toggleTitle = 'Avvia la serata';

        if ($state === PlaybackState::STATE_PLAYING) {
            $toggleAction = route('admin.queue.stop', $eventNight);
            $toggleMode = 'pause';
            $toggleTitle = 'Metti in pausa la serata';
        } elseif ($state === PlaybackState::STATE_PAUSED) {
            $toggleAction = route('admin.queue.resume', $eventNight);
            $toggleMode = 'play';
            $toggleTitle = 'Riprendi la serata';
        }

        return [
            'state' => $state,
            'status_label' => $labels[$state] ?? ucfirst($state),
            'current_song_title' => $playbackState?->currentRequest?->song?->title ?? '—',
            'expected_end_at' => $playbackState?->expected_end_at?->toIso8601String(),
            'toggle_action' => $toggleAction,
            'toggle_mode' => $toggleMode,
            'toggle_title' => $toggleTitle,
        ];
    }

    private function serializeQueueRequest(SongRequest $songRequest): array
    {
        return [
            'id' => $songRequest->id,
            'position' => $songRequest->position,
            'participant_name' => $songRequest->participant?->display_name ?? 'Ospite',
            'song_title' => $songRequest->song?->title ?? 'Sconosciuta',
            'status' => $songRequest->status,
            'is_movable' => $songRequest->status === SongRequest::STATUS_QUEUED,
            'is_playing' => $songRequest->status === SongRequest::STATUS_PLAYING,
        ];
    }

    private function serializeHistoryRequest(SongRequest $songRequest, string $timezone): array
    {
        $when = $songRequest->played_at ?? $songRequest->updated_at;
        $displayTime = '—';

        if ($when) {
            try {
                $displayTime = $when->copy()->setTimezone($timezone)->format('H:i');
            } catch (\Throwable) {
                $displayTime = $when->format('H:i');
            }
        }

        return [
            'id' => $songRequest->id,
            'played_at' => $when?->toIso8601String(),
            'display_time' => $displayTime,
            'participant_name' => $songRequest->participant?->display_name ?? 'Ospite',
            'song_title' => $songRequest->song?->title ?? 'Sconosciuta',
            'status' => $songRequest->status,
        ];
    }

    private function resolveEventTimezone(EventNight $eventNight): string
    {
        $eventNight->loadMissing('venue');

        return $eventNight->venue?->timezone ?? config('app.timezone', 'Europe/Rome');
    }
}
