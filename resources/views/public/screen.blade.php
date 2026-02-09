<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Schermo</title>
    <style>
        :root {
            --accent: #38bdf8;
            --accent-strong: #7dd3fc;
            --base: #0f172a;
            --panel: rgba(8, 13, 29, 0.85);
            --text: #f8fafc;
            --muted: rgba(226, 232, 240, 0.72);
            --shadow: 0 20px 60px rgba(3, 7, 18, 0.45);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: "Inter", "Segoe UI", sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.15), transparent 45%),
                radial-gradient(circle at top right, rgba(244, 114, 182, 0.18), transparent 45%),
                linear-gradient(160deg, #0b1222 0%, #0f172a 60%, #111827 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 30% 20%, rgba(56, 189, 248, 0.2), transparent 55%),
                radial-gradient(circle at 70% 10%, rgba(168, 85, 247, 0.2), transparent 50%),
                radial-gradient(circle at 50% 80%, rgba(244, 114, 182, 0.18), transparent 60%);
            z-index: -2;
        }
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120" fill="none"><circle cx="12" cy="12" r="1.5" fill="rgba(255,255,255,0.08)"/><circle cx="88" cy="28" r="1" fill="rgba(255,255,255,0.08)"/><circle cx="48" cy="92" r="1.2" fill="rgba(255,255,255,0.08)"/></svg>');
            opacity: 0.4;
            z-index: -1;
            mix-blend-mode: screen;
        }
        .screen {
            padding: 28px 32px 40px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo-wrap {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            display: grid;
            place-items: center;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .logo-wrap img {
            max-width: 56px;
            max-height: 56px;
        }
        .eyebrow {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.24em;
            color: var(--muted);
            margin: 0 0 6px;
        }
        .event-title {
            margin: 0;
            font-size: clamp(26px, 3vw, 36px);
            font-weight: 700;
        }
        .event-meta {
            margin-top: 8px;
            color: var(--muted);
            font-size: 14px;
        }
        .cta-card {
            background: linear-gradient(130deg, rgba(56, 189, 248, 0.18), rgba(168, 85, 247, 0.2));
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 16px 20px;
            min-width: 260px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        .cta-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.15), transparent);
            transform: translateX(-100%);
            animation: shimmer 8s infinite;
        }
        .cta-title {
            font-weight: 700;
            font-size: 18px;
        }
        .cta-body {
            margin-top: 6px;
            font-size: 14px;
            color: var(--muted);
        }
        .cta-code {
            margin-top: 12px;
            font-size: 15px;
            font-weight: 600;
            color: var(--accent-strong);
        }
        .cta-pin {
            margin-top: 6px;
            font-size: 13px;
            color: var(--muted);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 20px;
        }
        .card {
            background: var(--panel);
            border-radius: 20px;
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(148, 163, 184, 0.12);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 15% 20%, rgba(56, 189, 248, 0.16), transparent 60%);
            opacity: 0.7;
            pointer-events: none;
        }
        .card-title {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: var(--muted);
            margin: 0 0 12px;
        }
        .now-playing {
            grid-column: span 7;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(56, 189, 248, 0.2);
            color: var(--accent-strong);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .song-title {
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 700;
        }
        .song-artist {
            font-size: 18px;
            color: var(--accent-strong);
        }
        .lyrics {
            background: rgba(15, 23, 42, 0.65);
            border-radius: 14px;
            padding: 16px;
            min-height: 120px;
            white-space: pre-wrap;
            color: var(--muted);
        }
        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13px;
            color: var(--muted);
        }
        .queue {
            grid-column: span 5;
        }
        .queue-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }
        .queue-item {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.5);
        }
        .queue-item strong {
            font-size: 15px;
        }
        .queue-meta {
            font-size: 12px;
            color: var(--muted);
        }
        .recent {
            grid-column: span 4;
        }
        .announcements {
            grid-column: span 4;
        }
        .announcement-list {
            display: grid;
            gap: 10px;
        }
        .announcement {
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.2);
            font-size: 14px;
        }
        .sponsor {
            grid-column: span 4;
            display: grid;
            gap: 12px;
        }
        .sponsor-card {
            background: linear-gradient(120deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.85));
            border-radius: 16px;
            padding: 16px;
            border: 1px solid rgba(148, 163, 184, 0.18);
        }
        .sponsor-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sponsor-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            display: grid;
            place-items: center;
        }
        .sponsor-logo img {
            max-width: 36px;
            max-height: 36px;
        }
        .sponsor-title {
            font-weight: 700;
            font-size: 18px;
        }
        .sponsor-subtitle {
            color: var(--muted);
            font-size: 13px;
        }
        .sponsor-image {
            width: 100%;
            border-radius: 14px;
            margin-top: 12px;
        }
        .footer {
            text-align: right;
            font-size: 12px;
            color: var(--muted);
        }
        @keyframes shimmer {
            0% {
                transform: translateX(-120%);
                opacity: 0.1;
            }
            30% {
                opacity: 0.3;
            }
            60% {
                transform: translateX(120%);
                opacity: 0;
            }
            100% {
                transform: translateX(120%);
                opacity: 0;
            }
        }
        @media (max-width: 1200px) {
            .now-playing {
                grid-column: span 12;
            }
            .queue,
            .recent,
            .announcements,
            .sponsor {
                grid-column: span 6;
            }
        }
        @media (max-width: 900px) {
            .screen {
                padding: 20px;
            }
            .grid {
                grid-template-columns: 1fr;
            }
            .queue,
            .recent,
            .announcements,
            .sponsor,
            .now-playing {
                grid-column: span 1;
            }
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="screen">
    <header class="topbar">
        <div class="brand">
            <div class="logo-wrap" id="event-logo-wrap" hidden>
                <img id="event-logo" alt="Logo evento">
            </div>
            <div>
                <p class="eyebrow">Karaoke Night</p>
                <h1 class="event-title" id="event-title">Serata Karaoke</h1>
                <div class="event-meta" id="event-meta"></div>
            </div>
        </div>
        <div class="cta-card">
            <div class="cta-title">Vuoi cantare una canzone?</div>
            <div class="cta-body">Apri <span id="join-url"></span> e prenota il tuo posto.</div>
            <div class="cta-code">Codice evento: <span id="event-code"></span></div>
            <div class="cta-pin" id="join-pin" hidden></div>
        </div>
    </header>

    <main class="grid">
        <section class="card now-playing">
            <div class="card-title">In riproduzione</div>
            <div class="status-pill" id="playback-status"></div>
            <div class="song-title" id="now-title">—</div>
            <div class="song-artist" id="now-artist"></div>
            <div class="lyrics" id="now-lyrics">In attesa della prossima canzone...</div>
            <div class="meta-row">
                <div id="playback-updated"></div>
                <div id="playback-start"></div>
            </div>
        </section>

        <section class="card queue">
            <div class="card-title">Prossimi talenti</div>
            <ul class="queue-list" id="next-list"></ul>
        </section>

        <section class="card recent">
            <div class="card-title">Momenti appena cantati</div>
            <ul class="queue-list" id="recent-list"></ul>
        </section>

        <section class="card announcements">
            <div class="card-title">Annunci & messaggi</div>
            <div class="announcement-list" id="announcement-list"></div>
        </section>

        <section class="card sponsor" id="banner" hidden>
            <div class="card-title">Sponsor della serata</div>
            <div class="sponsor-card">
                <div class="sponsor-header">
                    <div class="sponsor-logo" id="banner-logo-wrap" hidden>
                        <img id="banner-logo" alt="Logo sponsor">
                    </div>
                    <div>
                        <div class="sponsor-title" id="banner-title"></div>
                        <div class="sponsor-subtitle" id="banner-subtitle"></div>
                    </div>
                </div>
                <img class="sponsor-image" id="banner-image" alt="Banner pubblicitario" hidden>
            </div>
        </section>
    </main>

    <div class="footer" id="theme-updated"></div>
</div>
<script>
    const initialState = @json($state);
    const realtimeEnabled = @json($realtimeEnabled);
    const pollMs = @json($pollSeconds * 1000);
    const stateUrl = @json(route('public.screen.state', $eventNight->code));
    const streamUrl = @json(route('public.screen.stream', $eventNight->code));

    const elements = {
        eventTitle: document.getElementById('event-title'),
        eventMeta: document.getElementById('event-meta'),
        eventCode: document.getElementById('event-code'),
        joinUrl: document.getElementById('join-url'),
        joinPin: document.getElementById('join-pin'),
        eventLogoWrap: document.getElementById('event-logo-wrap'),
        eventLogo: document.getElementById('event-logo'),
        playbackStatus: document.getElementById('playback-status'),
        nowTitle: document.getElementById('now-title'),
        nowArtist: document.getElementById('now-artist'),
        nowLyrics: document.getElementById('now-lyrics'),
        playbackUpdated: document.getElementById('playback-updated'),
        playbackStart: document.getElementById('playback-start'),
        nextList: document.getElementById('next-list'),
        recentList: document.getElementById('recent-list'),
        banner: document.getElementById('banner'),
        bannerImage: document.getElementById('banner-image'),
        bannerTitle: document.getElementById('banner-title'),
        bannerSubtitle: document.getElementById('banner-subtitle'),
        bannerLogoWrap: document.getElementById('banner-logo-wrap'),
        bannerLogo: document.getElementById('banner-logo'),
        themeUpdated: document.getElementById('theme-updated'),
        announcementList: document.getElementById('announcement-list'),
    };

    let currentState = initialState;
    const resolveTimezone = () => currentState?.event?.timezone || 'Europe/Rome';
    const formatTime = (isoValue, withDate = false) => {
        const date = isoValue ? new Date(isoValue) : null;
        if (!date || Number.isNaN(date.getTime())) {
            return '';
        }
        const options = withDate
            ? { timeZone: resolveTimezone(), hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' }
            : { timeZone: resolveTimezone(), hour: '2-digit', minute: '2-digit' };
        try {
            return date.toLocaleTimeString('it-IT', options);
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
        elements.nowLyrics.textContent = song?.lyrics || 'Testo non disponibile.';
        elements.playbackStatus.textContent = playback.state ? playback.state.toUpperCase() : 'IN ATTESA';
        elements.playbackUpdated.textContent = playback.expected_end_at
            ? `Fine prevista: ${formatTime(playback.expected_end_at)}`
            : '';
        elements.playbackStart.textContent = playback.started_at
            ? `Iniziata: ${formatTime(playback.started_at)}`
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
        items.forEach((item) => {
            const li = document.createElement('li');
            li.className = 'queue-item';
            const title = document.createElement('div');
            title.innerHTML = `<strong>${formatSong(item)}</strong>`;
            const meta = document.createElement('div');
            meta.className = 'queue-meta';
            if (item.position) {
                meta.textContent = `Posizione ${item.position}`;
            } else if (item.played_at) {
                meta.textContent = `Riprodotta ${formatTime(item.played_at)}`;
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
        const primary = config.primaryColor || '#38bdf8';
        const secondary = config.secondaryColor || '#0f172a';
        document.documentElement.style.setProperty('--accent', primary);
        document.documentElement.style.setProperty('--accent-strong', primary);
        document.documentElement.style.setProperty('--base', secondary);
        document.documentElement.style.setProperty('--panel', 'rgba(8, 13, 29, 0.85)');

        if (theme.background_image_url) {
            document.body.style.backgroundImage = `url('${theme.background_image_url}')`;
            document.body.style.backgroundSize = 'cover';
            document.body.style.backgroundPosition = 'center';
        } else {
            document.body.style.backgroundImage = '';
        }

        if (theme.logo_url) {
            elements.eventLogoWrap.hidden = false;
            elements.eventLogo.src = theme.logo_url;
        } else {
            elements.eventLogoWrap.hidden = true;
            elements.eventLogo.removeAttribute('src');
        }

        if (theme.banner && theme.banner.is_active) {
            elements.banner.hidden = false;
            elements.bannerTitle.textContent = theme.banner.title ?? '';
            elements.bannerSubtitle.textContent = theme.banner.subtitle ?? '';
            if (theme.banner.logo_url) {
                elements.bannerLogoWrap.hidden = false;
                elements.bannerLogo.src = theme.banner.logo_url;
            } else {
                elements.bannerLogoWrap.hidden = true;
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
            elements.bannerImage.removeAttribute('src');
            elements.bannerTitle.textContent = '';
            elements.bannerSubtitle.textContent = '';
            elements.bannerLogoWrap.hidden = true;
            elements.bannerLogo.removeAttribute('src');
        }

        elements.announcementList.innerHTML = '';
        const overlays = theme.overlay_texts || [];
        overlays.forEach((text) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'announcement';
            wrapper.textContent = text;
            elements.announcementList.appendChild(wrapper);
        });

        if (overlays.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'announcement';
            empty.textContent = 'Invia la tua richiesta e lascia che la musica parli per te!';
            elements.announcementList.appendChild(empty);
        }

        elements.themeUpdated.textContent = theme.theme?.name ? `Tema attivo: ${theme.theme.name}` : '';
    };

    const updateEvent = (event) => {
        if (!event) {
            return;
        }
        const title = event.venue ? `${event.venue}` : 'Serata Karaoke';
        elements.eventTitle.textContent = title;
        elements.eventCode.textContent = event.code ?? '';
        const joinUrl = `${window.location.origin}/e/${event.code}`;
        elements.joinUrl.textContent = joinUrl;
        const metaParts = [];
        if (event.starts_at) {
            metaParts.push(`Inizio: ${formatTime(event.starts_at, true)}`);
        }
        if (event.ends_at) {
            metaParts.push(`Fine: ${formatTime(event.ends_at, true)}`);
        }
        elements.eventMeta.textContent = metaParts.join(' · ');
        if (event.join_pin) {
            elements.joinPin.hidden = false;
            elements.joinPin.textContent = `PIN di accesso: ${event.join_pin}`;
        } else {
            elements.joinPin.hidden = true;
            elements.joinPin.textContent = '';
        }
    };

    const renderState = (state) => {
        if (!state) {
            return;
        }
        currentState = state;
        if (state.event) {
            updateEvent(state.event);
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
                const state = await response.json();
                renderState(state);
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
