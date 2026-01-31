@extends('admin.layout')

@section('content')
    <h1>Theme & Ads for Event #{{ $eventNight->id }}</h1>
    <p>Venue: {{ $eventNight->venue?->name ?? 'N/A' }}</p>

    @php
        $overlayTexts = old('overlay_texts', $eventNight->overlay_texts ?? []);
        $overlayTexts = array_pad($overlayTexts, 5, '');
        $backgroundUrl = $eventNight->background_image_path
            ? Storage::disk('public')->url($eventNight->background_image_path)
            : null;
    @endphp

    <form method="POST" action="{{ route('admin.theme.update', $eventNight) }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="theme_id">Theme</label>
            <select id="theme_id" name="theme_id">
                <option value="">No theme</option>
                @foreach ($themes as $theme)
                    <option value="{{ $theme->id }}" @selected($eventNight->theme_id === $theme->id)>
                        {{ $theme->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="ad_banner_id">Ad Banner</label>
            <select id="ad_banner_id" name="ad_banner_id">
                <option value="">No banner</option>
                @foreach ($ads as $ad)
                    <option value="{{ $ad->id }}" @selected($eventNight->ad_banner_id === $ad->id)>
                        {{ $ad->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="background_image">Background Image</label>
            @if ($backgroundUrl)
                <div style="margin: 8px 0;">
                    <img src="{{ $backgroundUrl }}" alt="Background preview" style="max-width: 320px; border-radius: 8px;">
                </div>
            @endif
            <input id="background_image" type="file" name="background_image" @disabled(! $adminUser->isAdmin())>
            @if ($backgroundUrl)
                <label style="display: block; margin-top: 8px;">
                    <input type="checkbox" name="remove_background_image" value="1" @disabled(! $adminUser->isAdmin())>
                    Remove background image
                </label>
            @endif
            @if (! $adminUser->isAdmin())
                <div style="margin-top: 6px; font-size: 12px; color: #6b7280;">Only admins can upload assets.</div>
            @endif
        </div>

        <div style="margin-bottom: 16px;">
            <label>Overlay Texts (up to 5)</label>
            <div style="display: grid; gap: 8px; margin-top: 8px;">
                @foreach ($overlayTexts as $text)
                    <input type="text" name="overlay_texts[]" value="{{ $text }}" placeholder="Overlay text">
                @endforeach
            </div>
        </div>

        <button class="button" type="submit">Save</button>
    </form>

    <hr style="margin: 32px 0;">

    <h2>Ad Banners</h2>
    <p style="color: #6b7280; font-size: 14px;">Banners are shown on the public screen when selected above.</p>

    @if ($ads->isEmpty())
        <p>No banners yet.</p>
    @else
        <div style="display: grid; gap: 16px; margin-bottom: 24px;">
            @foreach ($ads as $ad)
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px;">
                    <form method="POST" action="{{ route('admin.ad-banners.update', [$eventNight, $ad]) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div style="margin-bottom: 8px;">
                            <label>Title</label>
                            <input type="text" name="title" value="{{ $ad->title }}" required>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label>Replace Image</label>
                            <input type="file" name="image" @disabled(! $adminUser->isAdmin())>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label>
                                <input type="checkbox" name="is_active" value="1" @checked($ad->is_active)>
                                Active
                            </label>
                        </div>
                        @if ($ad->image_url)
                            <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" style="max-width: 200px; border-radius: 8px;">
                        @endif
                        <div style="margin-top: 8px;">
                            <button class="button" type="submit">Update</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.ad-banners.destroy', [$eventNight, $ad]) }}" style="margin-top: 8px;">
                        @csrf
                        @method('DELETE')
                        <button class="button danger" type="submit">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <h3>Create New Banner</h3>
    <form method="POST" action="{{ route('admin.ad-banners.store', $eventNight) }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 8px;">
            <label>Title</label>
            <input type="text" name="title" required>
        </div>
        <div style="margin-bottom: 8px;">
            <label>Image</label>
            <input type="file" name="image" @disabled(! $adminUser->isAdmin()) required>
        </div>
        <div style="margin-bottom: 8px;">
            <label>
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>
        </div>
        <button class="button" type="submit">Create Banner</button>
    </form>
@endsection
