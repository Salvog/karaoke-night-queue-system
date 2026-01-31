<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Screen</title>
    <style>
        :root {
            --primary-color: #38bdf8;
            --secondary-color: #0f172a;
            --panel-color: #111827;
            --text-color: #f8fafc;
        }
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: var(--secondary-color);
            color: var(--text-color);
        }
        header {
            padding: 24px 32px;
            background: var(--panel-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            margin: 0;
            font-size: 28px;
        }
        header .event-name {
            font-size: 16px;
            color: var(--primary-color);
        }
        main {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            padding: 24px 32px 32px;
        }
        .panel {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .now-playing h2,
        .queue h2 {
            margin-top: 0;
            font-size: 22px;
        }
        .now-playing .song-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .now-playing .song-artist {
            font-size: 20px;
            color: var(--primary-color);
            margin-bottom: 16px;
        }
        .lyrics {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 12px;
            padding: 16px;
            white-space: pre-wrap;
            min-height: 140px;
        }
        .status-pill {
            display: inline-block;
            margin-top: 16px;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--primary-color);
            color: #0f172a;
            font-weight: 600;
        }
        .queue-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .queue-list li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        .queue-list li:last-child {
            border-bottom: none;
        }
        .queue-meta {
            color: rgba(226, 232, 240, 0.8);
            font-size: 14px;
        }
        .banner {
            margin-top: 24px;
            text-align: center;
        }
        .banner img {
            max-width: 100%;
            border-radius: 16px;
        }
        .banner-title {
            margin-top: 8px;
            color: var(--primary-color);
        }
        .updated-at {
            font-size: 12px;
            color: rgba(226, 232, 240, 0.7);
            margin-top: 8px;
        }
        @media (max-width: 900px) {
            main {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<header>
    <div>
        <h1>Now Playing</h1>
        <div class="event-name" id="event-name"></div>
    </div>
    <div class="status-pill" id="playback-status"></div>
</header>
<main>
    <section class="panel now-playing">
        <h2>Current Song</h2>
        <div class="song-title" id="now-title">—</div>
        <div class="song-artist" id="now-artist"></div>
        <div class="lyrics" id="now-lyrics">Waiting for the next song...</div>
        <div class="updated-at" id="playback-updated"></div>
    </section>

    <section class="panel queue">
        <h2>Up Next</h2>
        <ul class="queue-list" id="next-list"></ul>

        <h2 style="margin-top: 24px;">Recently Played</h2>
        <ul class="queue-list" id="recent-list"></ul>

        <div class="banner" id="banner" hidden>
            <img id="banner-image" alt="Ad banner">
            <div class="banner-title" id="banner-title"></div>
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
        eventName: document.getElementById('event-name'),
        playbackStatus: document.getElementById('playback-status'),
        nowTitle: document.getElementById('now-title'),
        nowArtist: document.getElementById('now-artist'),
        nowLyrics: document.getElementById('now-lyrics'),
        playbackUpdated: document.getElementById('playback-updated'),
        nextList: document.getElementById('next-list'),
        recentList: document.getElementById('recent-list'),
        banner: document.getElementById('banner'),
        bannerImage: document.getElementById('banner-image'),
        bannerTitle: document.getElementById('banner-title'),
        themeUpdated: document.getElementById('theme-updated'),
    };

    const formatSong = (song) => {
        if (!song) {
            return 'No song queued';
        }
        return song.artist ? `${song.title} — ${song.artist}` : song.title;
    };

    const updatePlayback = (playback) => {
        const song = playback.song;
        elements.nowTitle.textContent = song?.title ?? 'No song playing';
        elements.nowArtist.textContent = song?.artist ?? '';
        elements.nowLyrics.textContent = song?.lyrics || 'Lyrics unavailable.';
        elements.playbackStatus.textContent = playback.state ? playback.state.toUpperCase() : 'IDLE';
        elements.playbackUpdated.textContent = playback.expected_end_at
            ? `Expected end: ${new Date(playback.expected_end_at).toLocaleTimeString()}`
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
                meta.textContent = `Position ${item.position}`;
            } else if (item.played_at) {
                meta.textContent = `Played ${new Date(item.played_at).toLocaleTimeString()}`;
            }
            li.appendChild(title);
            li.appendChild(meta);
            container.appendChild(li);
        });
    };

    const updateQueue = (queue) => {
        renderList(elements.nextList, queue.next, 'No upcoming songs yet.');
        renderList(elements.recentList, queue.recent, 'Nothing played yet.');
    };

    const updateTheme = (theme) => {
        const config = theme.theme?.config || {};
        const primary = config.primaryColor || '#38bdf8';
        const secondary = config.secondaryColor || '#0f172a';
        document.documentElement.style.setProperty('--primary-color', primary);
        document.documentElement.style.setProperty('--secondary-color', secondary);
        document.documentElement.style.setProperty('--panel-color', '#111827');

        if (theme.banner && theme.banner.is_active && theme.banner.image_url) {
            elements.banner.hidden = false;
            elements.bannerImage.src = theme.banner.image_url;
            elements.bannerTitle.textContent = theme.banner.title ?? '';
        } else {
            elements.banner.hidden = true;
            elements.bannerImage.removeAttribute('src');
            elements.bannerTitle.textContent = '';
        }
        elements.themeUpdated.textContent = theme.theme?.name ? `Theme: ${theme.theme.name}` : '';
    };

    const renderState = (state) => {
        if (!state) {
            return;
        }
        if (state.event) {
            elements.eventName.textContent = state.event.venue
                ? `${state.event.venue} · ${state.event.code}`
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
