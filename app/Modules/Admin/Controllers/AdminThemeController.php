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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminThemeController extends Controller
{
    public function show(Request $request, EventNight $eventNight): View
    {
        $adminUser = $request->user('admin');
        Gate::forUser($adminUser)->authorize('manage-event-nights');

        $eventNight->load('venue');
        $themes = Theme::where('venue_id', $eventNight->venue_id)->orderBy('name')->get();
        $ads = AdBanner::where('venue_id', $eventNight->venue_id)
            ->orderBy('title')
            ->get()
            ->each(function (AdBanner $ad): void {
                $ad->image_url = $this->resolveMediaUrl($ad->image_url);
                $ad->logo_url = $this->resolveMediaUrl($ad->logo_url);
            });

        return view('admin.theme.show', [
            'eventNight' => $eventNight,
            'themes' => $themes,
            'ads' => $ads,
            'adminUser' => $adminUser,
            'backgroundUrl' => $eventNight->background_image_path
                ? $this->resolvePublicDiskPath($eventNight->background_image_path)
                : null,
            'brandLogoUrl' => $eventNight->brand_logo_path
                ? $this->resolvePublicDiskPath($eventNight->brand_logo_path)
                : null,
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
            'background_image' => ['nullable', 'image', 'max:5120'],
            'remove_background_image' => ['nullable', 'boolean'],
            'event_logo' => ['nullable', 'image', 'max:3072'],
            'remove_event_logo' => ['nullable', 'boolean'],
            'overlay_texts' => ['nullable', 'array', 'max:5'],
            'overlay_texts.*' => ['nullable', 'string', 'max:120'],
        ]);

        if (
            $request->hasFile('background_image')
            || $request->boolean('remove_background_image')
            || $request->hasFile('event_logo')
            || $request->boolean('remove_event_logo')
        ) {
            abort_unless($adminUser->isAdmin(), 403);
        }

        $overlayTexts = collect($data['overlay_texts'] ?? [])
            ->map(fn (?string $text) => $text !== null ? trim($text) : null)
            ->filter()
            ->values()
            ->all();

        // Store selections directly on the event for a single per-event source of truth.
        $eventNight->update($this->buildEventThemeUpdate($request, $eventNight, [
            'theme_id' => $data['theme_id'] ?? null,
            'ad_banner_id' => $data['ad_banner_id'] ?? null,
            'overlay_texts' => $overlayTexts ?: null,
        ]));

        $logger->execute(new AdminActionData(
            userId: $adminUser->id,
            action: 'theme.update',
            subjectType: EventNight::class,
            subjectId: (string) $eventNight->id,
            metadata: [
                'event_night_id' => $eventNight->id,
                'theme_id' => $data['theme_id'] ?? null,
                'ad_banner_id' => $data['ad_banner_id'] ?? null,
                'has_background_image' => ! empty($eventNight->background_image_path),
                'has_event_logo' => ! empty($eventNight->brand_logo_path),
                'overlay_texts' => $overlayTexts,
            ]
        ));

        $publisher->publishThemeUpdated($eventNight);

        return back()->with('status', 'Tema/annunci aggiornati.');
    }

    private function buildEventThemeUpdate(Request $request, EventNight $eventNight, array $base): array
    {
        $updates = $base;

        if ($request->hasFile('background_image')) {
            if ($eventNight->background_image_path) {
                Storage::disk('public')->delete($eventNight->background_image_path);
            }

            $path = $request->file('background_image')->store("event-themes/{$eventNight->id}", 'public');
            $updates['background_image_path'] = $path;
        } elseif ($request->boolean('remove_background_image')) {
            if ($eventNight->background_image_path) {
                Storage::disk('public')->delete($eventNight->background_image_path);
            }
            $updates['background_image_path'] = null;
        }

        if ($request->hasFile('event_logo')) {
            if ($eventNight->brand_logo_path) {
                Storage::disk('public')->delete($eventNight->brand_logo_path);
            }

            $path = $request->file('event_logo')->store("event-branding/{$eventNight->id}", 'public');
            $updates['brand_logo_path'] = $path;
        } elseif ($request->boolean('remove_event_logo')) {
            if ($eventNight->brand_logo_path) {
                Storage::disk('public')->delete($eventNight->brand_logo_path);
            }

            $updates['brand_logo_path'] = null;
        }

        return $updates;
    }

    private function resolveMediaUrl(?string $url): ?string
    {
        $value = $this->normalizeLocalAbsoluteUrl(is_string($url) ? trim($url) : '');
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//', 'data:', 'blob:'])) {
            return $value;
        }

        if (Str::startsWith($value, '/storage/')) {
            $path = ltrim(Str::after($value, '/storage/'), '/');
            if ($path !== '' && Storage::disk('public')->exists($path)) {
                return $this->resolvePublicDiskPath($path);
            }
        }

        if (Str::startsWith($value, '/media/')) {
            $path = ltrim(Str::after($value, '/media/'), '/');
            if ($path !== '' && Storage::disk('public')->exists($path)) {
                return $this->resolvePublicDiskPath($path);
            }
        }

        $path = ltrim($value, '/');
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            return $this->resolvePublicDiskPath($path);
        }

        return Str::startsWith($value, '/') ? $value : '/' . $value;
    }

    private function resolvePublicDiskPath(string $path): string
    {
        return route('public.screen.media', ['path' => ltrim($path, '/')], false);
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
