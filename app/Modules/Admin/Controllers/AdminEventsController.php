<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use App\Models\Venue;
use App\Modules\Admin\Services\EventNightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminEventsController extends Controller
{
    public function index(Request $request): View
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $events = EventNight::with('venue')->orderByDesc('id')->get();

        return view('admin.events.index', [
            'events' => $events,
            'adminUser' => $adminUser,
        ]);
    }

    public function create(Request $request): View
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $venues = Venue::orderBy('name')->get();

        return view('admin.events.create', [
            'venues' => $venues,
            'statuses' => EventNight::statusOptions(),
            'adminUser' => $adminUser,
        ]);
    }

    public function store(Request $request, EventNightService $service): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $this->validatedEventData($request);
        $data['join_pin'] = $this->normalizePin($data['join_pin'] ?? null);

        $eventNight = $service->create($data);

        return redirect()
            ->route('admin.events.edit', $eventNight)
            ->with('status', 'Event created.');
    }

    public function edit(Request $request, EventNight $eventNight): View
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $venues = Venue::orderBy('name')->get();

        return view('admin.events.edit', [
            'eventNight' => $eventNight,
            'venues' => $venues,
            'statuses' => EventNight::statusOptions(),
            'adminUser' => $adminUser,
        ]);
    }

    public function update(Request $request, EventNight $eventNight, EventNightService $service): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $this->validatedEventData($request);
        $data['join_pin'] = $this->normalizePin($data['join_pin'] ?? null);

        $service->update($eventNight, $data);

        return redirect()
            ->route('admin.events.edit', $eventNight)
            ->with('status', 'Event updated.');
    }

    public function destroy(Request $request, EventNight $eventNight): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('delete', $eventNight);

        $eventNight->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('status', 'Event deleted.');
    }

    private function validatedEventData(Request $request): array
    {
        return $request->validate([
            'venue_id' => ['required', 'integer', Rule::exists('venues', 'id')],
            'starts_at' => ['required', 'date'],
            'break_seconds' => ['required', 'integer', 'min:0'],
            'request_cooldown_seconds' => ['required', 'integer', 'min:0'],
            'join_pin' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(array_keys(EventNight::statusOptions()))],
        ]);
    }

    private function normalizePin(?string $pin): ?string
    {
        $trimmed = $pin !== null ? trim($pin) : null;

        return $trimmed === '' ? null : $trimmed;
    }
}
