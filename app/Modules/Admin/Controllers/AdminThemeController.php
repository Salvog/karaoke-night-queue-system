<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdBanner;
use App\Models\EventNight;
use App\Models\Theme;
use App\Modules\Auth\Actions\LogAdminAction;
use App\Modules\Auth\DTOs\AdminActionData;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminThemeController extends Controller
{
    public function show(Request $request, EventNight $eventNight): View
    {
        Gate::forUser($request->user('admin'))->authorize('manage-event-nights');

        $eventNight->load('venue');
        $themes = Theme::where('venue_id', $eventNight->venue_id)->orderBy('name')->get();
        $ads = AdBanner::where('venue_id', $eventNight->venue_id)->orderBy('title')->get();

        return view('admin.theme.show', [
            'eventNight' => $eventNight,
            'themes' => $themes,
            'ads' => $ads,
        ]);
    }

    public function update(
        Request $request,
        EventNight $eventNight,
        LogAdminAction $logger,
        RealtimePublisher $publisher
    ): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $data = $request->validate([
            'theme_id' => [
                'nullable',
                'integer',
                Rule::exists('themes', 'id')->where('venue_id', $eventNight->venue_id),
            ],
            'ad_banner_id' => [
                'nullable',
                'integer',
                Rule::exists('ad_banners', 'id')->where('venue_id', $eventNight->venue_id),
            ],
        ]);

        // Store selections directly on the event for a single per-event source of truth.
        $eventNight->update([
            'theme_id' => $data['theme_id'] ?? null,
            'ad_banner_id' => $data['ad_banner_id'] ?? null,
        ]);

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'theme.update',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
                'theme_id' => $data['theme_id'] ?? null,
                'ad_banner_id' => $data['ad_banner_id'] ?? null,
            ]
        ));

        $publisher->publishThemeUpdated($eventNight);

        return back()->with('status', 'Theme/ads updated.');
    }
}
