<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use App\Models\SongRequest;
use App\Models\Song;
use App\Modules\Admin\Services\QueueManagementService;
use App\Modules\Auth\Actions\LogAdminAction;
use App\Modules\Auth\DTOs\AdminActionData;
use App\Modules\Queue\Services\QueueEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminQueueController extends Controller
{
    public function show(Request $request, EventNight $eventNight): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $data = $this->buildQueueState($eventNight);

        $songs = Song::orderBy('artist')->orderBy('title')->get();

        return view('admin.queue.show', [
            'eventNight' => $eventNight,
            'queue' => $data['queue'],
            'history' => $data['history'],
            'playback' => $data['playback'],
            'timezone' => $data['timezone'],
            'songs' => $songs,
        ]);
    }

    public function state(Request $request, EventNight $eventNight, QueueEngine $queueEngine): JsonResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        if ($eventNight->status === EventNight::STATUS_ACTIVE) {
            $queueEngine->advanceIfNeeded($eventNight);
        }

        return response()->json($this->buildQueueState($eventNight));
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

        return back()->with('status', 'Song request skipped.');
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

        return back()->with('status', 'Song request canceled.');
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

        return back()->with('status', 'Playback paused.');
    }

    public function start(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $startedRequest = $queueEngine->resume($eventNight);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.start',
            subjectType: SongRequest::class,
            subjectId: $startedRequest ? (string) $startedRequest->id : 'none',
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', $startedRequest ? 'Playback started.' : 'No queued songs to start.');
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

        return back()->with('status', 'Moved to the next song.');
    }

    public function add(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueManagementService $queueManagementService): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'song_id' => ['required', 'integer', Rule::exists('songs', 'id')],
        ]);

        $songRequest = $queueManagementService->addManualRequest(
            $eventNight,
            $data['display_name'],
            (int) $data['song_id']
        );

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.add',
            subjectType: SongRequest::class,
            subjectId: (string) $songRequest->id,
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', 'Manual request added to the queue.');
    }

    private function buildQueueState(EventNight $eventNight): array
    {
        $eventNight->load([
            'venue',
            'playbackState.currentRequest.song',
            'playbackState.currentRequest.participant',
        ]);

        $timezone = $eventNight->venue?->timezone ?? config('app.timezone');
        $playbackState = $eventNight->playbackState;
        $currentRequest = $playbackState?->currentRequest;
        $expectedEndAt = $playbackState?->expected_end_at?->copy()->setTimezone($timezone);

        $queue = SongRequest::where('event_night_id', $eventNight->id)
            ->whereIn('status', [SongRequest::STATUS_QUEUED, SongRequest::STATUS_PLAYING])
            ->orderByRaw('position is null')
            ->orderBy('position')
            ->orderBy('id')
            ->with(['song', 'participant'])
            ->get();

        $history = SongRequest::where('event_night_id', $eventNight->id)
            ->whereIn('status', [SongRequest::STATUS_PLAYED, SongRequest::STATUS_SKIPPED, SongRequest::STATUS_CANCELED])
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->with(['song', 'participant'])
            ->get();

        return [
            'timezone' => $timezone,
            'playback' => [
                'state' => $playbackState?->state ?? 'idle',
                'expected_end_at' => $expectedEndAt?->toIso8601String(),
                'expected_end_label' => $expectedEndAt?->format('H:i:s'),
                'current_song' => $currentRequest?->song?->title ?? 'None',
                'current_participant' => $currentRequest?->participant?->display_name ?? 'Guest',
            ],
            'queue' => $queue->map(fn (SongRequest $request) => [
                'id' => $request->id,
                'position' => $request->position,
                'participant' => $request->participant?->display_name ?? 'Guest',
                'song' => $request->song?->title ?? 'Unknown',
                'status' => $request->status,
            ])->all(),
            'history' => $history->map(fn (SongRequest $request) => [
                'id' => $request->id,
                'played_at' => $request->played_at?->toIso8601String(),
                'played_at_label' => $request->played_at?->copy()->setTimezone($timezone)->format('H:i'),
                'participant' => $request->participant?->display_name ?? 'Guest',
                'song' => $request->song?->title ?? 'Unknown',
                'status' => $request->status,
            ])->all(),
        ];
    }
}
