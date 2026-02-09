<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Schermo</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@500;700;800&display=swap');

        :root {
            --primary-color: #2ad8ff;
            --secondary-color: #0f172a;
            --highlight-color: #ff4fd8;
            --panel-color: rgba(15, 23, 42, 0.8);
            --text-color: #f8fafc;
            --muted-color: rgba(226, 232, 240, 0.7);
            --accent-color: rgba(45, 212, 191, 0.85);
            --glow-color: rgba(99, 102, 241, 0.25);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top, rgba(59, 130, 246, 0.2), transparent 55%),
                radial-gradient(circle at 20% 20%, rgba(255, 79, 216, 0.18), transparent 45%),
                var(--secondary-color);
            color: var(--text-color);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(120deg, rgba(15, 23, 42, 0.95), rgba(2, 6, 23, 0.8));
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.12)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.12)"/><circle cx="50" cy="90" r="1" fill="rgba(255,255,255,0.12)"/></svg>');
            opacity: 0.4;
            z-index: -1;
        }

        .screen-shell {
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 28px 36px 36px;
        }

        .hero {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 24px;
            align-items: stretch;
        }

        .hero-left {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.9));
            border-radius: 24px;
            padding: 24px 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(148, 163, 184, 0.15);
        }

        .event-brand {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .logo-shell {
            width: 90px;
            height: 90px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.12);
            display: grid;
            place-items: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-shell img {
            max-width: 70%;
            max-height: 70%;
            object-fit: contain;
        }

        .eyebrow {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--muted-color);
            margin: 0 0 6px;
        }

        .event-name {
            font-family: 'Poppins', sans-serif;
            font-size: 36px;
            margin: 0;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 14px;
            color: var(--muted-color);
        }

        .event-chip {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 999px;
            padding: 6px 12px;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .hero-right {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .status-card,
        .cta-card {
            background: rgba(15, 23, 42, 0.85);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: var(--highlight-color);
            color: #0f172a;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.1em;
        }

        .status-pill::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.7);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
        }

        .status-timing {
            margin-top: 12px;
            font-size: 14px;
            color: var(--muted-color);
        }

        .cta-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 12px;
        }

        .cta-steps {
            display: grid;
            gap: 12px;
        }

        .cta-step {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 14px;
            color: var(--muted-color);
        }

        .step-number {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            background: var(--primary-color);
            color: #0f172a;
            display: grid;
            place-items: center;
            font-weight: 700;
            flex-shrink: 0;
        }

        .cta-pill {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            padding: 4px 10px;
            color: var(--text-color);
            font-weight: 600;
        }

        .grid {
            display: grid;
            grid-template-columns: 2fr 1.2fr;
            gap: 24px;
        }

        .panel {
            background: var(--panel-color);
            border-radius: 22px;
            padding: 22px;
            border: 1px solid rgba(148, 163, 184, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        .panel h2 {
            margin-top: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .now-playing .song-title {
            font-family: 'Poppins', sans-serif;
            font-size: 34px;
            margin: 0 0 6px;
        }

        .now-playing .song-artist {
            font-size: 20px;
            color: var(--primary-color);
            margin-bottom: 16px;
        }

        .lyrics {
            background: rgba(15, 23, 42, 0.65);
            border-radius: 16px;
            padding: 16px;
            white-space: pre-wrap;
            min-height: 160px;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .now-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .meta-card {
            background: rgba(30, 41, 59, 0.7);
            border-radius: 16px;
            padding: 12px 14px;
            border: 1px solid rgba(148, 163, 184, 0.15);
        }

        .meta-label {
            font-size: 12px;
            color: var(--muted-color);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .meta-value {
            font-size: 16px;
            font-weight: 600;
        }

        .queue-section {
            display: grid;
            gap: 18px;
        }

        .queue-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 12px;
        }

        .queue-list li {
            background: rgba(30, 41, 59, 0.6);
            border-radius: 14px;
            padding: 12px 14px;
            border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .queue-meta {
            color: var(--muted-color);
            font-size: 13px;
        }

        .queue-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .queue-count {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .announcements {
            display: grid;
            gap: 12px;
        }

        .announcement-item {
            padding: 10px 14px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px dashed rgba(148, 163, 184, 0.2);
        }

        .sponsor-card {
            display: grid;
            gap: 12px;
        }

        .sponsor-banner {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(244, 63, 94, 0.2));
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .sponsor-banner img.banner-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
            display: block;
        }

        .sponsor-content {
            padding: 16px;
            display: grid;
            gap: 8px;
        }

        .sponsor-logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.9);
            display: grid;
            place-items: center;
            overflow: hidden;
        }

        .sponsor-logo img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }

        .sponsor-title {
            font-size: 18px;
            font-weight: 700;
        }

        .sponsor-subtitle {
            font-size: 14px;
            color: var(--muted-color);
        }

        .footer-note {
            font-size: 12px;
            color: var(--muted-color);
            margin-top: 8px;
        }

        @media (max-width: 1100px) {
            .hero,
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .screen-shell {
                padding: 20px;
            }

            .event-brand {
                flex-direction: column;
                align-items: flex-start;
            }

            .event-name {
                font-size: 28px;
            }

            .now-playing .song-title {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
<div class="screen-shell">
    <header class="hero">
        <div class="hero-left">
            <div class="event-brand">
                <div class="logo-shell" id="event-logo-shell" hidden>
                    <img id="event-logo" alt="Logo evento">
                </div>
                <div>
                    <p class="eyebrow">Karaoke Night</p>
                    <h1 class="event-name" id="event-name">Serata Karaoke</h1>
                    <div class="event-meta" id="event-meta"></div>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <div class="status-card">
                <div class="status-pill" id="playback-status">IN ATTESA</div>
                <div class="status-timing" id="playback-updated"></div>
            </div>
            <div class="cta-card">
                <div class="cta-title">Pronto a salire sul palco?</div>
                <div class="cta-steps">
                    <div class="cta-step">
                        <div class="step-number">1</div>
                        <div>Vai su <span class="cta-pill" id="join-url"></span> dal tuo telefono.</div>
                    </div>
                    <div class="cta-step">
                        <div class="step-number">2</div>
                        <div>Inserisci il codice evento <span class="cta-pill" id="join-code"></span>.</div>
                    </div>
                    <div class="cta-step">
                        <div class="step-number">3</div>
                        <div>Se richiesto, usa il PIN <span class="cta-pill" id="join-pin"></span>.</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="grid">
        <section class="panel now-playing">
            <h2>Canzone corrente</h2>
            <div class="song-title" id="now-title">—</div>
            <div class="song-artist" id="now-artist"></div>
            <div class="lyrics" id="now-lyrics">In attesa della prossima canzone...</div>
            <div class="now-meta">
                <div class="meta-card">
                    <div class="meta-label">Inizio</div>
                    <div class="meta-value" id="playback-started">—</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">Fine prevista</div>
                    <div class="meta-value" id="playback-eta">—</div>
                </div>
            </div>
        </section>

        <section class="panel queue">
            <div class="queue-section">
                <div>
                    <div class="queue-header">
                        <h2>Prossime in scaletta</h2>
                        <div class="queue-count" id="next-count">0 in attesa</div>
                    </div>
                    <ul class="queue-list" id="next-list"></ul>
                </div>

                <div>
                    <div class="queue-header">
                        <h2>Già cantate</h2>
                        <div class="queue-count" id="recent-count">0 esibizioni</div>
                    </div>
                    <ul class="queue-list" id="recent-list"></ul>
                </div>

                <div>
                    <h2>Annunci & messaggi</h2>
                    <div class="announcements" id="announcements-list"></div>
                </div>

                <div class="sponsor-card" id="banner" hidden>
                    <h2>Sponsor della serata</h2>
                    <div class="sponsor-banner">
                        <img class="banner-image" id="banner-image" alt="Banner sponsor" hidden>
                        <div class="sponsor-content">
                            <div class="sponsor-logo" id="banner-logo-shell" hidden>
                                <img id="banner-logo" alt="Logo sponsor">
                            </div>
                            <div class="sponsor-title" id="banner-title"></div>
                            <div class="sponsor-subtitle" id="banner-subtitle"></div>
                        </div>
                    </div>
                </div>

                <div class="footer-note" id="theme-updated"></div>
            </div>
        </section>
    </main>
</div>
<script>
    const initialState = @json($state);
    const realtimeEnabled = @json($realtimeEnabled);
    const pollMs = @json($pollSeconds * 1000);
    const stateUrl = @json(route('public.screen.state', $eventNight->code));
    const streamUrl = @json(route('public.screen.stream', $eventNight->code));
    const joinUrl = @json(route('public.join.show', $eventNight->code));

    let currentState = initialState;

    const elements = {
        eventName: document.getElementById('event-name'),
        eventMeta: document.getElementById('event-meta'),
        eventLogoShell: document.getElementById('event-logo-shell'),
        eventLogo: document.getElementById('event-logo'),
        playbackStatus: document.getElementById('playback-status'),
        nowTitle: document.getElementById('now-title'),
        nowArtist: document.getElementById('now-artist'),
        nowLyrics: document.getElementById('now-lyrics'),
        playbackUpdated: document.getElementById('playback-updated'),
        playbackStarted: document.getElementById('playback-started'),
        playbackEta: document.getElementById('playback-eta'),
        nextList: document.getElementById('next-list'),
        recentList: document.getElementById('recent-list'),
        nextCount: document.getElementById('next-count'),
        recentCount: document.getElementById('recent-count'),
        banner: document.getElementById('banner'),
        bannerImage: document.getElementById('banner-image'),
        bannerTitle: document.getElementById('banner-title'),
        bannerSubtitle: document.getElementById('banner-subtitle'),
        bannerLogoShell: document.getElementById('banner-logo-shell'),
        bannerLogo: document.getElementById('banner-logo'),
        themeUpdated: document.getElementById('theme-updated'),
        announcementsList: document.getElementById('announcements-list'),
        joinUrl: document.getElementById('join-url'),
        joinCode: document.getElementById('join-code'),
        joinPin: document.getElementById('join-pin'),
    };

    const resolveTimezone = () => currentState?.event?.timezone || 'Europe/Rome';
    const formatTime = (isoValue) => {
        const date = isoValue ? new Date(isoValue) : null;
        if (!date || Number.isNaN(date.getTime())) {
            return '—';
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
        elements.nowLyrics.textContent = song?.lyrics || 'Testo non disponibile.';
        elements.playbackStatus.textContent = playback.state ? playback.state.toUpperCase() : 'IN ATTESA';
        elements.playbackUpdated.textContent = playback.expected_end_at
            ? `Fine prevista: ${formatTime(playback.expected_end_at)}`
            : 'Preparati alla prossima voce!';
        elements.playbackStarted.textContent = playback.started_at ? formatTime(playback.started_at) : '—';
        elements.playbackEta.textContent = playback.expected_end_at ? formatTime(playback.expected_end_at) : '—';
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
        elements.nextCount.textContent = `${queue.next?.length ?? 0} in attesa`;
        elements.recentCount.textContent = `${queue.recent?.length ?? 0} esibizioni`;
    };

    const updateAnnouncements = (texts) => {
        elements.announcementsList.innerHTML = '';
        if (!texts || texts.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'announcement-item';
            empty.textContent = 'Nessun annuncio al momento. Goditi la musica!';
            elements.announcementsList.appendChild(empty);
            return;
        }
        texts.forEach((text) => {
            const item = document.createElement('div');
            item.className = 'announcement-item';
            item.textContent = text;
            elements.announcementsList.appendChild(item);
        });
    };

    const updateTheme = (theme) => {
        const config = theme.theme?.config || {};
        const primary = config.primaryColor || '#2ad8ff';
        const secondary = config.secondaryColor || '#0f172a';
        const highlight = config.highlightColor || '#ff4fd8';
        document.documentElement.style.setProperty('--primary-color', primary);
        document.documentElement.style.setProperty('--secondary-color', secondary);
        document.documentElement.style.setProperty('--highlight-color', highlight);
        document.documentElement.style.setProperty('--panel-color', 'rgba(15, 23, 42, 0.8)');

        if (theme.background_image_url) {
            document.body.style.backgroundImage = `url('${theme.background_image_url}')`;
            document.body.style.backgroundSize = 'cover';
            document.body.style.backgroundPosition = 'center';
        } else {
            document.body.style.backgroundImage = '';
        }

        if (theme.event_logo_url) {
            elements.eventLogoShell.hidden = false;
            elements.eventLogo.src = theme.event_logo_url;
        } else {
            elements.eventLogoShell.hidden = true;
            elements.eventLogo.removeAttribute('src');
        }

        if (theme.banner && theme.banner.is_active) {
            elements.banner.hidden = false;
            if (theme.banner.image_url) {
                elements.bannerImage.hidden = false;
                elements.bannerImage.src = theme.banner.image_url;
            } else {
                elements.bannerImage.hidden = true;
                elements.bannerImage.removeAttribute('src');
            }
            elements.bannerTitle.textContent = theme.banner.title ?? '';
            elements.bannerSubtitle.textContent = theme.banner.subtitle ?? '';
            if (theme.banner.logo_url) {
                elements.bannerLogoShell.hidden = false;
                elements.bannerLogo.src = theme.banner.logo_url;
            } else {
                elements.bannerLogoShell.hidden = true;
                elements.bannerLogo.removeAttribute('src');
            }
        } else {
            elements.banner.hidden = true;
            elements.bannerImage.removeAttribute('src');
            elements.bannerTitle.textContent = '';
            elements.bannerSubtitle.textContent = '';
            elements.bannerLogo.removeAttribute('src');
        }

        updateAnnouncements(theme.overlay_texts || []);

        elements.themeUpdated.textContent = theme.theme?.name ? `Tema: ${theme.theme.name}` : '';
    };

    const updateEventDetails = (event) => {
        if (!event) {
            return;
        }
        elements.eventName.textContent = event.venue
            ? `${event.venue} · ${event.code}`
            : event.code;
        const chips = [];
        if (event.starts_at) {
            chips.push(`Inizio ${formatTime(event.starts_at)}`);
        }
        if (event.ends_at) {
            chips.push(`Fine ${formatTime(event.ends_at)}`);
        }
        elements.eventMeta.innerHTML = chips
            .map((text) => `<span class="event-chip">${text}</span>`)
            .join('');
        elements.joinUrl.textContent = joinUrl.replace(/^https?:\/\//, '');
        elements.joinCode.textContent = event.code;
        elements.joinPin.textContent = event.join_pin ? event.join_pin : 'Nessun PIN';
    };

    const renderState = (state) => {
        if (!state) {
            return;
        }
        currentState = state;
        if (state.event) {
            updateEventDetails(state.event);
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
