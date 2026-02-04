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
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        $venues = Venue::orderBy('name')->get();

        return view('admin.venues.index', [
            'venues' => $venues,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        return view('admin.venues.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        $data = $this->validatedData($request);

        $venue = Venue::create($data);

        return redirect()
            ->route('admin.venues.edit', $venue)
            ->with('status', 'Location creata.');
    }

    public function edit(Request $request, Venue $venue): View
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        return view('admin.venues.edit', [
            'venue' => $venue,
        ]);
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        $data = $this->validatedData($request, $venue);

        $venue->update($data);

        return redirect()
            ->route('admin.venues.edit', $venue)
            ->with('status', 'Location aggiornata.');
    }

    public function destroy(Request $request, Venue $venue): RedirectResponse
    {
        Gate::forUser($request->user('admin'))->authorize('access-admin');

        $venue->delete();

        return redirect()
            ->route('admin.venues.index')
            ->with('status', 'Location eliminata.');
    }

    private function validatedData(Request $request, ?Venue $venue = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('venues', 'name')->ignore($venue?->id),
            ],
            'timezone' => ['required', 'string', 'max:64'],
        ]);
    }
}
