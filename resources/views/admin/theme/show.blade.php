@extends('admin.layout')

@section('content')
    <h1>Tema e annunci per l'evento #{{ $eventNight->id }}</h1>
    <p>Location: {{ $eventNight->venue?->name ?? 'N/D' }}</p>

    @php
        $overlayTexts = old('overlay_texts', $eventNight->overlay_texts ?? []);
        $overlayTexts = array_pad($overlayTexts, 5, '');
        $backgroundUrl = $eventNight->background_image_path
            ? Storage::disk('public')->url($eventNight->background_image_path)
            : null;
        $logoUrl = $eventNight->logo_path
            ? Storage::disk('public')->url($eventNight->logo_path)
            : null;
    @endphp

    <form method="POST" action="{{ route('admin.theme.update', $eventNight) }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="theme_id">Tema</label>
            <select id="theme_id" name="theme_id">
                <option value="">Nessun tema</option>
                @foreach ($themes as $theme)
                    <option value="{{ $theme->id }}" @selected($eventNight->theme_id === $theme->id)>
                        {{ $theme->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="ad_banner_id">Banner pubblicitario</label>
            <select id="ad_banner_id" name="ad_banner_id">
                <option value="">Nessun banner</option>
                @foreach ($ads as $ad)
                    <option value="{{ $ad->id }}" @selected($eventNight->ad_banner_id === $ad->id)>
                        {{ $ad->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="background_image">Immagine di sfondo</label>
            @if ($backgroundUrl)
                <div style="margin: 8px 0;">
                    <img src="{{ $backgroundUrl }}" alt="Anteprima sfondo" style="max-width: 320px; border-radius: 8px;">
                </div>
            @endif
            <input id="background_image" type="file" name="background_image" @disabled(! $adminUser->isAdmin())>
            @if ($backgroundUrl)
                <label style="display: block; margin-top: 8px;">
                    <input type="checkbox" name="remove_background_image" value="1" @disabled(! $adminUser->isAdmin())>
                    Rimuovi immagine di sfondo
                </label>
            @endif
            @if (! $adminUser->isAdmin())
                <div style="margin-top: 6px; font-size: 12px; color: #6b7280;">Solo gli admin possono caricare risorse.</div>
            @endif
        </div>

        <div style="margin-bottom: 16px;">
            <label for="logo_image">Logo evento</label>
            @if ($logoUrl)
                <div style="margin: 8px 0;">
                    <img src="{{ $logoUrl }}" alt="Logo evento" style="max-width: 200px; border-radius: 8px; background: #f8fafc; padding: 8px;">
                </div>
            @endif
            <input id="logo_image" type="file" name="logo_image" @disabled(! $adminUser->isAdmin())>
            @if ($logoUrl)
                <label style="display: block; margin-top: 8px;">
                    <input type="checkbox" name="remove_logo_image" value="1" @disabled(! $adminUser->isAdmin())>
                    Rimuovi logo
                </label>
            @endif
            @if (! $adminUser->isAdmin())
                <div style="margin-top: 6px; font-size: 12px; color: #6b7280;">Solo gli admin possono caricare risorse.</div>
            @endif
        </div>

        <div style="margin-bottom: 16px;">
            <label>Testi sovrapposti (max 5)</label>
            <div style="display: grid; gap: 8px; margin-top: 8px;">
                @foreach ($overlayTexts as $text)
                    <input type="text" name="overlay_texts[]" value="{{ $text }}" placeholder="Testo sovrapposto">
                @endforeach
            </div>
        </div>

        <button class="button" type="submit">Salva</button>
    </form>

    <hr style="margin: 32px 0;">

    <h2>Banner pubblicitari</h2>
    <p style="color: #6b7280; font-size: 14px;">I banner vengono mostrati nello schermo pubblico quando selezionati sopra.</p>

    @if ($ads->isEmpty())
        <p>Nessun banner ancora.</p>
    @else
        <div style="display: grid; gap: 16px; margin-bottom: 24px;">
            @foreach ($ads as $ad)
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px;">
                    <form method="POST" action="{{ route('admin.ad-banners.update', [$eventNight, $ad]) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div style="margin-bottom: 8px;">
                            <label>Titolo</label>
                            <input type="text" name="title" value="{{ $ad->title }}" required>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label>Sottotitolo</label>
                            <input type="text" name="subtitle" value="{{ $ad->subtitle }}">
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label>Sostituisci immagine</label>
                            <input type="file" name="image" @disabled(! $adminUser->isAdmin())>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label>Logo sponsor</label>
                            <input type="file" name="logo" @disabled(! $adminUser->isAdmin())>
                            @if ($ad->logo_url)
                                <label style="display: block; margin-top: 6px;">
                                    <input type="checkbox" name="remove_logo" value="1">
                                    Rimuovi logo
                                </label>
                            @endif
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label>
                                <input type="checkbox" name="is_active" value="1" @checked($ad->is_active)>
                                Attivo
                            </label>
                        </div>
                        @if ($ad->image_url)
                            <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" style="max-width: 200px; border-radius: 8px;">
                        @endif
                        <div style="margin-top: 8px;">
                            <button class="button" type="submit">Aggiorna</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.ad-banners.destroy', [$eventNight, $ad]) }}" style="margin-top: 8px;">
                        @csrf
                        @method('DELETE')
                        <button class="button danger" type="submit">Elimina</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <h3>Crea nuovo banner</h3>
    <form method="POST" action="{{ route('admin.ad-banners.store', $eventNight) }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 8px;">
            <label>Titolo</label>
            <input type="text" name="title" required>
        </div>
        <div style="margin-bottom: 8px;">
            <label>Sottotitolo</label>
            <input type="text" name="subtitle">
        </div>
        <div style="margin-bottom: 8px;">
            <label>Immagine</label>
            <input type="file" name="image" @disabled(! $adminUser->isAdmin()) required>
        </div>
        <div style="margin-bottom: 8px;">
            <label>Logo sponsor (opzionale)</label>
            <input type="file" name="logo" @disabled(! $adminUser->isAdmin())>
        </div>
        <div style="margin-bottom: 8px;">
            <label>
                <input type="checkbox" name="is_active" value="1" checked>
                Attivo
            </label>
        </div>
        <button class="button" type="submit">Crea banner</button>
    </form>
@endsection
