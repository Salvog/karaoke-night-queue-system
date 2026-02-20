@extends('admin.layout')

@section('content')
    @php
        $overlayTexts = old('overlay_texts', $eventNight->overlay_texts ?? []);
        $overlayTexts = array_pad($overlayTexts, 5, '');
    @endphp

    <h1>Tema e annunci per l'evento #{{ $eventNight->id }}</h1>
    <p>Location: {{ $eventNight->venue?->name ?? 'N/D' }}</p>

    <div class="card" style="margin-bottom: 22px;">
        <h2 style="margin-bottom: 14px;">Identità visiva evento</h2>

        <form method="POST" action="{{ route('admin.theme.update', $eventNight) }}" enctype="multipart/form-data">
            @csrf

            <div style="display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); margin-bottom: 12px;">
                <div>
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

                <div>
                    <label for="ad_banner_id">Banner principale (hero)</label>
                    <select id="ad_banner_id" name="ad_banner_id">
                        <option value="">Nessun banner</option>
                        @foreach ($ads as $ad)
                            <option value="{{ $ad->id }}" @selected($eventNight->ad_banner_id === $ad->id)>
                                {{ $ad->title }}
                            </option>
                        @endforeach
                    </select>
                    <p class="helper" style="margin: 6px 0 0;">Lo schermo pubblico mostra anche tutti gli sponsor attivi della location.</p>
                </div>
            </div>

            <div style="display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 14px;">
                <div style="border: 1px solid rgba(255,255,255,0.18); border-radius: 12px; padding: 12px;">
                    <label for="background_image">Immagine di sfondo</label>
                    @if ($backgroundUrl)
                        <div style="margin: 8px 0;">
                            <img src="{{ $backgroundUrl }}" alt="Anteprima sfondo" style="width: 100%; max-width: 320px; border-radius: 10px;">
                        </div>
                    @endif
                    <input id="background_image" type="file" name="background_image" @disabled(! $adminUser->isAdmin())>

                    @if ($backgroundUrl)
                        <label style="display: block; margin-top: 8px;">
                            <input type="checkbox" name="remove_background_image" value="1" @disabled(! $adminUser->isAdmin())>
                            Rimuovi immagine di sfondo
                        </label>
                    @endif
                </div>

                <div style="border: 1px solid rgba(255,255,255,0.18); border-radius: 12px; padding: 12px;">
                    <label for="event_logo">Logo evento / locale</label>
                    @if ($brandLogoUrl)
                        <div style="margin: 8px 0;">
                            <img src="{{ $brandLogoUrl }}" alt="Logo evento" style="width: 120px; height: 120px; object-fit: contain; border-radius: 12px; background: rgba(255,255,255,0.05); padding: 10px;">
                        </div>
                    @endif
                    <input id="event_logo" type="file" name="event_logo" @disabled(! $adminUser->isAdmin())>

                    @if ($brandLogoUrl)
                        <label style="display: block; margin-top: 8px;">
                            <input type="checkbox" name="remove_event_logo" value="1" @disabled(! $adminUser->isAdmin())>
                            Rimuovi logo evento
                        </label>
                    @endif
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label>Messaggi annuncio (max 5)</label>
                <div style="display: grid; gap: 8px; margin-top: 8px;">
                    @foreach ($overlayTexts as $text)
                        <input type="text" name="overlay_texts[]" value="{{ $text }}" placeholder="Messaggio in sovrimpressione/ticker">
                    @endforeach
                </div>
            </div>

            @if (! $adminUser->isAdmin())
                <p class="helper" style="margin-top: 0;">Solo gli admin possono caricare o rimuovere immagini.</p>
            @endif

            <button class="button" type="submit">Salva tema e annunci</button>
        </form>
    </div>

    <div class="card">
        <h2>Banner sponsor</h2>
        <p style="font-size: 14px; margin-bottom: 18px;">Ogni banner supporta <strong>titolo + sottotitolo + logo</strong> e può comparire nel carosello sponsor dello schermo pubblico quando attivo.</p>

        @if ($ads->isEmpty())
            <p>Nessun banner ancora.</p>
        @else
            <div style="display: grid; gap: 16px; margin-bottom: 24px;">
                @foreach ($ads as $ad)
                    <div style="border: 1px solid rgba(255,255,255,0.18); border-radius: 12px; padding: 14px; background: rgba(255,255,255,0.03);">
                        <form method="POST" action="{{ route('admin.ad-banners.update', [$eventNight, $ad]) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div style="display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                                <div>
                                    <label>Titolo</label>
                                    <input type="text" name="title" value="{{ $ad->title }}" required>
                                </div>

                                <div>
                                    <label>Sottotitolo</label>
                                    <input type="text" name="subtitle" value="{{ $ad->subtitle }}" placeholder="Promo, claim o descrizione breve">
                                </div>
                            </div>

                            <div style="display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-top: 12px;">
                                <div>
                                    <label>Sostituisci visual banner</label>
                                    <input type="file" name="image" @disabled(! $adminUser->isAdmin())>
                                    @if ($ad->image_url)
                                        <div style="margin-top: 8px;">
                                            <img src="{{ $ad->image_url }}" alt="Visual {{ $ad->title }}" style="max-width: 240px; border-radius: 10px;">
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label>Logo sponsor</label>
                                    <input type="file" name="logo" @disabled(! $adminUser->isAdmin())>

                                    @if ($ad->logo_url)
                                        <div style="margin-top: 8px;">
                                            <img src="{{ $ad->logo_url }}" alt="Logo {{ $ad->title }}" style="width: 120px; height: 80px; object-fit: contain; border-radius: 10px; background: rgba(255,255,255,0.06); padding: 8px;">
                                        </div>

                                        <label style="display: block; margin-top: 8px;">
                                            <input type="checkbox" name="remove_logo" value="1" @disabled(! $adminUser->isAdmin())>
                                            Rimuovi logo sponsor
                                        </label>
                                    @endif
                                </div>
                            </div>

                            <div style="margin-top: 10px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <label style="display: inline-flex; gap: 8px; align-items: center;">
                                    <input type="checkbox" name="is_active" value="1" @checked($ad->is_active)>
                                    Banner attivo
                                </label>

                                <button class="button" type="submit">Aggiorna</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.ad-banners.destroy', [$eventNight, $ad]) }}" style="margin-top: 10px;">
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

            <div style="display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 12px;">
                <div>
                    <label>Titolo</label>
                    <input type="text" name="title" required>
                </div>

                <div>
                    <label>Sottotitolo</label>
                    <input type="text" name="subtitle" placeholder="Descrizione sponsor">
                </div>
            </div>

            <div style="display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 12px;">
                <div>
                    <label>Visual banner</label>
                    <input type="file" name="image" @disabled(! $adminUser->isAdmin()) required>
                </div>

                <div>
                    <label>Logo sponsor</label>
                    <input type="file" name="logo" @disabled(! $adminUser->isAdmin())>
                </div>
            </div>

            <label style="display: inline-flex; gap: 8px; align-items: center; margin-bottom: 10px;">
                <input type="checkbox" name="is_active" value="1" checked>
                Attivo subito
            </label>

            <div>
                <button class="button" type="submit">Crea banner</button>
            </div>
        </form>
    </div>
@endsection
