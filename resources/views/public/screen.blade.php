<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Schermo Evento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-cyan: #49dcff;
            --accent-gold: #ffc659;
            --accent-pink: #ff4f9a;
            --surface: rgba(10, 16, 34, 0.78);
            --surface-strong: rgba(9, 14, 30, 0.92);
            --text: #f8fbff;
            --muted: rgba(225, 236, 255, 0.78);
            --event-bg-image: none;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', 'Trebuchet MS', sans-serif;
            color: var(--text);
            background-image:
                var(--event-bg-image),
                radial-gradient(circle at 10% 18%, rgba(73, 220, 255, 0.26), transparent 34%),
                radial-gradient(circle at 85% 12%, rgba(255, 198, 89, 0.22), transparent 30%),
                radial-gradient(circle at 78% 76%, rgba(255, 79, 154, 0.2), transparent 34%),
                linear-gradient(130deg, #0a1024 0%, #101738 45%, #1b1230 100%);
            background-size: cover;
            background-position: center;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            width: 360px;
            height: 360px;
            border-radius: 999px;
            pointer-events: none;
            z-index: -1;
            filter: blur(2px);
        }

        body::before {
            top: -100px;
            left: -80px;
            background: radial-gradient(circle, rgba(73, 220, 255, 0.42), rgba(73, 220, 255, 0));
            animation: floatGlow 9s ease-in-out infinite;
        }

        body::after {
            right: -110px;
            bottom: -120px;
            background: radial-gradient(circle, rgba(255, 79, 154, 0.35), rgba(255, 79, 154, 0));
            animation: floatGlow 11s ease-in-out infinite reverse;
        }

        @keyframes floatGlow {
            0%,
            100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }

        .screen-shell {
            max-width: 1500px;
            margin: 0 auto;
            padding: 16px clamp(12px, 1.7vw, 24px) 18px;
        }

        .play-now-marquee {
            margin: 0 auto 14px;
            width: min(740px, 100%);
            display: grid;
            place-items: center;
            border: 1px solid rgba(252, 248, 255, 0.38);
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(55, 93, 255, 0.35), rgba(255, 79, 154, 0.34));
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.18) inset,
                0 0 20px rgba(113, 172, 255, 0.32);
            padding: 8px 12px;
            text-transform: uppercase;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.08em;
            font-size: clamp(1.6rem, 2.8vw, 2.6rem);
        }

        .topline {
            display: flex;
            gap: 14px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .event-brand {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 10px 14px;
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(6px);
            min-width: 280px;
        }

        .event-brand-logo {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            object-fit: contain;
            padding: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .event-brand-logo[hidden] {
            display: none;
        }

        .event-brand-meta {
            display: grid;
            gap: 2px;
        }

        .event-brand-meta strong {
            font-size: 1.05rem;
        }

        .event-brand-meta span {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .join-cta {
            text-decoration: none;
            color: #191126;
            font-weight: 800;
            border-radius: 999px;
            padding: 11px 20px;
            background: linear-gradient(110deg, var(--accent-gold), #ffe4a7 58%, #ffad57);
            border: 1px solid rgba(255, 220, 164, 0.55);
            box-shadow: 0 8px 24px rgba(255, 185, 87, 0.32);
            transition: transform 180ms ease, filter 180ms ease;
            white-space: nowrap;
        }

        .join-cta:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .layout {
            display: grid;
            grid-template-columns: 2.1fr 1fr;
            gap: 14px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 18px;
            box-shadow: 0 14px 30px rgba(5, 10, 22, 0.42);
            backdrop-filter: blur(8px);
            overflow: hidden;
            position: relative;
        }

        .panel::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 30%);
        }

        .panel-header {
            padding: 13px 16px 0;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: 1.38rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #d7ecff;
        }

        .now-panel {
            min-height: 330px;
        }

        .now-grid {
            padding: 10px 16px 16px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 14px;
            align-items: stretch;
        }

        .now-artist {
            color: #cae7ff;
            font-weight: 700;
            font-size: clamp(1.2rem, 1.95vw, 1.9rem);
        }

        .now-title {
            margin: 2px 0 8px;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: clamp(2rem, 4.7vw, 4.1rem);
            line-height: 0.95;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--accent-gold);
            text-shadow: 0 0 16px rgba(255, 198, 89, 0.42);
        }

        .now-singer {
            font-size: 1.08rem;
            color: #ebf4ff;
            margin-bottom: 8px;
        }

        .playback-meta {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .status-pill {
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(73, 220, 255, 0.2);
            border: 1px solid rgba(73, 220, 255, 0.5);
            color: #d9f7ff;
        }

        .end-at {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .progress-track {
            height: 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.24);
            margin-top: 5px;
        }

        .progress-fill {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent-cyan), #7be6ff 44%, var(--accent-gold));
            box-shadow: 0 0 18px rgba(73, 220, 255, 0.5);
            transition: width 420ms ease;
        }

        .progress-meta {
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: var(--muted);
            font-size: 0.86rem;
        }

        .featured-banner {
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: var(--surface-strong);
            padding: 12px;
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 10px;
            min-height: 240px;
        }

        .featured-banner[hidden] {
            display: none;
        }

        .featured-label {
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #b7daff;
            font-size: 1.1rem;
        }

        .featured-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 10px;
        }

        .featured-title {
            font-size: 1.2rem;
            font-weight: 800;
        }

        .featured-subtitle {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .featured-visual {
            width: 100%;
            max-height: 120px;
            object-fit: contain;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.06);
            padding: 8px;
        }

        .next-panel {
            min-height: 330px;
            display: grid;
            grid-template-rows: auto auto auto 1fr;
        }

        .next-highlight {
            margin: 10px 14px 6px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            padding: 12px;
            background: linear-gradient(165deg, rgba(255, 79, 154, 0.24), rgba(8, 16, 34, 0.35));
        }

        .next-artist {
            color: #ffe2f2;
            font-size: 1rem;
            font-weight: 700;
        }

        .next-title {
            margin: 2px 0 4px;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: clamp(1.6rem, 2.2vw, 2.5rem);
            line-height: 0.95;
            text-transform: uppercase;
            color: #ffd3a3;
        }

        .next-singer {
            color: #fcebf7;
            font-size: 0.95rem;
        }

        .queue-title {
            margin: 6px 14px 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #d6edff;
            font-size: 1.18rem;
        }

        .queue-list,
        .recent-list {
            list-style: none;
            margin: 0;
            padding: 0 12px 12px;
            display: grid;
            gap: 8px;
        }

        .song-row {
            border-radius: 12px;
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.04);
            display: grid;
            gap: 2px;
        }

        .song-row-main {
            font-weight: 700;
            font-size: 0.95rem;
        }

        .song-row-meta {
            color: var(--muted);
            font-size: 0.78rem;
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .bottom-layout {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            gap: 14px;
        }

        .recent-panel,
        .join-panel {
            min-height: 220px;
        }

        .join-content {
            padding: 10px 16px 16px;
            display: grid;
            gap: 10px;
        }

        .join-copy {
            color: #eaf6ff;
            font-size: 0.97rem;
        }

        .join-code {
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.08em;
            font-size: 1.8rem;
            padding: 8px 10px;
            border-radius: 12px;
            border: 1px dashed rgba(255, 255, 255, 0.38);
            background: rgba(255, 255, 255, 0.05);
            justify-self: start;
        }

        .join-url {
            color: var(--muted);
            font-size: 0.88rem;
            word-break: break-all;
        }

        .join-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .join-chip {
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 0.77rem;
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.08);
            color: #e5f4ff;
        }

        .ticker-wrap {
            margin-top: 14px;
            border-radius: 999px;
            border: 1px solid rgba(255, 196, 89, 0.4);
            background: linear-gradient(90deg, rgba(255, 150, 74, 0.24), rgba(255, 79, 154, 0.2));
            overflow: hidden;
            position: relative;
        }

        .ticker-track {
            display: inline-flex;
            align-items: center;
            gap: 38px;
            white-space: nowrap;
            min-width: 100%;
            padding: 9px 0;
            animation: tickerMove 28s linear infinite;
            font-weight: 700;
            color: #fff6e1;
        }

        .ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: clamp(0.85rem, 1.4vw, 1.2rem);
        }

        .ticker-item::before {
            content: '•';
            color: #ffde8a;
            font-size: 1.1em;
        }

        @keyframes tickerMove {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        .venue-strip {
            margin-top: 14px;
            border-radius: 14px;
            border: 1px solid rgba(73, 220, 255, 0.4);
            background: linear-gradient(120deg, rgba(11, 45, 80, 0.65), rgba(13, 32, 62, 0.65));
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .venue-name {
            margin: 0;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: clamp(1.5rem, 2.5vw, 2.3rem);
            letter-spacing: 0.04em;
            color: #88ddff;
        }

        .venue-sub {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .sponsor-strip {
            margin-top: 12px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: var(--surface-strong);
            padding: 10px;
        }

        .sponsor-head {
            margin: 0 0 8px;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-size: 1.3rem;
            color: #f0f7ff;
        }

        .sponsor-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .sponsor-card {
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.04);
            padding: 8px;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: 10px;
            min-height: 78px;
        }

        .sponsor-logo {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.08);
            padding: 6px;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .sponsor-title {
            font-weight: 800;
            font-size: 0.9rem;
            color: #f7fbff;
            line-height: 1.15;
        }

        .sponsor-subtitle {
            color: var(--muted);
            font-size: 0.77rem;
            margin-top: 2px;
            line-height: 1.2;
        }

        .updated-at {
            margin-top: 10px;
            text-align: right;
            color: rgba(214, 230, 250, 0.7);
            font-size: 0.78rem;
        }

        @media (max-width: 1120px) {
            .layout,
            .bottom-layout {
                grid-template-columns: 1fr;
            }

            .now-grid {
                grid-template-columns: 1fr;
            }

            .sponsor-row {
                grid-template-columns: 1fr;
            }

            .screen-shell {
                padding: 14px 10px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="screen-shell">
        <div class="play-now-marquee">Playing now!</div>

        <div class="topline">
            <div class="event-brand">
                <img id="brand-logo" class="event-brand-logo" alt="Logo evento" hidden>
                <div class="event-brand-meta">
                    <strong id="event-venue">Karaoke Night</strong>
                    <span id="event-code">Codice evento: {{ $eventNight->code }}</span>
                </div>
            </div>

            <a class="join-cta" id="join-cta" href="{{ route('public.join.show', $eventNight->code) }}">Canta anche tu: entra in coda</a>
        </div>

        <div class="layout">
            <section class="panel now-panel">
                <div class="panel-header">Ora in corso</div>
                <div class="now-grid">
                    <div>
                        <div class="now-artist" id="now-artist">—</div>
                        <div class="now-title" id="now-title">In attesa</div>
                        <div class="now-singer">Cantata da: <strong id="now-singer">—</strong></div>

                        <div class="playback-meta">
                            <span class="status-pill" id="playback-status">WAITING</span>
                            <span class="end-at" id="expected-end"></span>
                        </div>

                        <div class="progress-track">
                            <div class="progress-fill" id="progress-fill"></div>
                        </div>
                        <div class="progress-meta">
                            <span id="progress-elapsed">00:00</span>
                            <span id="progress-remaining">Restante 00:00</span>
                        </div>
                    </div>

                    <aside class="featured-banner" id="featured-banner" hidden>
                        <div class="featured-label">Sponsor in evidenza</div>
                        <div class="featured-content">
                            <div>
                                <div class="featured-title" id="featured-title">—</div>
                                <div class="featured-subtitle" id="featured-subtitle"></div>
                            </div>
                            <img id="featured-visual" class="featured-visual" alt="Sponsor">
                        </div>
                    </aside>
                </div>
            </section>

            <aside class="panel next-panel">
                <div class="panel-header">Prossima canzone</div>
                <div class="next-highlight">
                    <div class="next-artist" id="next-artist">Nessuna in coda</div>
                    <div class="next-title" id="next-title">—</div>
                    <div class="next-singer" id="next-singer"></div>
                </div>

                <div class="queue-title">
                    <span>In coda</span>
                    <span id="queue-total">0</span>
                </div>

                <ul class="queue-list" id="next-list"></ul>
            </aside>
        </div>

        <div class="bottom-layout">
            <section class="panel recent-panel">
                <div class="panel-header">Canzoni recenti</div>
                <ul class="recent-list" id="recent-list"></ul>
            </section>

            <section class="panel join-panel">
                <div class="panel-header">Partecipa alla serata</div>
                <div class="join-content">
                    <div class="join-copy">Apri la pagina pubblica, scegli la canzone e sali sul palco quando arriva il tuo turno.</div>
                    <div class="join-code" id="join-code">{{ $eventNight->code }}</div>
                    <div class="join-url" id="join-url"></div>
                    <div class="join-meta">
                        <span class="join-chip" id="join-pin-chip"></span>
                        <span class="join-chip" id="join-cooldown-chip"></span>
                        <span class="join-chip" id="event-time-chip"></span>
                    </div>
                </div>
            </section>
        </div>

        <section class="ticker-wrap">
            <div class="ticker-track" id="ticker-track"></div>
        </section>

        <section class="venue-strip">
            <div>
                <h2 class="venue-name" id="venue-name">{{ $eventNight->venue?->name ?? 'Karaoke Night' }}</h2>
                <div class="venue-sub" id="theme-name"></div>
            </div>

            <div class="venue-sub" id="event-datetime"></div>
        </section>

        <section class="sponsor-strip">
            <h3 class="sponsor-head">Sponsor</h3>
            <div class="sponsor-row" id="sponsor-row"></div>
        </section>

        <div class="updated-at" id="updated-at"></div>
    </div>

    <script>
        const initialState = @json($state);
        const realtimeEnabled = @json($realtimeEnabled);
        const pollMs = @json($pollSeconds * 1000);
        const stateUrl = @json(route('public.screen.state', $eventNight->code));
        const streamUrl = @json(route('public.screen.stream', $eventNight->code));
        const fallbackJoinUrl = @json(route('public.join.show', $eventNight->code));

        const elements = {
            brandLogo: document.getElementById('brand-logo'),
            eventVenue: document.getElementById('event-venue'),
            eventCode: document.getElementById('event-code'),
            joinCta: document.getElementById('join-cta'),
            nowArtist: document.getElementById('now-artist'),
            nowTitle: document.getElementById('now-title'),
            nowSinger: document.getElementById('now-singer'),
            playbackStatus: document.getElementById('playback-status'),
            expectedEnd: document.getElementById('expected-end'),
            progressFill: document.getElementById('progress-fill'),
            progressElapsed: document.getElementById('progress-elapsed'),
            progressRemaining: document.getElementById('progress-remaining'),
            featuredBanner: document.getElementById('featured-banner'),
            featuredTitle: document.getElementById('featured-title'),
            featuredSubtitle: document.getElementById('featured-subtitle'),
            featuredVisual: document.getElementById('featured-visual'),
            nextArtist: document.getElementById('next-artist'),
            nextTitle: document.getElementById('next-title'),
            nextSinger: document.getElementById('next-singer'),
            queueTotal: document.getElementById('queue-total'),
            nextList: document.getElementById('next-list'),
            recentList: document.getElementById('recent-list'),
            joinCode: document.getElementById('join-code'),
            joinUrl: document.getElementById('join-url'),
            joinPinChip: document.getElementById('join-pin-chip'),
            joinCooldownChip: document.getElementById('join-cooldown-chip'),
            eventTimeChip: document.getElementById('event-time-chip'),
            tickerTrack: document.getElementById('ticker-track'),
            venueName: document.getElementById('venue-name'),
            themeName: document.getElementById('theme-name'),
            eventDatetime: document.getElementById('event-datetime'),
            sponsorRow: document.getElementById('sponsor-row'),
            updatedAt: document.getElementById('updated-at'),
        };

        const appState = {
            event: initialState?.event || null,
            playback: initialState?.playback || null,
            queue: initialState?.queue || null,
            theme: initialState?.theme || null,
            updatedAt: initialState?.updated_at || null,
        };

        const resolveTimezone = () => appState?.event?.timezone || 'Europe/Rome';

        const formatTime = (isoValue) => {
            const date = isoValue ? new Date(isoValue) : null;
            if (!date || Number.isNaN(date.getTime())) {
                return '';
            }

            try {
                return date.toLocaleTimeString('it-IT', {
                    timeZone: resolveTimezone(),
                    hour: '2-digit',
                    minute: '2-digit',
                });
            } catch (_error) {
                return date.toLocaleTimeString('it-IT', {
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }
        };

        const formatDateTime = (isoValue) => {
            const date = isoValue ? new Date(isoValue) : null;
            if (!date || Number.isNaN(date.getTime())) {
                return '';
            }

            try {
                return date.toLocaleString('it-IT', {
                    timeZone: resolveTimezone(),
                    day: '2-digit',
                    month: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            } catch (_error) {
                return date.toLocaleString('it-IT', {
                    day: '2-digit',
                    month: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }
        };

        const formatDuration = (seconds) => {
            const safe = Number.isFinite(seconds) ? Math.max(0, Math.floor(seconds)) : 0;
            const minutes = Math.floor(safe / 60);
            const remainder = safe % 60;
            return `${String(minutes).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`;
        };

        const songLine = (item) => {
            if (!item) {
                return 'Nessuna canzone';
            }

            if (item.artist) {
                return `${item.artist} - ${item.title}`;
            }

            return item.title || 'Nessuna canzone';
        };

        const toDisplayUrl = (rawUrl) => {
            if (!rawUrl) {
                return fallbackJoinUrl;
            }

            try {
                const parsed = new URL(rawUrl);
                return `${parsed.host}${parsed.pathname}`;
            } catch (_error) {
                return rawUrl;
            }
        };

        const statusLabel = (state) => {
            const value = (state || '').toLowerCase();
            if (value === 'playing') {
                return 'Live';
            }
            if (value === 'paused') {
                return 'In pausa';
            }
            if (value === 'stopped') {
                return 'Stop';
            }
            return 'In attesa';
        };

        const renderRowList = (container, rows, emptyMessage, metaBuilder) => {
            container.innerHTML = '';

            if (!rows || rows.length === 0) {
                const li = document.createElement('li');
                li.className = 'song-row';
                const main = document.createElement('div');
                main.className = 'song-row-main';
                main.textContent = emptyMessage;
                li.appendChild(main);
                container.appendChild(li);
                return;
            }

            rows.forEach((item) => {
                const li = document.createElement('li');
                li.className = 'song-row';

                const main = document.createElement('div');
                main.className = 'song-row-main';
                main.textContent = songLine(item);

                const meta = document.createElement('div');
                meta.className = 'song-row-meta';

                const singer = document.createElement('span');
                singer.textContent = item.requested_by ? `Voce: ${item.requested_by}` : 'Voce: —';

                const extra = document.createElement('span');
                extra.textContent = metaBuilder(item);

                meta.appendChild(singer);
                meta.appendChild(extra);
                li.appendChild(main);
                li.appendChild(meta);
                container.appendChild(li);
            });
        };

        const updateEvent = (event) => {
            if (!event) {
                return;
            }

            const venue = event.venue || 'Karaoke Night';
            const joinUrl = event.join_url || fallbackJoinUrl;
            const cooldownSeconds = Number(event.request_cooldown_seconds || 0);
            const cooldownMinutes = Math.ceil(cooldownSeconds / 60);
            const timeRange = event.starts_at || event.ends_at
                ? `${formatDateTime(event.starts_at)} - ${formatTime(event.ends_at)}`
                : 'Orario non definito';

            elements.eventVenue.textContent = venue;
            elements.venueName.textContent = venue;
            elements.eventCode.textContent = `Codice evento: ${event.code || ''}`;
            elements.joinCode.textContent = event.code || '---';
            elements.joinUrl.textContent = `Partecipa su ${toDisplayUrl(joinUrl)}`;
            elements.joinCta.href = joinUrl;
            elements.joinPinChip.textContent = event.join_pin_required ? 'Accesso con PIN' : 'Accesso libero';
            elements.joinCooldownChip.textContent = cooldownMinutes > 0
                ? `Nuova richiesta ogni ${cooldownMinutes} min`
                : 'Richieste senza attesa';
            elements.eventTimeChip.textContent = timeRange;
            elements.eventDatetime.textContent = timeRange;
        };

        const updatePlayback = (playback) => {
            if (!playback) {
                return;
            }

            const song = playback.song;
            const progress = playback.progress || {};
            const percent = Math.max(0, Math.min(100, Number(progress.percent || 0)));

            elements.nowArtist.textContent = song?.artist || 'Palco karaoke';
            elements.nowTitle.textContent = song?.title || 'In attesa della prossima canzone';
            elements.nowSinger.textContent = song?.requested_by || 'Cantante in arrivo';
            elements.playbackStatus.textContent = statusLabel(playback.state);
            elements.expectedEnd.textContent = playback.expected_end_at
                ? `Fine prevista ${formatTime(playback.expected_end_at)}`
                : '';
            elements.progressFill.style.width = `${percent}%`;
            elements.progressElapsed.textContent = formatDuration(progress.elapsed_seconds);
            elements.progressRemaining.textContent = `Restante ${formatDuration(progress.remaining_seconds)}`;
        };

        const updateQueue = (queue) => {
            if (!queue) {
                return;
            }

            const next = Array.isArray(queue.next) ? queue.next : [];
            const recent = Array.isArray(queue.recent) ? queue.recent : [];
            const highlighted = next[0] || null;

            elements.nextArtist.textContent = highlighted?.artist || 'Nessuna canzone in coda';
            elements.nextTitle.textContent = highlighted?.title || '—';
            elements.nextSinger.textContent = highlighted?.requested_by
                ? `Canta: ${highlighted.requested_by}`
                : '';
            elements.queueTotal.textContent = `${queue.total_pending ?? next.length}`;

            const listAfterHighlight = highlighted ? next.slice(1) : next;
            renderRowList(
                elements.nextList,
                listAfterHighlight,
                'Nessuna altra canzone in coda.',
                (item) => item.position ? `Pos. ${item.position}` : ''
            );

            renderRowList(
                elements.recentList,
                recent,
                'Nessuna canzone riprodotta finora.',
                (item) => item.played_at ? formatTime(item.played_at) : ''
            );
        };

        const renderTicker = (messages, eventCode) => {
            const safeMessages = Array.isArray(messages)
                ? messages.filter((text) => typeof text === 'string' && text.trim() !== '').map((text) => text.trim())
                : [];

            if (safeMessages.length === 0) {
                safeMessages.push(`Benvenuti al Karaoke Night! Inserisci ${eventCode || ''} su /e/ per partecipare.`);
            }

            const sequence = [...safeMessages, ...safeMessages];
            elements.tickerTrack.innerHTML = '';

            sequence.forEach((text) => {
                const item = document.createElement('span');
                item.className = 'ticker-item';
                item.textContent = text;
                elements.tickerTrack.appendChild(item);
            });
        };

        const renderSponsors = (sponsors, fallbackBanner) => {
            let cards = Array.isArray(sponsors) ? sponsors.slice(0, 3) : [];

            if (cards.length === 0 && fallbackBanner && fallbackBanner.is_active) {
                cards = [fallbackBanner];
            }

            elements.sponsorRow.innerHTML = '';

            if (cards.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'sponsor-card';

                const details = document.createElement('div');
                details.style.gridColumn = '1 / -1';

                const title = document.createElement('div');
                title.className = 'sponsor-title';
                title.textContent = 'Spazio sponsor disponibile';

                const subtitle = document.createElement('div');
                subtitle.className = 'sponsor-subtitle';
                subtitle.textContent = 'Contatta il locale per comparire sullo schermo pubblico.';

                details.appendChild(title);
                details.appendChild(subtitle);
                empty.appendChild(details);
                elements.sponsorRow.appendChild(empty);
                return;
            }

            cards.forEach((card) => {
                const wrapper = document.createElement('article');
                wrapper.className = 'sponsor-card';

                const logo = card.logo_url || card.image_url;
                if (logo) {
                    const logoEl = document.createElement('img');
                    logoEl.className = 'sponsor-logo';
                    logoEl.src = logo;
                    logoEl.alt = card.title || 'Sponsor';
                    wrapper.appendChild(logoEl);
                }

                const details = document.createElement('div');
                const title = document.createElement('div');
                title.className = 'sponsor-title';
                title.textContent = card.title || 'Sponsor';
                details.appendChild(title);

                if (card.subtitle) {
                    const subtitle = document.createElement('div');
                    subtitle.className = 'sponsor-subtitle';
                    subtitle.textContent = card.subtitle;
                    details.appendChild(subtitle);
                }

                wrapper.appendChild(details);
                elements.sponsorRow.appendChild(wrapper);
            });
        };

        const updateTheme = (themePayload) => {
            if (!themePayload) {
                return;
            }

            const config = themePayload.theme?.config || {};
            const primary = config.primaryColor || '#49dcff';
            const secondary = config.secondaryColor || '#0f1c3e';
            const highlight = config.highlightColor || '#ffc659';

            document.documentElement.style.setProperty('--accent-cyan', primary);
            document.documentElement.style.setProperty('--accent-pink', secondary);
            document.documentElement.style.setProperty('--accent-gold', highlight);

            if (themePayload.background_image_url) {
                document.documentElement.style.setProperty('--event-bg-image', `url('${themePayload.background_image_url}')`);
            } else {
                document.documentElement.style.setProperty('--event-bg-image', 'none');
            }

            if (themePayload.brand_logo_url) {
                elements.brandLogo.hidden = false;
                elements.brandLogo.src = themePayload.brand_logo_url;
            } else {
                elements.brandLogo.hidden = true;
                elements.brandLogo.removeAttribute('src');
            }

            const banner = themePayload.banner;
            if (banner && banner.is_active) {
                elements.featuredBanner.hidden = false;
                elements.featuredTitle.textContent = banner.title || 'Sponsor';
                elements.featuredSubtitle.textContent = banner.subtitle || '';

                const visual = banner.logo_url || banner.image_url;
                if (visual) {
                    elements.featuredVisual.src = visual;
                    elements.featuredVisual.hidden = false;
                } else {
                    elements.featuredVisual.hidden = true;
                    elements.featuredVisual.removeAttribute('src');
                }
            } else {
                elements.featuredBanner.hidden = true;
                elements.featuredVisual.hidden = true;
                elements.featuredVisual.removeAttribute('src');
                elements.featuredTitle.textContent = '—';
                elements.featuredSubtitle.textContent = '';
            }

            elements.themeName.textContent = themePayload.theme?.name
                ? `Tema attivo: ${themePayload.theme.name}`
                : 'Tema base serata';

            renderTicker(themePayload.overlay_texts, appState?.event?.code);
            renderSponsors(themePayload.sponsor_banners, banner);
        };

        const renderUpdatedAt = (iso) => {
            if (!iso) {
                elements.updatedAt.textContent = '';
                return;
            }

            const time = formatDateTime(iso);
            elements.updatedAt.textContent = time ? `Aggiornato alle ${time}` : '';
        };

        const renderState = (snapshot) => {
            if (!snapshot) {
                return;
            }

            appState.event = snapshot.event || appState.event;
            appState.playback = snapshot.playback || appState.playback;
            appState.queue = snapshot.queue || appState.queue;
            appState.theme = snapshot.theme || appState.theme;
            appState.updatedAt = snapshot.updated_at || appState.updatedAt;

            updateEvent(appState.event);
            updatePlayback(appState.playback);
            updateQueue(appState.queue);
            updateTheme(appState.theme);
            renderUpdatedAt(appState.updatedAt);
        };

        const startPolling = () => {
            const poll = async () => {
                try {
                    const response = await fetch(stateUrl, { cache: 'no-store' });
                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    renderState(payload);
                } catch (_error) {
                    // Keep screen stable when network blips happen.
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
                const payload = JSON.parse(event.data);
                renderState(payload);
            });

            source.addEventListener('playback', (event) => {
                appState.playback = JSON.parse(event.data);
                updatePlayback(appState.playback);
            });

            source.addEventListener('queue', (event) => {
                appState.queue = JSON.parse(event.data);
                updateQueue(appState.queue);
            });

            source.addEventListener('theme', (event) => {
                appState.theme = JSON.parse(event.data);
                updateTheme(appState.theme);
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
