<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdBanner;
use App\Models\EventNight;
use App\Modules\PublicScreen\Realtime\RealtimePublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminAdBannerController extends Controller
{
    public function store(Request $request, EventNight $eventNight, RealtimePublisher $publisher): RedirectResponse
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');
        abort_unless($adminUser->isAdmin(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'image' => ['required', 'image', 'max:5120'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('image')->store("ad-banners/{$eventNight->venue_id}", 'public');
        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store("ad-banners/{$eventNight->venue_id}", 'public')
            : null;

        AdBanner::create([
            'venue_id' => $eventNight->venue_id,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'image_url' => Storage::disk('public')->url($path),
            'logo_url' => $logoPath ? Storage::disk('public')->url($logoPath) : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $publisher->publishThemeUpdated($eventNight);

        return back()->with('status', 'Banner creato.');
    }

    public function update(
        Request $request,
        EventNight $eventNight,
        AdBanner $adBanner,
        RealtimePublisher $publisher
    ): RedirectResponse {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');
        abort_unless($adminUser->isAdmin(), 403);

        if ($adBanner->venue_id !== $eventNight->venue_id) {
            abort(404);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'image' => ['nullable', 'image', 'max:5120'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $this->deletePublicAsset($adBanner->image_url);
            $path = $request->file('image')->store("ad-banners/{$eventNight->venue_id}", 'public');
            $adBanner->image_url = Storage::disk('public')->url($path);
        }

        if ($request->hasFile('logo')) {
            if ($adBanner->logo_url) {
                $this->deletePublicAsset($adBanner->logo_url);
            }
            $logoPath = $request->file('logo')->store("ad-banners/{$eventNight->venue_id}", 'public');
            $adBanner->logo_url = Storage::disk('public')->url($logoPath);
        }

        $adBanner->fill([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ])->save();

        $this->publishBannerUpdates($adBanner, $publisher);

        return back()->with('status', 'Banner aggiornato.');
    }

    public function destroy(
        Request $request,
        EventNight $eventNight,
        AdBanner $adBanner,
        RealtimePublisher $publisher
    ): RedirectResponse {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');
        abort_unless($adminUser->isAdmin(), 403);

        if ($adBanner->venue_id !== $eventNight->venue_id) {
            abort(404);
        }

        $this->deletePublicAsset($adBanner->image_url);
        if ($adBanner->logo_url) {
            $this->deletePublicAsset($adBanner->logo_url);
        }
        $adBanner->delete();

        $this->publishBannerUpdates($adBanner, $publisher);

        return back()->with('status', 'Banner eliminato.');
    }

    private function deletePublicAsset(string $url): void
    {
        $prefix = Storage::disk('public')->url('');
        $path = Str::startsWith($url, $prefix) ? Str::after($url, $prefix) : null;

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function publishBannerUpdates(AdBanner $adBanner, RealtimePublisher $publisher): void
    {
        EventNight::where('ad_banner_id', $adBanner->id)->get()->each(
            fn (EventNight $eventNight) => $publisher->publishThemeUpdated($eventNight)
        );
    }
}
