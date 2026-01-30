<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventNight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

    public function destroy(Request $request, EventNight $eventNight): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('delete', $eventNight);

        $eventNight->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('status', 'Event deleted.');
    }
}
