<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Schermo</title>
    <style>
        :root {
            --primary-color: #38bdf8;
            --secondary-color: #0f172a;
            --panel-color: rgba(15, 23, 42, 0.85);
            --text-color: #f8fafc;
            --highlight-color: #f472b6;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, rgba(56, 189, 248, 0.2), transparent 45%),
                radial-gradient(circle at 20% 20%, rgba(244, 114, 182, 0.2), transparent 40%),
                var(--secondary-color);
            color: var(--text-color);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        .glow-orb {
            position: fixed;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.5), transparent 65%);
            filter: blur(4px);
            opacity: 0.7;
            animation: float 14s ease-in-out infinite;
            z-index: 0;
        }
        .glow-orb.orb-1 { top: 40px; left: 40px; }
        .glow-orb.orb-2 { bottom: 60px; right: 60px; animation-delay: -4s; }
        .glow-orb.orb-3 { top: 35%; right: 20%; animation-delay: -8s; background: radial-gradient(circle, rgba(244, 114, 182, 0.5), transparent 65%); }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-18px); }
        }
        header {
            position: relative;
            z-index: 2;
            padding: 28px 32px 18px;
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
        }
        .brand {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .event-logo {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.4);
        }
        .event-venue {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: rgba(248, 250, 252, 0.7);
            margin: 0 0 6px 0;
        }
        .event-title {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        .event-subtitle {
            margin: 8px 0 0 0;
            font-size: 16px;
            color: rgba(248, 250, 252, 0.8);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.2);
            border: 1px solid rgba(56, 189, 248, 0.4);
            color: var(--primary-color);
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.3);
        }
        .hero-actions {
            display: flex;
            gap: 16px;
            align-items: stretch;
        }
        .cta-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 16px 20px;
            min-width: 240px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.35);
        }
        .cta-label {
            font-size: 12px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(248, 250, 252, 0.6);
        }
        .cta-code {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin-top: 10px;
            font-size: 18px;
        }
        .cta-code strong {
            font-size: 28px;
            color: var(--highlight-color);
        }
        .cta-url {
            margin-top: 8px;
            font-size: 14px;
            color: rgba(248, 250, 252, 0.8);
        }
        .cta-hint {
            margin-top: 6px;
            font-size: 12px;
            color: rgba(248, 250, 252, 0.6);
        }
        .info-chips {
            display: grid;
            gap: 10px;
        }
        .chip {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            color: rgba(248, 250, 252, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        main {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 24px;
            padding: 8px 32px 32px;
        }
        .panel {
            background: var(--panel-color);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .now-playing h2,
        .queue h2 {
            margin-top: 0;
            font-size: 20px;
        }
        .song-title {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .song-artist {
            font-size: 20px;
            color: var(--primary-color);
            margin-bottom: 18px;
        }
        .lyrics {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 16px;
            padding: 18px;
            white-space: pre-wrap;
            min-height: 160px;
            font-size: 16px;
            line-height: 1.6;
            position: relative;
            overflow: hidden;
        }
        .lyrics::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.08), transparent);
            animation: shimmer 6s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
            100% { transform: translateX(100%); }
        }
        .queue-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 12px;
        }
        .queue-item {
            padding: 12px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.2);
            display: grid;
            gap: 6px;
        }
        .queue-item.highlight {
            border-color: rgba(244, 114, 182, 0.5);
            box-shadow: 0 10px 18px rgba(244, 114, 182, 0.2);
        }
        .queue-meta {
            color: rgba(226, 232, 240, 0.8);
            font-size: 13px;
        }
        .ad-card {
            margin-top: 20px;
            border-radius: 18px;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .ad-media {
            width: 100%;
            height: 140px;
            overflow: hidden;
        }
        .ad-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ad-content {
            display: flex;
            gap: 14px;
            align-items: center;
            padding: 14px 16px 18px;
        }
        .ad-logo {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: #fff;
            padding: 6px;
            object-fit: contain;
        }
        .ad-title {
            font-size: 18px;
            font-weight: 600;
        }
        .ad-subtitle {
            font-size: 14px;
            color: rgba(248, 250, 252, 0.7);
        }
        .announcements {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }
        .announcement {
            background: rgba(56, 189, 248, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 14px;
        }
        .updated-at {
            font-size: 12px;
            color: rgba(226, 232, 240, 0.7);
            margin-top: 10px;
        }
        @media (max-width: 1024px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            .hero-actions {
                width: 100%;
                flex-direction: column;
            }
        }
        @media (max-width: 900px) {
            main {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="glow-orb orb-1"></div>
<div class="glow-orb orb-2"></div>
<div class="glow-orb orb-3"></div>
<header>
    <div class="brand">
        <img id="event-logo" class="event-logo" alt="Logo evento" hidden>
        <div>
            <div class="event-venue" id="event-venue"></div>
            <h1 class="event-title" id="event-title">Karaoke Night</h1>
            <p class="event-subtitle" id="event-subtitle">Sali sul palco, scegli la tua hit e canta con noi.</p>
        </div>
    </div>
    <div class="hero-actions">
        <div class="cta-card">
            <div class="cta-label">Partecipa ora</div>
            <div class="cta-code">
                <span>Codice evento</span>
                <strong id="event-code">—</strong>
            </div>
            <div class="cta-url" id="event-url"></div>
            <div class="cta-hint">Apri il link dal telefono per iscriverti alla tua canzone.</div>
        </div>
        <div class="info-chips">
            <div class="chip">Inizio serata: <strong id="event-start">—</strong></div>
            <div class="chip">Ora locale: <strong id="local-time">—</strong></div>
            <div class="chip">Prossimi in coda: <strong id="queue-count">0</strong></div>
            <div class="status-pill" id="playback-status"></div>
        </div>
    </div>
</header>

<main>
    <section class="panel now-playing">
        <h2>Canzone in corso</h2>
        <div class="song-title" id="now-title">—</div>
        <div class="song-artist" id="now-artist"></div>
        <div class="lyrics" id="now-lyrics">In attesa della prossima canzone...</div>
        <div class="updated-at" id="playback-updated"></div>
        <div class="announcements" id="overlay-texts"></div>
    </section>

    <section class="panel queue">
        <h2>Prossime in scaletta</h2>
        <ul class="queue-list" id="next-list"></ul>

        <h2 style="margin-top: 22px;">Appena cantate</h2>
        <ul class="queue-list" id="recent-list"></ul>

        <div class="ad-card" id="banner" hidden>
            <div class="ad-media">
                <img id="banner-image" alt="Banner pubblicitario">
            </div>
            <div class="ad-content">
                <img id="banner-logo" class="ad-logo" alt="Logo sponsor" hidden>
                <div>
                    <div class="ad-title" id="banner-title"></div>
                    <div class="ad-subtitle" id="banner-subtitle"></div>
                </div>
            </div>
        </div>
        <div class="updated-at" id="theme-updated"></div>
    </section>
</main>
<script>
    const initialState = @json($state);
    const realtimeEnabled = @json($realtimeEnabled);
    const pollMs = @json($pollSeconds * 1000);
    const stateUrl = @json(route('public.screen.state', $eventNight->code));
    const streamUrl = @json(route('public.screen.stream', $eventNight->code));

    const elements = {
        eventVenue: document.getElementById('event-venue'),
        eventTitle: document.getElementById('event-title'),
        eventSubtitle: document.getElementById('event-subtitle'),
        eventCode: document.getElementById('event-code'),
        eventStart: document.getElementById('event-start'),
        eventUrl: document.getElementById('event-url'),
        eventLogo: document.getElementById('event-logo'),
        localTime: document.getElementById('local-time'),
        queueCount: document.getElementById('queue-count'),
        playbackStatus: document.getElementById('playback-status'),
        nowTitle: document.getElementById('now-title'),
        nowArtist: document.getElementById('now-artist'),
        nowLyrics: document.getElementById('now-lyrics'),
        playbackUpdated: document.getElementById('playback-updated'),
        nextList: document.getElementById('next-list'),
        recentList: document.getElementById('recent-list'),
        banner: document.getElementById('banner'),
        bannerImage: document.getElementById('banner-image'),
        bannerLogo: document.getElementById('banner-logo'),
        bannerTitle: document.getElementById('banner-title'),
        bannerSubtitle: document.getElementById('banner-subtitle'),
        themeUpdated: document.getElementById('theme-updated'),
        overlayTexts: document.getElementById('overlay-texts'),
    };

    let state = initialState;

    const resolveTimezone = () => state?.event?.timezone || 'Europe/Rome';
    const formatTime = (isoValue, options = {}) => {
        const date = isoValue ? new Date(isoValue) : null;
        if (!date || Number.isNaN(date.getTime())) {
            return '';
        }
        try {
            return date.toLocaleTimeString('it-IT', { timeZone: resolveTimezone(), ...options });
        } catch (error) {
            return date.toLocaleTimeString('it-IT', options);
        }
    };

    const formatDateTime = (isoValue) => {
        const date = isoValue ? new Date(isoValue) : null;
        if (!date || Number.isNaN(date.getTime())) {
            return '—';
        }
        try {
            return date.toLocaleString('it-IT', {
                timeZone: resolveTimezone(),
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch (error) {
            return date.toLocaleString('it-IT');
        }
    };

    const updateLocalTime = () => {
        elements.localTime.textContent = formatTime(new Date().toISOString(), { hour: '2-digit', minute: '2-digit' }) || '—';
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
        elements.nowLyrics.textContent = song?.lyrics || 'Testo non disponibile.';
        elements.playbackStatus.textContent = playback.state ? playback.state.toUpperCase() : 'IN ATTESA';
        elements.playbackUpdated.textContent = playback.expected_end_at
            ? `Fine prevista: ${formatTime(playback.expected_end_at, { hour: '2-digit', minute: '2-digit' })}`
            : '';
    };

    const renderList = (container, items, emptyMessage) => {
        container.innerHTML = '';
        if (!items || items.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'queue-item';
            empty.textContent = emptyMessage;
            container.appendChild(empty);
            return;
        }
        items.forEach((item, index) => {
            const li = document.createElement('li');
            li.className = `queue-item${index === 0 ? ' highlight' : ''}`;
            const title = document.createElement('div');
            title.textContent = formatSong(item);
            const meta = document.createElement('div');
            meta.className = 'queue-meta';
            if (item.position) {
                meta.textContent = `Posizione ${item.position}`;
            } else if (item.played_at) {
                meta.textContent = `Riprodotta ${formatTime(item.played_at, { hour: '2-digit', minute: '2-digit' })}`;
            }
            li.appendChild(title);
            li.appendChild(meta);
            container.appendChild(li);
        });
    };

    const updateQueue = (queue) => {
        renderList(elements.nextList, queue.next, 'Nessuna prossima canzone al momento.');
        renderList(elements.recentList, queue.recent, 'Nessuna canzone riprodotta finora.');
        elements.queueCount.textContent = queue.next?.length ?? 0;
    };

    const updateTheme = (theme) => {
        const config = theme.theme?.config || {};
        const primary = config.primaryColor || '#38bdf8';
        const secondary = config.secondaryColor || '#0f172a';
        const highlight = config.highlightColor || '#f472b6';
        document.documentElement.style.setProperty('--primary-color', primary);
        document.documentElement.style.setProperty('--secondary-color', secondary);
        document.documentElement.style.setProperty('--highlight-color', highlight);
        document.documentElement.style.setProperty('--panel-color', 'rgba(15, 23, 42, 0.85)');

        if (theme.background_image_url) {
            document.body.style.backgroundImage = `url('${theme.background_image_url}')`;
        } else {
            document.body.style.backgroundImage = '';
        }

        if (theme.logo_image_url) {
            elements.eventLogo.hidden = false;
            elements.eventLogo.src = theme.logo_image_url;
        } else {
            elements.eventLogo.hidden = true;
            elements.eventLogo.removeAttribute('src');
        }

        if (theme.banner && theme.banner.is_active && theme.banner.image_url) {
            elements.banner.hidden = false;
            elements.bannerImage.src = theme.banner.image_url;
            elements.bannerTitle.textContent = theme.banner.title ?? '';
            elements.bannerSubtitle.textContent = theme.banner.subtitle ?? '';
            if (theme.banner.logo_url) {
                elements.bannerLogo.hidden = false;
                elements.bannerLogo.src = theme.banner.logo_url;
            } else {
                elements.bannerLogo.hidden = true;
                elements.bannerLogo.removeAttribute('src');
            }
        } else {
            elements.banner.hidden = true;
            elements.bannerImage.removeAttribute('src');
            elements.bannerLogo.removeAttribute('src');
            elements.bannerTitle.textContent = '';
            elements.bannerSubtitle.textContent = '';
        }

        elements.overlayTexts.innerHTML = '';
        const overlays = theme.overlay_texts || [];
        overlays.forEach((text) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'announcement';
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
            elements.eventVenue.textContent = state.event.venue
                ? state.event.venue.toUpperCase()
                : 'KARAOKE NIGHT';
            elements.eventCode.textContent = state.event.code ?? '—';
            const baseUrl = window.location.origin;
            elements.eventUrl.textContent = state.event.code ? `${baseUrl}/e/${state.event.code}` : baseUrl;
            elements.eventStart.textContent = state.event.starts_at ? formatDateTime(state.event.starts_at) : '—';
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
        updateLocalTime();
    };

    const startPolling = () => {
        const poll = async () => {
            try {
                const response = await fetch(stateUrl, { cache: 'no-store' });
                if (!response.ok) {
                    return;
                }
                const nextState = await response.json();
                renderState(nextState);
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
    updateLocalTime();
    setInterval(updateLocalTime, 60000);
    startRealtime();
</script>
</body>
</html>
