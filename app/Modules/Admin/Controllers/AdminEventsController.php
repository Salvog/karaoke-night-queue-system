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

        $now = now();
        $currentEvent = EventNight::with('venue')
            ->where('status', EventNight::STATUS_ACTIVE)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('starts_at')
            ->first();

        $events = EventNight::with('venue')
            ->when($currentEvent, fn ($query) => $query->whereKeyNot($currentEvent->id))
            ->orderBy('starts_at')
            ->get();

        return view('admin.events.index', [
            'events' => $events,
            'currentEvent' => $currentEvent,
            'adminUser' => $adminUser,
        ]);
    }

    public function create(Request $request, EventNightService $service): View
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $venues = Venue::orderBy('name')->get();

        return view('admin.events.create', [
            'venues' => $venues,
            'statuses' => EventNight::statusOptions(),
            'generatedCode' => $service->generateCode(),
            'adminUser' => $adminUser,
        ]);
    }

    public function store(Request $request, EventNightService $service): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $this->normalizeEventData($this->validatedEventData($request));

        $eventNight = $service->create($data);

        return redirect()
            ->route('admin.events.edit', $eventNight)
            ->with('status', 'Evento creato.');
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

        $data = $this->normalizeEventData($this->validatedEventData($request));

        $service->update($eventNight, $data);

        return redirect()
            ->route('admin.events.edit', $eventNight)
            ->with('status', 'Evento aggiornato.');
    }

    public function destroy(Request $request, EventNight $eventNight): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('delete', $eventNight);

        $eventNight->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('status', 'Evento eliminato.');
    }

    private function validatedEventData(Request $request): array
    {
        return $request->validate([
            'venue_id' => ['required', 'integer', Rule::exists('venues', 'id')],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'break_seconds' => ['required', 'integer', 'min:0'],
            'request_cooldown_minutes' => ['required', 'integer', 'min:0'],
            'join_pin' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(array_keys(EventNight::statusOptions()))],
        ]);
    }

    private function normalizeEventData(array $data): array
    {
        $data['join_pin'] = $this->normalizePin($data['join_pin'] ?? null);
        $data['request_cooldown_seconds'] = ((int) $data['request_cooldown_minutes']) * 60;

        unset($data['request_cooldown_minutes']);

        return $data;
    }

    private function normalizePin(?string $pin): ?string
    {
        $trimmed = $pin !== null ? trim($pin) : null;

        return $trimmed === '' ? null : $trimmed;
    }

}
