<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use App\Models\Song;
use App\Models\SongRequest;
use App\Modules\Auth\Actions\LogAdminAction;
use App\Modules\Auth\DTOs\AdminActionData;
use App\Modules\Admin\Services\ManualQueueService;
use App\Modules\Queue\Services\QueueEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminQueueController extends Controller
{
    public function show(Request $request, EventNight $eventNight): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $eventNight->load([
            'venue',
            'playbackState.currentRequest.song',
            'playbackState.currentRequest.participant',
            'songRequests.song',
            'songRequests.participant',
        ]);

        $queue = $eventNight->songRequests
            ->whereIn('status', [SongRequest::STATUS_QUEUED, SongRequest::STATUS_PLAYING])
            ->sortBy(fn (SongRequest $songRequest) => [$songRequest->position ?? PHP_INT_MAX, $songRequest->id]);

        $history = $eventNight->songRequests
            ->whereIn('status', [
                SongRequest::STATUS_PLAYED,
                SongRequest::STATUS_SKIPPED,
                SongRequest::STATUS_CANCELED,
            ])
            ->sortByDesc(fn (SongRequest $songRequest) => $songRequest->played_at ?? $songRequest->updated_at);

        return view('admin.queue.show', [
            'eventNight' => $eventNight,
            'queue' => $queue,
            'history' => $history,
            'songs' => Song::orderBy('artist')->orderBy('title')->get(),
        ]);
    }

    public function start(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $songRequest = $queueEngine->start($eventNight);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.start',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        $message = $songRequest ? 'Playback started.' : 'Queue is empty.';

        return back()->with('status', $message);
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

    public function pause(Request $request, EventNight $eventNight, LogAdminAction $logger, QueueEngine $queueEngine): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $queueEngine->pause($eventNight);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.pause',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', 'Playback paused.');
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

    public function storeManual(
        Request $request,
        EventNight $eventNight,
        LogAdminAction $logger,
        ManualQueueService $manualQueueService
    ): RedirectResponse {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'participant_name' => ['required', 'string', 'max:80'],
            'song_id' => ['required', 'integer', 'exists:songs,id'],
        ]);

        $songRequest = $manualQueueService->addManualRequest(
            $eventNight,
            $data['participant_name'],
            (int) $data['song_id']
        );

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.manual_add',
            subjectType: SongRequest::class,
            subjectId: (string) $songRequest->id,
            metadata: [
                'event_night_id' => $eventNight->id,
                'participant_name' => $data['participant_name'],
            ]
        ));

        return back()->with('status', 'Manual request added to the queue.');
    }
}
