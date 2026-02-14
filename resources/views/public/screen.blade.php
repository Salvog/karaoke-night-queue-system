<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Schermo</title>
    <style>
        :root {
            --primary-color: #67e8f9;
            --secondary-color: #070b1f;
            --panel-color: rgba(10, 16, 40, 0.78);
            --text-color: #f8fafc;
            --accent-pink: #f472b6;
            --accent-gold: #fbbf24;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, "Segoe UI", sans-serif;
            color: var(--text-color);
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 15%, rgba(244, 114, 182, 0.25), transparent 38%),
                radial-gradient(circle at 85% 10%, rgba(56, 189, 248, 0.28), transparent 36%),
                radial-gradient(circle at 50% 100%, rgba(251, 191, 36, 0.2), transparent 32%),
                var(--secondary-color);
            background-size: cover;
            background-position: center;
        }
        .screen-shell { padding: 18px; }
        .neon-border {
            border: 1px solid rgba(167, 139, 250, 0.5);
            box-shadow: 0 0 0 1px rgba(103, 232, 249, 0.18), 0 22px 45px rgba(3, 7, 18, 0.6), inset 0 0 22px rgba(103, 232, 249, 0.08);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(4px);
        }
        .top-bar {
            background: linear-gradient(90deg, rgba(30, 41, 59, 0.86), rgba(76, 29, 149, 0.82));
            padding: 14px 20px;
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            gap: 14px;
        }
        .live-pill {
            justify-self: center;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #f5d0fe;
            border: 2px solid rgba(244, 114, 182, 0.8);
            border-radius: 999px;
            padding: 6px 24px;
            text-shadow: 0 0 12px rgba(244, 114, 182, 0.8);
            animation: pulse 1.8s infinite ease-in-out;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 10px rgba(244, 114, 182, 0.6); }
            50% { box-shadow: 0 0 22px rgba(103, 232, 249, 0.9); }
        }
        .brand-wrap { display:flex; align-items:center; gap:12px; min-width:0; }
        .event-logo {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            object-fit: contain;
            background: rgba(15, 23, 42, 0.9);
            padding: 8px;
            border: 1px solid rgba(103, 232, 249, 0.45);
        }
        .event-logo-fallback {
            width:64px;height:64px;border-radius:14px;display:flex;align-items:center;justify-content:center;
            font-weight:800;background:rgba(15,23,42,.92);border:1px solid rgba(103,232,249,.45);
        }
        .event-name { font-size: 18px; font-weight: 700; color: var(--primary-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .event-meta { font-size: 13px; opacity: 0.9; }
        .status-pill {
            font-size: 12px;
            letter-spacing: .05em;
            font-weight: 800;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(103, 232, 249, 0.4);
        }
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 14px;
            padding: 14px;
        }
        .panel { background: var(--panel-color); border-radius: 16px; padding: 16px; border: 1px solid rgba(148,163,184,.2); }
        .section-label { margin:0 0 12px; font-size: 14px; text-transform: uppercase; letter-spacing: .09em; color: #c4b5fd; }
        .now-title { font-size: clamp(28px, 4vw, 52px); margin: 0; color: var(--accent-gold); text-shadow: 0 0 14px rgba(251,191,36,.3); }
        .now-artist { font-size: clamp(20px, 2.4vw, 34px); margin: 8px 0 4px; color: #f9a8d4; }
        .now-singer { margin:0 0 14px; font-weight: 600; color: rgba(248,250,252,.95); }
        .lyrics { min-height: 140px; max-height: 220px; overflow: auto; padding: 12px; border-radius: 12px; background: rgba(15,23,42,.75); white-space: pre-wrap; }
        .sub-grid { margin-top: 14px; display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .queue-list { list-style:none; margin:0; padding:0; display:grid; gap:8px; }
        .queue-list li { padding:10px; border-radius:10px; background: rgba(15,23,42,.65); border:1px solid rgba(148,163,184,.2); }
        .queue-meta { font-size:12px; opacity:.75; margin-top:4px; }
        .cta {
            margin-top: 14px;
            background: linear-gradient(90deg, rgba(251,191,36,.22), rgba(244,114,182,.2));
            border: 1px solid rgba(251,191,36,.4);
            border-radius: 12px;
            padding: 10px 12px;
            font-weight: 600;
        }
        .join-url { font-size: 13px; color: var(--primary-color); word-break: break-all; margin-top: 6px; }
        .banner-card {
            min-height: 220px;
            background-size: cover;
            background-position: center;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(248,250,252,.25);
        }
        .banner-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(15,23,42,.2), rgba(15,23,42,.88));
            padding: 14px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 8px;
        }
        .sponsor-logo {
            width: 58px; height: 58px; object-fit: contain; border-radius: 999px;
            border: 2px solid rgba(248,250,252,.8); background: rgba(15,23,42,.82); padding: 7px;
        }
        .banner-title { margin:0; font-size: 22px; color: #fde68a; }
        .banner-subtitle { margin:0; font-size: 14px; color: #f8fafc; opacity: .92; }
        .overlay-strip { margin-top: 12px; display:flex; flex-wrap:wrap; gap:8px; }
        .overlay-chip { padding: 6px 12px; border-radius: 999px; background: rgba(15,23,42,.8); border: 1px solid rgba(244,114,182,.5); font-size: 13px; }
        .updated-at { margin-top: 10px; font-size: 11px; opacity: .7; }
        @media (max-width: 980px) {
            .content-grid, .sub-grid { grid-template-columns: 1fr; }
            .top-bar { grid-template-columns: 1fr; }
            .live-pill { justify-self: start; }
        }
    </style>
</head>
<body>
<div class="screen-shell">
    <div class="neon-border">
        <header class="top-bar">
            <div class="brand-wrap">
                <img id="event-logo" class="event-logo" alt="Logo evento" hidden>
                <div id="event-logo-fallback" class="event-logo-fallback">KN</div>
                <div>
                    <div class="event-name" id="event-name">Karaoke Night</div>
                    <div class="event-meta" id="event-meta"></div>
                </div>
            </div>
            <div class="live-pill">Playing now!</div>
            <div class="status-pill" id="playback-status">IN ATTESA</div>
        </header>

        <main class="content-grid">
            <section class="panel">
                <p class="section-label">Ora in corso</p>
                <h1 class="now-title" id="now-title">—</h1>
                <h2 class="now-artist" id="now-artist"></h2>
                <p class="now-singer" id="now-singer">Cantata da: —</p>
                <div class="lyrics" id="now-lyrics">In attesa della prossima canzone...</div>

                <div class="sub-grid">
                    <div class="panel" style="padding:12px;">
                        <p class="section-label" style="margin-bottom:8px;">Prossime canzoni</p>
                        <ul class="queue-list" id="next-list"></ul>
                    </div>
                    <div class="panel" style="padding:12px;">
                        <p class="section-label" style="margin-bottom:8px;">Appena cantate</p>
                        <ul class="queue-list" id="recent-list"></ul>
                    </div>
                </div>

                <div class="cta">
                    🎤 Vuoi cantare anche tu? Iscriviti subito dal tuo telefono.
                    <div class="join-url" id="join-url"></div>
                </div>
                <div class="updated-at" id="playback-updated"></div>
            </section>

            <aside class="panel">
                <p class="section-label">Sponsor in evidenza</p>
                <div class="banner-card" id="banner" hidden>
                    <div class="banner-overlay">
                        <img class="sponsor-logo" id="banner-logo" alt="Logo sponsor" hidden>
                        <h3 class="banner-title" id="banner-title"></h3>
                        <p class="banner-subtitle" id="banner-subtitle"></p>
                    </div>
                </div>
                <div id="banner-empty" class="queue-meta">Nessuno sponsor attivo al momento.</div>

                <div class="overlay-strip" id="overlay-texts"></div>
                <div class="updated-at" id="theme-updated"></div>
            </aside>
        </main>
    </div>
</div>
<script>
    const initialState = @json($state);
    const realtimeEnabled = @json($realtimeEnabled);
    const pollMs = @json($pollSeconds * 1000);
    const stateUrl = @json(route('public.screen.state', $eventNight->code));
    const streamUrl = @json(route('public.screen.stream', $eventNight->code));

    let state = initialState;

    const elements = {
        eventName: document.getElementById('event-name'),
        eventMeta: document.getElementById('event-meta'),
        eventLogo: document.getElementById('event-logo'),
        eventLogoFallback: document.getElementById('event-logo-fallback'),
        playbackStatus: document.getElementById('playback-status'),
        nowTitle: document.getElementById('now-title'),
        nowArtist: document.getElementById('now-artist'),
        nowSinger: document.getElementById('now-singer'),
        nowLyrics: document.getElementById('now-lyrics'),
        playbackUpdated: document.getElementById('playback-updated'),
        nextList: document.getElementById('next-list'),
        recentList: document.getElementById('recent-list'),
        banner: document.getElementById('banner'),
        bannerEmpty: document.getElementById('banner-empty'),
        bannerTitle: document.getElementById('banner-title'),
        bannerSubtitle: document.getElementById('banner-subtitle'),
        bannerLogo: document.getElementById('banner-logo'),
        themeUpdated: document.getElementById('theme-updated'),
        overlayTexts: document.getElementById('overlay-texts'),
        joinUrl: document.getElementById('join-url'),
    };

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
        return song.artist ? `${song.title} — ${song.artist}` : song.title;
    };

    const updatePlayback = (playback) => {
        const song = playback.song;
        elements.nowTitle.textContent = song?.title ?? 'Nessuna canzone in riproduzione';
        elements.nowArtist.textContent = song?.artist ?? '';
        elements.nowSinger.textContent = 'Cantata da: in aggiornamento live';
        elements.nowLyrics.textContent = song?.lyrics || 'Testo non disponibile.';
        elements.playbackStatus.textContent = playback.state ? playback.state.toUpperCase() : 'IN ATTESA';
        elements.playbackUpdated.textContent = playback.expected_end_at
            ? `Fine prevista: ${formatTime(playback.expected_end_at)}`
            : '';
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

    const updateQueue = (queue) => {
        renderList(elements.nextList, queue.next, 'Nessuna prossima canzone al momento.');
        renderList(elements.recentList, queue.recent, 'Nessuna canzone riprodotta finora.');
    };

    const updateTheme = (theme) => {
        const config = theme.theme?.config || {};
        const primary = config.primaryColor || '#67e8f9';
        const secondary = config.secondaryColor || '#070b1f';
        document.documentElement.style.setProperty('--primary-color', primary);
        document.documentElement.style.setProperty('--secondary-color', secondary);

        if (theme.background_image_url) {
            document.body.style.backgroundImage = `url('${theme.background_image_url}'), radial-gradient(circle at 15% 15%, rgba(244, 114, 182, 0.25), transparent 38%), radial-gradient(circle at 85% 10%, rgba(56, 189, 248, 0.28), transparent 36%), var(--secondary-color)`;
        }

        if (theme.event_logo_url) {
            elements.eventLogo.hidden = false;
            elements.eventLogo.src = theme.event_logo_url;
            elements.eventLogoFallback.hidden = true;
        } else {
            elements.eventLogo.hidden = true;
            elements.eventLogo.removeAttribute('src');
            elements.eventLogoFallback.hidden = false;
        }

        if (theme.banner && theme.banner.is_active && theme.banner.image_url) {
            elements.banner.hidden = false;
            elements.bannerEmpty.hidden = true;
            elements.banner.style.backgroundImage = `url('${theme.banner.image_url}')`;
            elements.bannerTitle.textContent = theme.banner.title ?? '';
            elements.bannerSubtitle.textContent = theme.banner.subtitle ?? 'Sponsor ufficiale della serata';

            if (theme.banner.logo_url) {
                elements.bannerLogo.hidden = false;
                elements.bannerLogo.src = theme.banner.logo_url;
            } else {
                elements.bannerLogo.hidden = true;
                elements.bannerLogo.removeAttribute('src');
            }
        } else {
            elements.banner.hidden = true;
            elements.bannerEmpty.hidden = false;
            elements.banner.style.backgroundImage = '';
            elements.bannerTitle.textContent = '';
            elements.bannerSubtitle.textContent = '';
            elements.bannerLogo.hidden = true;
            elements.bannerLogo.removeAttribute('src');
        }

        elements.overlayTexts.innerHTML = '';
        const overlays = theme.overlay_texts || [];
        overlays.forEach((text) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'overlay-chip';
            wrapper.textContent = text;
            elements.overlayTexts.appendChild(wrapper);
        });

        elements.themeUpdated.textContent = theme.theme?.name ? `Tema: ${theme.theme.name}` : '';
    };

    const renderState = (nextState) => {
        if (!nextState) {
            return;
        }
        state = nextState;

        if (state.event) {
            elements.eventName.textContent = state.event.venue
                ? `${state.event.venue} · ${state.event.code}`
                : state.event.code;
            elements.eventMeta.textContent = state.event.starts_at
                ? `Start: ${formatTime(state.event.starts_at)} · Codice evento: ${state.event.code}`
                : `Codice evento: ${state.event.code}`;
            elements.joinUrl.textContent = state.event.join_url ?? '';
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
                const data = await response.json();
                renderState(data);
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

        source.addEventListener('snapshot', (event) => {
            renderState(JSON.parse(event.data));
        });
        source.addEventListener('playback', (event) => {
            updatePlayback(JSON.parse(event.data));
        });
        source.addEventListener('queue', (event) => {
            updateQueue(JSON.parse(event.data));
        });
        source.addEventListener('theme', (event) => {
            updateTheme(JSON.parse(event.data));
        });
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
