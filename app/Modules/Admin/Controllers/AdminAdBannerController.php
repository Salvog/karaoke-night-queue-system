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
            'subtitle' => ['nullable', 'string', 'max:140'],
            'image' => ['required', 'image', 'max:5120'],
            'logo' => ['nullable', 'image', 'max:3072'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('image')->store("ad-banners/{$eventNight->venue_id}", 'public');
        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store("ad-banners/{$eventNight->venue_id}", 'public')
            : null;

        $banner = AdBanner::create([
            'venue_id' => $eventNight->venue_id,
            'title' => $data['title'],
            'subtitle' => $this->normalizeOptionalText($data['subtitle'] ?? null),
            'image_url' => $this->resolvePublicDiskPath($path),
            'logo_url' => $logoPath ? $this->resolvePublicDiskPath($logoPath) : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $this->publishBannerUpdates($banner, $publisher);

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
            'subtitle' => ['nullable', 'string', 'max:140'],
            'image' => ['nullable', 'image', 'max:5120'],
            'logo' => ['nullable', 'image', 'max:3072'],
            'remove_logo' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $this->deletePublicAsset($adBanner->image_url);
            $path = $request->file('image')->store("ad-banners/{$eventNight->venue_id}", 'public');
            $adBanner->image_url = $this->resolvePublicDiskPath($path);
        }

        if ($request->hasFile('logo')) {
            $this->deletePublicAsset($adBanner->logo_url);
            $logoPath = $request->file('logo')->store("ad-banners/{$eventNight->venue_id}", 'public');
            $adBanner->logo_url = $this->resolvePublicDiskPath($logoPath);
        } elseif ($request->boolean('remove_logo')) {
            $this->deletePublicAsset($adBanner->logo_url);
            $adBanner->logo_url = null;
        }

        $adBanner->fill([
            'title' => $data['title'],
            'subtitle' => $this->normalizeOptionalText($data['subtitle'] ?? null),
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
        $this->deletePublicAsset($adBanner->logo_url);
        $adBanner->delete();

        $this->publishBannerUpdates($adBanner, $publisher);

        return back()->with('status', 'Banner eliminato.');
    }

    private function deletePublicAsset(?string $url): void
    {
        $path = $this->extractPublicDiskPath($url);
        if ($path === null) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function publishBannerUpdates(AdBanner $adBanner, RealtimePublisher $publisher): void
    {
        EventNight::where('venue_id', $adBanner->venue_id)
            ->where('status', EventNight::STATUS_ACTIVE)
            ->get()
            ->each(
            fn (EventNight $eventNight) => $publisher->publishThemeUpdated($eventNight)
        );
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $trimmed = $value !== null ? trim($value) : null;

        return $trimmed === '' ? null : $trimmed;
    }

    private function resolvePublicDiskPath(string $path): string
    {
        return route('public.screen.media', ['path' => ltrim($path, '/')], false);
    }

    private function extractPublicDiskPath(?string $url): ?string
    {
        $value = $this->normalizeLocalAbsoluteUrl(is_string($url) ? trim($url) : '');
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, '/storage/')) {
            $path = ltrim(Str::after($value, '/storage/'), '/');
        } elseif (Str::startsWith($value, '/media/')) {
            $path = ltrim(Str::after($value, '/media/'), '/');
        } else {
            $path = ltrim($value, '/');
        }

        if ($path === '' || str_contains($path, '..') || str_contains($path, '\\')) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? $path : null;
    }

    private function normalizeLocalAbsoluteUrl(string $value): string
    {
        if ($value === '' || ! Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $parsed = parse_url($value);
        if (! is_array($parsed)) {
            return $value;
        }

        $path = (string) ($parsed['path'] ?? '/');
        if (! Str::startsWith($path, ['/storage/', '/media/'])) {
            return $value;
        }

        $host = isset($parsed['host']) ? strtolower((string) $parsed['host']) : '';
        if ($host === '') {
            return $value;
        }

        $appHost = $this->appUrlHost();
        $isLoopback = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if (! $isLoopback && ($appHost === null || $host !== $appHost)) {
            return $value;
        }

        $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) && $parsed['fragment'] !== '' ? '#' . $parsed['fragment'] : '';

        return $path . $query . $fragment;
    }

    private function appUrlHost(): ?string
    {
        $configured = (string) config('app.url', '');
        if ($configured === '') {
            return null;
        }

        $parsed = parse_url($configured);
        if (! is_array($parsed)) {
            return null;
        }

        $host = isset($parsed['host']) ? strtolower((string) $parsed['host']) : '';

        return $host !== '' ? $host : null;
    }
}
