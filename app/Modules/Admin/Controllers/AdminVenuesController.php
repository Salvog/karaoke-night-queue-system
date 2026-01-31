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

        $venues = Venue::orderBy('name')->get();

        return view('admin.venues.index', [
            'venues' => $venues,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        return view('admin.venues.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $data = $this->validatedVenueData($request);

        $venue = Venue::create($data);

        return redirect()
            ->route('admin.venues.edit', $venue)
            ->with('status', 'Venue created.');
    }

    public function edit(Request $request, Venue $venue): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        return view('admin.venues.edit', [
            'venue' => $venue,
        ]);
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $data = $this->validatedVenueData($request, $venue);

        $venue->update($data);

        return redirect()
            ->route('admin.venues.edit', $venue)
            ->with('status', 'Venue updated.');
    }

    public function destroy(Request $request, Venue $venue): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $venue->delete();

        return redirect()
            ->route('admin.venues.index')
            ->with('status', 'Venue deleted.');
    }

    private function validatedVenueData(Request $request, ?Venue $venue = null): array
    {
        $uniqueNameRule = Rule::unique('venues', 'name');

        if ($venue) {
            $uniqueNameRule = $uniqueNameRule->ignore($venue->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueNameRule],
            'timezone' => ['required', 'string', 'max:64'],
        ]);
    }
}
