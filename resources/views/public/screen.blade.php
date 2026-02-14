<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Schermo</title>
    <style>
        :root {
            --primary-color: #ff4fd8;
            --secondary-color: #0b1022;
            --accent-color: #55d8ff;
            --panel-color: rgba(16, 24, 54, 0.72);
            --text-color: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", system-ui, sans-serif;
            color: var(--text-color);
            background:
                radial-gradient(circle at 20% 15%, rgba(255, 79, 216, 0.25), transparent 32%),
                radial-gradient(circle at 85% 10%, rgba(85, 216, 255, 0.2), transparent 35%),
                radial-gradient(circle at 50% 90%, rgba(255, 170, 78, 0.18), transparent 40%),
                var(--secondary-color);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0.55));
            z-index: 0;
        }
        .screen {
            position: relative;
            z-index: 1;
            padding: 20px 28px 24px;
            display: grid;
            gap: 16px;
        }
        .neon-card {
            background: var(--panel-color);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 18px;
            box-shadow: 0 0 24px rgba(85, 216, 255, 0.2), inset 0 0 20px rgba(255, 79, 216, 0.08);
            backdrop-filter: blur(8px);
        }
        .topbar {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 16px;
            padding: 14px 18px;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 8px 16px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: .08em;
            background: linear-gradient(90deg, rgba(255, 79, 216, 0.3), rgba(85, 216, 255, 0.3));
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 15px rgba(255, 79, 216, 0.3);
        }
        .event-line {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 10px;
        }
        .event-name {
            font-size: clamp(1.1rem, 1.8vw, 1.5rem);
            font-weight: 700;
        }
        .event-meta {
            color: rgba(226, 232, 240, 0.82);
            font-size: .92rem;
        }
        .event-logo {
            height: 58px;
            max-width: 130px;
            border-radius: 12px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.92);
            padding: 6px;
            display: none;
        }
        .layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
        }
        .main-stage { padding: 20px; }
        .kicker { color: var(--accent-color); font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
        .song-title { font-size: clamp(2rem, 4.3vw, 4rem); margin: 6px 0 4px; line-height: 1.04; }
        .song-artist { color: rgba(226, 232, 240, 0.95); font-size: clamp(1.15rem, 2vw, 1.8rem); margin: 0 0 14px; }
        .lyrics {
            background: rgba(8, 12, 28, 0.45);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 14px;
            padding: 14px;
            min-height: 110px;
            max-height: 180px;
            overflow: auto;
            white-space: pre-wrap;
        }
        .meta-row { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px 16px; color: rgba(226, 232, 240, 0.82); }
        .aside { display: grid; gap: 16px; }
        .aside-panel { padding: 16px; }
        .queue-list { list-style: none; margin: 10px 0 0; padding: 0; }
        .queue-list li { padding: 8px 0; border-bottom: 1px solid rgba(148, 163, 184, .2); }
        .queue-list li:last-child { border-bottom: none; }
        .queue-meta { font-size: .82rem; color: rgba(226, 232, 240, .65); }
        .join-cta {
            background: linear-gradient(120deg, rgba(255,79,216,.25), rgba(85,216,255,.2));
            border-radius: 14px;
            padding: 12px;
            border: 1px solid rgba(255,255,255,.2);
        }
        .join-link {
            margin-top: 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .85rem;
            background: rgba(15, 23, 42, .72);
            border-radius: 10px;
            padding: 8px;
            word-break: break-all;
            border: 1px solid rgba(255,255,255,.15);
        }
        .overlay-texts { display: flex; flex-wrap: wrap; gap: 8px; }
        .overlay-text {
            background: rgba(0,0,0,.35);
            border: 1px solid rgba(255,255,255,.24);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: .88rem;
        }
        .sponsor-strip { padding: 12px 16px; }
        .sponsor-track { display: flex; gap: 10px; overflow: hidden; }
        .sponsor-card {
            min-width: 220px;
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 10px;
            align-items: center;
            background: rgba(7,12,30,.62);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 12px;
            padding: 10px;
        }
        .sponsor-logo {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: contain;
            background: rgba(255,255,255,.92);
            padding: 6px;
        }
        .sponsor-title { font-size: .95rem; font-weight: 700; }
        .sponsor-subtitle { font-size: .78rem; color: rgba(226,232,240,.72); }
        .banner { margin-top: 10px; display: none; }
        .banner-image { width: 100%; max-height: 135px; object-fit: cover; border-radius: 12px; border: 1px solid rgba(255,255,255,.16); }
        .banner-head { margin-top: 8px; display: grid; grid-template-columns: 44px 1fr; gap: 8px; align-items: center; }
        .banner-logo { width: 44px; height: 44px; border-radius: 8px; object-fit: contain; background: rgba(255,255,255,.92); padding: 6px; display: none; }
        .banner-subtitle { font-size: .85rem; color: rgba(226,232,240,.72); }
        .ticker {
            padding: 9px 14px;
            font-weight: 700;
            color: #ffef9f;
            overflow: hidden;
            white-space: nowrap;
        }
        .ticker span { display: inline-block; padding-left: 100%; animation: ticker 20s linear infinite; }
        @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-100%); } }
        @media (max-width: 980px) { .layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="screen">
    <header class="neon-card topbar">
        <div class="pill" id="playback-status">IN ATTESA</div>
        <div>
            <div class="event-line">
                <div class="event-name" id="event-name">Karaoke Night</div>
                <div class="event-meta" id="event-meta"></div>
            </div>
            <div class="overlay-texts" id="overlay-texts"></div>
        </div>
        <img id="event-logo" class="event-logo" alt="Logo evento">
    </header>

    <div class="layout">
        <section class="neon-card main-stage">
            <div class="kicker">Ora in corso</div>
            <h1 class="song-title" id="now-title">—</h1>
            <p class="song-artist" id="now-artist"></p>
            <div class="lyrics" id="now-lyrics">In attesa della prossima canzone...</div>
            <div class="meta-row">
                <span id="playback-updated"></span>
                <span id="theme-updated"></span>
            </div>
            <div class="join-cta" style="margin-top: 14px;">
                <strong>Vuoi cantare anche tu?</strong>
                <div>Iscriviti ora: scegli la tua canzone e sali sul palco 🎤</div>
                <div class="join-link" id="join-link"></div>
            </div>
        </section>

        <aside class="aside">
            <section class="neon-card aside-panel">
                <div class="kicker">Prossime canzoni</div>
                <ul class="queue-list" id="next-list"></ul>
            </section>
            <section class="neon-card aside-panel">
                <div class="kicker">Appena cantate</div>
                <ul class="queue-list" id="recent-list"></ul>

                <div class="banner" id="banner">
                    <img class="banner-image" id="banner-image" alt="Banner sponsor">
                    <div class="banner-head">
                        <img class="banner-logo" id="banner-logo" alt="Logo sponsor">
                        <div>
                            <div id="banner-title"></div>
                            <div class="banner-subtitle" id="banner-subtitle"></div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <div class="neon-card ticker"><span id="ticker-text">Benvenuti al Karaoke! Scegli la tua canzone e partecipa adesso ✨</span></div>

    <footer class="neon-card sponsor-strip">
        <div class="kicker">Sponsor della serata</div>
        <div class="sponsor-track" id="sponsor-track"></div>
    </footer>
</div>

<script>
    const initialState = @json($state);
    const realtimeEnabled = @json($realtimeEnabled);
    const pollMs = @json($pollSeconds * 1000);
    const stateUrl = @json(route('public.screen.state', $eventNight->code));
    const streamUrl = @json(route('public.screen.stream', $eventNight->code));
    const joinUrl = @json(url('/e/' . $eventNight->code));

    const elements = {
        eventName: document.getElementById('event-name'),
        eventMeta: document.getElementById('event-meta'),
        eventLogo: document.getElementById('event-logo'),
        playbackStatus: document.getElementById('playback-status'),
        nowTitle: document.getElementById('now-title'),
        nowArtist: document.getElementById('now-artist'),
        nowLyrics: document.getElementById('now-lyrics'),
        playbackUpdated: document.getElementById('playback-updated'),
        themeUpdated: document.getElementById('theme-updated'),
        nextList: document.getElementById('next-list'),
        recentList: document.getElementById('recent-list'),
        overlayTexts: document.getElementById('overlay-texts'),
        joinLink: document.getElementById('join-link'),
        tickerText: document.getElementById('ticker-text'),
        banner: document.getElementById('banner'),
        bannerImage: document.getElementById('banner-image'),
        bannerLogo: document.getElementById('banner-logo'),
        bannerTitle: document.getElementById('banner-title'),
        bannerSubtitle: document.getElementById('banner-subtitle'),
        sponsorTrack: document.getElementById('sponsor-track'),
    };

    let currentState = initialState;

    const resolveTimezone = () => currentState?.event?.timezone || 'Europe/Rome';
    const formatTime = (isoValue) => {
        const date = isoValue ? new Date(isoValue) : null;
        if (!date || Number.isNaN(date.getTime())) {
            return '';
        }
        try {
            return date.toLocaleTimeString('it-IT', { timeZone: resolveTimezone(), hour: '2-digit', minute: '2-digit' });
        } catch (error) {
            return date.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
        }
    };

    const formatSong = (song) => song?.artist ? `${song.title} — ${song.artist}` : (song?.title || 'Nessuna canzone');

    const updatePlayback = (playback) => {
        const song = playback.song;
        elements.nowTitle.textContent = song?.title ?? 'Nessuna canzone in riproduzione';
        elements.nowArtist.textContent = song?.artist ?? 'Preparati: il palco aspetta te!';
        elements.nowLyrics.textContent = song?.lyrics || 'Testo non disponibile.';

        const statusMap = { playing: 'PLAYING NOW', paused: 'IN PAUSA', idle: 'IN ATTESA', finished: 'TERMINATA' };
        elements.playbackStatus.textContent = statusMap[playback.state] || (playback.state || 'IN ATTESA').toUpperCase();
        elements.playbackUpdated.textContent = playback.expected_end_at ? `Fine prevista: ${formatTime(playback.expected_end_at)}` : '';
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
            meta.textContent = item.position ? `In coda: posizione ${item.position}` : `Cantata alle ${formatTime(item.played_at)}`;
            li.append(title, meta);
            container.appendChild(li);
        });
    };

    const updateQueue = (queue) => {
        renderList(elements.nextList, queue.next, 'La coda è vuota: è il tuo momento!');
        renderList(elements.recentList, queue.recent, 'Nessuna esibizione recente.');
    };

    const renderSponsors = (sponsors) => {
        elements.sponsorTrack.innerHTML = '';
        if (!sponsors || sponsors.length === 0) {
            elements.sponsorTrack.innerHTML = '<div class="sponsor-subtitle">Spazio disponibile per sponsor.</div>';
            return;
        }

        sponsors.forEach((sponsor) => {
            const card = document.createElement('div');
            card.className = 'sponsor-card';

            const logo = document.createElement('img');
            logo.className = 'sponsor-logo';
            logo.alt = `Logo ${sponsor.title || 'sponsor'}`;
            logo.src = sponsor.logo_url || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="%2364748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"%3E%3Cpath d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/%3E%3C/svg%3E';

            const textWrap = document.createElement('div');
            const title = document.createElement('div');
            title.className = 'sponsor-title';
            title.textContent = sponsor.title || 'Sponsor';
            const subtitle = document.createElement('div');
            subtitle.className = 'sponsor-subtitle';
            subtitle.textContent = sponsor.subtitle || 'Partner ufficiale della serata';
            textWrap.append(title, subtitle);

            card.append(logo, textWrap);
            elements.sponsorTrack.appendChild(card);
        });
    };

    const updateTheme = (theme) => {
        const config = theme.theme?.config || {};
        document.documentElement.style.setProperty('--primary-color', config.primaryColor || '#ff4fd8');
        document.documentElement.style.setProperty('--secondary-color', config.secondaryColor || '#0b1022');
        document.documentElement.style.setProperty('--accent-color', config.accentColor || '#55d8ff');

        document.body.style.backgroundImage = theme.background_image_url ? `url('${theme.background_image_url}')` : '';
        elements.themeUpdated.textContent = theme.theme?.name ? `Tema: ${theme.theme.name}` : '';

        if (theme.event_logo_url) {
            elements.eventLogo.style.display = 'block';
            elements.eventLogo.src = theme.event_logo_url;
        } else {
            elements.eventLogo.style.display = 'none';
            elements.eventLogo.removeAttribute('src');
        }

        elements.overlayTexts.innerHTML = '';
        (theme.overlay_texts || []).forEach((text) => {
            const tag = document.createElement('div');
            tag.className = 'overlay-text';
            tag.textContent = text;
            elements.overlayTexts.appendChild(tag);
        });

        if (theme.banner && theme.banner.is_active && theme.banner.image_url) {
            elements.banner.style.display = 'block';
            elements.bannerImage.src = theme.banner.image_url;
            elements.bannerTitle.textContent = theme.banner.title || '';
            elements.bannerSubtitle.textContent = theme.banner.subtitle || '';
            if (theme.banner.logo_url) {
                elements.bannerLogo.style.display = 'block';
                elements.bannerLogo.src = theme.banner.logo_url;
            } else {
                elements.bannerLogo.style.display = 'none';
                elements.bannerLogo.removeAttribute('src');
            }
        } else {
            elements.banner.style.display = 'none';
        }

        renderSponsors(theme.sponsors || []);

        const tickerMessages = [...(theme.overlay_texts || [])];
        if (theme.banner?.title) {
            tickerMessages.push(`Sponsor: ${theme.banner.title}`);
        }
        elements.tickerText.textContent = tickerMessages.length
            ? `${tickerMessages.join(' • ')} • Iscriviti su ${joinUrl}`
            : `Benvenuti al Karaoke! Iscriviti e canta su ${joinUrl}`;
    };

    const renderEvent = (event) => {
        elements.eventName.textContent = event.venue ? `${event.venue} · ${event.code}` : event.code;
        const startLabel = event.starts_at ? `inizio ${formatTime(event.starts_at)}` : 'evento live';
        elements.eventMeta.textContent = startLabel;
        elements.joinLink.textContent = joinUrl;
    };

    const renderState = (state) => {
        if (!state) return;
        currentState = state;

        if (state.event) renderEvent(state.event);
        if (state.playback) updatePlayback(state.playback);
        if (state.queue) updateQueue(state.queue);
        if (state.theme) updateTheme(state.theme);
    };

    const startPolling = () => {
        const poll = async () => {
            try {
                const response = await fetch(stateUrl, { cache: 'no-store' });
                if (!response.ok) return;
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
            if (fallbackStarted) return;
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
