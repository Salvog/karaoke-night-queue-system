<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminVenuesController extends Controller
{
    public function index(Request $request): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $venues = Venue::withCount('eventNights')
            ->orderBy('name')
            ->get();

        return view('admin.venues.index', [
            'venues' => $venues,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $data = $this->validatedVenueData($request);

        Venue::create($data);

        return back()->with('status', 'Venue created.');
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $data = $this->validatedVenueData($request, $venue);

        $venue->update($data);

        return back()->with('status', 'Venue updated.');
    }

    public function destroy(Request $request, Venue $venue): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        if ($venue->eventNights()->exists()) {
            return back()->withErrors(['venue' => 'Venue has events and cannot be deleted.']);
        }

        $venue->delete();

        return back()->with('status', 'Venue deleted.');
    }

    private function validatedVenueData(Request $request, ?Venue $venue = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('venues', 'name')->ignore($venue?->id),
            ],
            'timezone' => ['required', 'string', 'timezone', 'max:64'],
        ]);
    }
}
