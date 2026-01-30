@extends('admin.layout')

@section('content')
    <h1>Theme & Ads for Event #{{ $eventNight->id }}</h1>
    <p>Venue: {{ $eventNight->venue?->name ?? 'N/A' }}</p>

    <form method="POST" action="{{ route('admin.theme.update', $eventNight) }}">
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

        <button class="button" type="submit">Save</button>
    </form>
@endsection
