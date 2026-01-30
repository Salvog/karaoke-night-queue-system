<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use App\Models\PlaybackState;
use App\Models\SongRequest;
use App\Modules\Auth\Actions\LogAdminAction;
use App\Modules\Auth\DTOs\AdminActionData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminQueueController extends Controller
{
    public function show(Request $request, EventNight $eventNight): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $eventNight->load(['venue', 'playbackState', 'songRequests.song', 'songRequests.participant']);

        $queue = $eventNight->songRequests
            ->whereIn('status', [SongRequest::STATUS_QUEUED, SongRequest::STATUS_PLAYING])
            ->sortBy(fn (SongRequest $songRequest) => [$songRequest->position ?? PHP_INT_MAX, $songRequest->id]);

        return view('admin.queue.show', [
            'eventNight' => $eventNight,
            'queue' => $queue,
        ]);
    }

    public function skip(Request $request, EventNight $eventNight, LogAdminAction $logger): RedirectResponse
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

        DB::transaction(function () use ($songRequest, $eventNight) {
            // Simplest deterministic reset: mark skipped and clear playback state.
            $songRequest->update([
                'status' => SongRequest::STATUS_SKIPPED,
                'played_at' => now(),
            ]);

            $eventNight->playbackState()->updateOrCreate(
                ['event_night_id' => $eventNight->id],
                ['state' => PlaybackState::STATE_IDLE, 'current_request_id' => null]
            );
        });

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

    public function cancel(Request $request, EventNight $eventNight, LogAdminAction $logger): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'song_request_id' => ['required', 'integer'],
        ]);

        $songRequest = $eventNight->songRequests()->whereKey($data['song_request_id'])->firstOrFail();

        DB::transaction(function () use ($songRequest, $eventNight) {
            $songRequest->update([
                'status' => SongRequest::STATUS_CANCELED,
            ]);

            if ($eventNight->playbackState?->current_request_id === $songRequest->id) {
                $eventNight->playbackState()->update([
                    'state' => PlaybackState::STATE_IDLE,
                    'current_request_id' => null,
                ]);
            }
        });

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

    public function stop(Request $request, EventNight $eventNight, LogAdminAction $logger): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        DB::transaction(function () use ($eventNight) {
            // Simplest deterministic stop: clear the current request and mark idle.
            $eventNight->playbackState()->updateOrCreate(
                ['event_night_id' => $eventNight->id],
                ['state' => PlaybackState::STATE_IDLE, 'current_request_id' => null]
            );
        });

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'queue.stop',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
            ]
        ));

        return back()->with('status', 'Playback stopped.');
    }
}
