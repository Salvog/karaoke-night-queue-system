<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Schermo</title>
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #0f172a;
            --accent-color: #f59e0b;
            --text-color: #f8fafc;
            --panel-bg: rgba(10, 15, 34, 0.75);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", sans-serif;
            color: var(--text-color);
            background: radial-gradient(circle at 20% 20%, rgba(124, 58, 237, 0.35), transparent 35%),
                        radial-gradient(circle at 80% 10%, rgba(245, 158, 11, 0.25), transparent 40%),
                        linear-gradient(145deg, #050816 0%, #0b1024 45%, #1a0b2f 100%);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .screen {
            padding: 22px;
            display: grid;
            gap: 16px;
            min-height: 100vh;
            grid-template-rows: auto 1fr auto;
            backdrop-filter: saturate(1.1);
        }

        .top-banner {
            border: 2px solid color-mix(in srgb, var(--primary-color) 65%, white);
            border-radius: 999px;
            padding: 8px 18px;
            width: fit-content;
            margin: 0 auto;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            background: linear-gradient(90deg, rgba(124, 58, 237, 0.35), rgba(15, 23, 42, 0.6), rgba(245, 158, 11, 0.3));
            box-shadow: 0 0 22px rgba(124, 58, 237, 0.55);
        }

        .content {
            display: grid;
            gap: 16px;
            grid-template-columns: 2fr 1fr;
        }

        .panel {
            background: var(--panel-bg);
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 18px;
            padding: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 10px 30px rgba(2,6,23,.45);
        }

        .headline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .event-name { color: #bfdbfe; font-weight: 600; }
        .status-pill {
            padding: 7px 12px;
            border-radius: 999px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--primary-color), #ec4899);
        }

        .now-grid { display: grid; grid-template-columns: 1fr 140px; gap: 12px; align-items: center; }
        .song-title { font-size: clamp(30px, 5vw, 56px); font-weight: 800; color: #fbbf24; line-height: 1; }
        .song-artist { font-size: clamp(18px, 2vw, 28px); color: #f1f5f9; margin-top: 6px; }
        .singer { color: #cbd5e1; margin-top: 8px; }
        .microphone {
            width: 140px; height: 140px; border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #fbbf24, #7c2d12 65%);
            box-shadow: 0 0 25px rgba(245, 158, 11, .5);
        }

        .list { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
        .list li { padding-bottom: 10px; border-bottom: 1px solid rgba(148,163,184,.2); }
        .queue-meta { color: #94a3b8; font-size: 13px; margin-top: 4px; }

        .overlay-texts { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .overlay-text {
            background: rgba(15, 23, 42, .8);
            border: 1px solid rgba(59,130,246,.4);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 13px;
        }

        .cta {
            margin-top: 14px;
            padding: 12px;
            border-radius: 12px;
            background: linear-gradient(90deg, rgba(245,158,11,.28), rgba(244,63,94,.25));
            font-weight: 700;
            text-align: center;
        }

        .brand-block {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }

        .event-logo {
            width: 110px;
            max-height: 64px;
            object-fit: contain;
            background: rgba(15,23,42,.8);
            border-radius: 10px;
            padding: 8px;
            border: 1px solid rgba(148,163,184,.3);
        }

        .sponsor {
            margin-top: 14px;
            border-radius: 14px;
            border: 1px solid rgba(244, 114, 182, .4);
            background: rgba(91, 33, 182, .22);
            padding: 10px;
        }

        .sponsor-head { display: flex; gap: 10px; align-items: center; }
        .sponsor-logo { width: 54px; height: 54px; object-fit: contain; border-radius: 10px; background: #0f172a; padding: 6px; }
        .sponsor-title { color: #f9a8d4; font-weight: 800; }
        .sponsor-subtitle { color: #e2e8f0; font-size: 14px; }

        .sponsor-image { margin-top: 10px; width: 100%; max-height: 180px; object-fit: cover; border-radius: 12px; }

        .footer-ticker {
            border-radius: 12px;
            padding: 10px 14px;
            background: linear-gradient(90deg, rgba(30,64,175,.55), rgba(194,65,12,.55));
            font-weight: 700;
            letter-spacing: .3px;
            color: #fef3c7;
        }

        @media (max-width: 1000px) {
            .content { grid-template-columns: 1fr; }
            .now-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="screen">
    <div class="top-banner">Playing now!</div>

    <div class="content">
        <section class="panel">
            <div class="headline">
                <div>
                    <div style="opacity:.8; font-weight:700;">Ora in corso</div>
                    <div class="event-name" id="event-name"></div>
                </div>
                <div class="status-pill" id="playback-status"></div>
            </div>

            <div class="now-grid">
                <div>
                    <div class="song-title" id="now-title">—</div>
                    <div class="song-artist" id="now-artist"></div>
                    <div class="singer" id="playback-updated"></div>
                </div>
                <div class="microphone" aria-hidden="true"></div>
            </div>

            <div class="overlay-texts" id="overlay-texts"></div>
            <div class="cta" id="cta">Scansiona il QR e prenota la tua canzone: fai salire l'energia!</div>

            <div class="brand-block">
                <div id="theme-updated" style="font-size:13px;color:#cbd5e1;"></div>
                <img class="event-logo" id="event-logo" alt="Logo evento" hidden>
            </div>
        </section>

        <aside class="panel">
            <h3 style="margin:0 0 8px;">Prossime canzoni</h3>
            <ul class="list" id="next-list"></ul>

            <h3 style="margin:16px 0 8px;">Canzoni recenti</h3>
            <ul class="list" id="recent-list"></ul>

            <div style="margin-top:10px; font-size:13px; color:#bfdbfe;" id="queue-stats"></div>

            <div class="sponsor" id="banner" hidden>
                <div class="sponsor-head">
                    <img class="sponsor-logo" id="banner-logo" alt="Logo sponsor" hidden>
                    <div>
                        <div class="sponsor-title" id="banner-title"></div>
                        <div class="sponsor-subtitle" id="banner-subtitle"></div>
                    </div>
                </div>
                <img class="sponsor-image" id="banner-image" alt="Banner pubblicitario" hidden>
            </div>
        </aside>
    </div>

    <div class="footer-ticker" id="footer-ticker">Benvenuti al karaoke! Divertitevi e cantate con noi!</div>
</div>

<script>
    const initialState = @json($state);
    const realtimeEnabled = @json($realtimeEnabled);
    const pollMs = @json($pollSeconds * 1000);
    const stateUrl = @json(route('public.screen.state', $eventNight->code));
    const streamUrl = @json(route('public.screen.stream', $eventNight->code));

    const elements = {
        eventName: document.getElementById('event-name'),
        playbackStatus: document.getElementById('playback-status'),
        nowTitle: document.getElementById('now-title'),
        nowArtist: document.getElementById('now-artist'),
        playbackUpdated: document.getElementById('playback-updated'),
        nextList: document.getElementById('next-list'),
        recentList: document.getElementById('recent-list'),
        queueStats: document.getElementById('queue-stats'),
        banner: document.getElementById('banner'),
        bannerImage: document.getElementById('banner-image'),
        bannerTitle: document.getElementById('banner-title'),
        bannerSubtitle: document.getElementById('banner-subtitle'),
        bannerLogo: document.getElementById('banner-logo'),
        themeUpdated: document.getElementById('theme-updated'),
        overlayTexts: document.getElementById('overlay-texts'),
        footerTicker: document.getElementById('footer-ticker'),
        cta: document.getElementById('cta'),
        eventLogo: document.getElementById('event-logo'),
    };

    let state = initialState;

    const resolveTimezone = () => state?.event?.timezone || 'Europe/Rome';

    const formatTime = (isoValue) => {
        const date = isoValue ? new Date(isoValue) : null;
        if (!date || Number.isNaN(date.getTime())) {
            return '';
        }
        try {
            return date.toLocaleTimeString('it-IT', { timeZone: resolveTimezone() });
        } catch (error) {
            return date.toLocaleTimeString('it-IT');
        }
    };

    const formatSong = (song) => {
        if (!song) {
            return 'Nessuna canzone in coda';
        }
        return song.artist ? `${song.artist} · ${song.title}` : song.title;
    };

    const renderList = (container, items, emptyMessage) => {
        container.innerHTML = '';
        if (!items || items.length === 0) {
            const empty = document.createElement('li');
            empty.textContent = emptyMessage;
            container.appendChild(empty);
            return;
        }

        items.forEach((item) => {
            const li = document.createElement('li');
            const title = document.createElement('div');
            title.textContent = formatSong(item);
            const meta = document.createElement('div');
            meta.className = 'queue-meta';
            if (item.position) {
                meta.textContent = `Posizione ${item.position}`;
            } else if (item.played_at) {
                meta.textContent = `Riprodotta alle ${formatTime(item.played_at)}`;
            }
            li.appendChild(title);
            li.appendChild(meta);
            container.appendChild(li);
        });
    };

    const updatePlayback = (playback) => {
        const song = playback.song;
        elements.nowTitle.textContent = song?.title ?? 'Momento DJ / pausa';
        elements.nowArtist.textContent = song?.artist ?? 'Preparati: la prossima potresti essere tu!';
        elements.playbackStatus.textContent = playback.state ? playback.state.toUpperCase() : 'IN ATTESA';

        elements.playbackUpdated.textContent = playback.expected_end_at
            ? `Fine prevista ${formatTime(playback.expected_end_at)}`
            : 'A breve nuova estrazione';
    };

    const updateQueue = (queue) => {
        renderList(elements.nextList, queue.next, 'Nessuna canzone in coda al momento.');
        renderList(elements.recentList, queue.recent, 'Nessuna canzone riprodotta finora.');

        const queued = queue.stats?.queued_count ?? 0;
        const played = queue.stats?.played_count ?? 0;
        elements.queueStats.textContent = `${queued} in coda · ${played} esibizioni completate`;
    };

    const updateTheme = (theme) => {
        const config = theme.theme?.config || {};
        const primary = config.primaryColor || '#7c3aed';
        const secondary = config.secondaryColor || '#0f172a';
        document.documentElement.style.setProperty('--primary-color', primary);
        document.documentElement.style.setProperty('--secondary-color', secondary);

        if (theme.background_image_url) {
            document.body.style.backgroundImage = `linear-gradient(145deg, rgba(2,6,23,.75), rgba(30,41,59,.66)), url('${theme.background_image_url}')`;
        }

        if (theme.public_logo_url) {
            elements.eventLogo.hidden = false;
            elements.eventLogo.src = theme.public_logo_url;
        } else {
            elements.eventLogo.hidden = true;
            elements.eventLogo.removeAttribute('src');
        }

        if (theme.banner && theme.banner.is_active) {
            elements.banner.hidden = false;
            elements.bannerTitle.textContent = theme.banner.title ?? '';
            elements.bannerSubtitle.textContent = theme.banner.subtitle ?? '';

            if (theme.banner.logo_url) {
                elements.bannerLogo.hidden = false;
                elements.bannerLogo.src = theme.banner.logo_url;
            } else {
                elements.bannerLogo.hidden = true;
                elements.bannerLogo.removeAttribute('src');
            }

            if (theme.banner.image_url) {
                elements.bannerImage.hidden = false;
                elements.bannerImage.src = theme.banner.image_url;
            } else {
                elements.bannerImage.hidden = true;
                elements.bannerImage.removeAttribute('src');
            }
        } else {
            elements.banner.hidden = true;
        }

        elements.overlayTexts.innerHTML = '';
        (theme.overlay_texts || []).forEach((text) => {
            const chip = document.createElement('div');
            chip.className = 'overlay-text';
            chip.textContent = text;
            elements.overlayTexts.appendChild(chip);
        });

        elements.themeUpdated.textContent = theme.theme?.name ? `Tema: ${theme.theme.name}` : 'Tema personalizzato';

        const tickerParts = [
            'Benvenuti al karaoke!',
            'Divertitevi e cantate con noi!',
            theme.banner?.title ? `Sponsor: ${theme.banner.title}` : null,
        ].filter(Boolean);
        elements.footerTicker.textContent = tickerParts.join(' · ');

        elements.cta.textContent = `Vuoi cantare? Vai su /e/${state?.event?.code ?? ''} e mettiti in coda subito!`;
    };

    const renderState = (nextState) => {
        if (!nextState) {
            return;
        }

        state = nextState;

        if (state.event) {
            elements.eventName.textContent = state.event.venue
                ? `${state.event.venue} · Evento ${state.event.code}`
                : state.event.code;
        }
        if (state.playback) {
            updatePlayback(state.playback);
        }
        if (state.queue) {
            updateQueue(state.queue);
        }
        if (state.theme) {
            updateTheme(state.theme);
        }
    };

    const startPolling = () => {
        const poll = async () => {
            try {
                const response = await fetch(stateUrl, { cache: 'no-store' });
                if (!response.ok) {
                    return;
                }
                renderState(await response.json());
            } catch (error) {
                console.warn('Polling failed', error);
            }
        };

        poll();
        setInterval(poll, pollMs);
    };

    const startRealtime = () => {
        if (!realtimeEnabled || typeof EventSource === 'undefined') {
            startPolling();
            return;
        }

        let fallbackStarted = false;
        const source = new EventSource(streamUrl);

        source.addEventListener('snapshot', (event) => renderState(JSON.parse(event.data)));
        source.addEventListener('playback', (event) => updatePlayback(JSON.parse(event.data)));
        source.addEventListener('queue', (event) => updateQueue(JSON.parse(event.data)));
        source.addEventListener('theme', (event) => updateTheme(JSON.parse(event.data)));
        source.addEventListener('error', () => {
            if (fallbackStarted) {
                return;
            }
            fallbackStarted = true;
            source.close();
            startPolling();
        });
    };

    renderState(initialState);
    startRealtime();
</script>
</body>
</html>
