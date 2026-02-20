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
            --surface: rgba(10, 16, 34, 0.8);
            --surface-strong: rgba(9, 14, 30, 0.9);
            --text: #f8fbff;
            --muted: rgba(225, 236, 255, 0.78);
            --event-bg-image: none;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --join-qr-display-size: 240px;
            --manager-logo-min-height: 135px;
            --manager-logo-height-factor: 0.96;
            --status-font-size: clamp(1.8rem, 3.2vw, 3.1rem);
            --status-letter-spacing: 0.1em;
            --status-min-width: clamp(210px, 28vw, 460px);
            --status-zoom-scale: 1.08;
            --status-anim-duration: 1.6s;
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
            max-width: 1540px;
            margin: 0 auto;
            padding: 14px clamp(10px, 1.8vw, 24px) 16px;
        }

        .topline {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }

        .event-brand {
            display: flex;
            gap: 8px;
            align-items: center;
            min-height: 66px;
            padding: 8px 11px;
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 12px 24px rgba(4, 9, 22, 0.34);
            backdrop-filter: blur(6px);
            max-width: min(40vw, 320px);
            min-width: 0;
        }

        .event-brand-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            flex-shrink: 0;
        }

        .event-brand-logo[hidden] {
            display: none;
        }

        .event-brand-meta {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .event-brand-meta strong {
            font-size: 1.06rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .event-brand-meta span {
            color: var(--muted);
            font-size: 0.86rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .play-now-marquee {
            min-height: auto;
            justify-self: end;
            display: inline-flex;
            justify-content: flex-end;
            min-width: var(--status-min-width);
            padding: 2px 0;
            text-transform: uppercase;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: var(--status-letter-spacing);
            font-size: var(--status-font-size);
            text-align: right;
            color: #dff2ff;
            text-shadow: 0 0 12px rgba(73, 220, 255, 0.45), 0 0 24px rgba(255, 79, 154, 0.25);
            transform-origin: right center;
            animation: statusZoom var(--status-anim-duration) ease-in-out infinite;
            line-height: 0.92;
        }

        @keyframes statusZoom {
            0%,
            100% {
                transform: scale(1);
            }
            50% {
                transform: scale(var(--status-zoom-scale));
            }
        }

        .ticker-wrap {
            margin-bottom: 16px;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(255, 196, 89, 0.4);
            background: linear-gradient(90deg, rgba(255, 150, 74, 0.24), rgba(255, 79, 154, 0.2));
            overflow: hidden;
            position: relative;
        }

        .ticker-track {
            display: inline-flex;
            align-items: center;
            gap: 34px;
            white-space: nowrap;
            min-width: 100%;
            padding: 8px 0;
            animation: tickerMove 28s linear infinite;
            font-weight: 700;
            color: #fff6e1;
        }

        .ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: clamp(0.82rem, 1.35vw, 1.15rem);
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

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 2.05fr) minmax(0, 1fr);
            gap: 16px;
            align-items: stretch;
        }

        .left-stack {
            display: grid;
            gap: 16px;
            align-content: start;
            min-width: 0;
        }

        .panel {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: var(--radius-lg);
            box-shadow: 0 14px 28px rgba(5, 10, 22, 0.36);
            backdrop-filter: blur(7px);
            overflow: hidden;
            position: relative;
        }

        .panel-header {
            padding: 10px 14px;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: 1.34rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #d7ecff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            line-height: 1;
        }

        .now-grid {
            padding: 12px 14px 14px;
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(200px, 0.8fr);
            gap: 16px;
            align-items: stretch;
        }

        .now-main {
            min-width: 0;
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .now-artist {
            color: #cae7ff;
            font-weight: 700;
            font-size: clamp(1.16rem, 1.9vw, 1.8rem);
            line-height: 1.1;
        }

        .now-title {
            margin: 2px 0 8px;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: clamp(2.1rem, 4.65vw, 4rem);
            line-height: 0.94;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--accent-gold);
            text-shadow: 0 0 15px rgba(255, 198, 89, 0.38);
        }

        .now-singer {
            font-size: 1.05rem;
            color: #ebf4ff;
            margin-bottom: 0;
        }

        .now-bottom {
            margin-top: auto;
            display: grid;
            gap: 8px;
        }

        .playback-meta {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 0;
        }

        .status-pill {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.98rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(73, 220, 255, 0.2);
            border: 1px solid rgba(73, 220, 255, 0.48);
            color: #d9f7ff;
            line-height: 1;
        }

        .end-at {
            color: var(--muted);
            font-size: 1.02rem;
            font-weight: 700;
        }

        .progress-track {
            height: 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.22);
            margin-top: 4px;
        }

        .progress-fill {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent-cyan), #7be6ff 44%, var(--accent-gold));
            box-shadow: 0 0 14px rgba(73, 220, 255, 0.5);
            transition: width 420ms ease;
        }

        .progress-meta {
            margin-top: 0;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: var(--muted);
            font-size: 1.06rem;
            font-weight: 700;
        }

        .manager-brand {
            border-left: 1px solid rgba(255, 255, 255, 0.18);
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 8px;
            align-content: start;
            padding: 2px 0 0 14px;
            min-width: 0;
            height: 100%;
        }

        .manager-brand-label {
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #b7daff;
            font-size: 1.08rem;
            line-height: 1;
        }

        .manager-brand-logo {
            width: 100%;
            height: calc(100% * var(--manager-logo-height-factor));
            min-height: var(--manager-logo-min-height);
            object-fit: contain;
            object-position: center;
            background: transparent;
            border: 0;
            border-radius: 0;
            padding: 0;
        }

        .manager-brand-logo[hidden] {
            display: none;
        }

        .join-panel {
            min-height: 230px;
        }

        .join-content {
            padding: 12px 14px 14px;
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(220px, 1fr) minmax(210px, 0.85fr);
            gap: 14px;
            align-items: stretch;
        }

        .join-main {
            display: grid;
            gap: 10px;
            min-width: 0;
            align-content: start;
        }

        .join-copy {
            color: #eaf6ff;
            font-size: 0.97rem;
            line-height: 1.35;
        }

        .join-code {
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.08em;
            font-size: 1.8rem;
            padding: 7px 10px;
            border-radius: var(--radius-md);
            border: 1px dashed rgba(255, 255, 255, 0.38);
            background: rgba(255, 255, 255, 0.05);
            justify-self: start;
            line-height: 1;
        }

        .join-url {
            color: var(--muted);
            font-size: 0.86rem;
            word-break: break-all;
        }

        .join-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .join-chip {
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 0.76rem;
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.08);
            color: #e5f4ff;
            line-height: 1;
        }

        .join-banner-card {
            grid-column: 2;
            border-left: 1px solid rgba(255, 255, 255, 0.18);
            padding: 2px 0 0 14px;
            display: grid;
            align-content: center;
            min-width: 0;
        }

        .join-banner-card[hidden] {
            display: none;
        }

        .join-banner-visual {
            width: 100%;
            min-height: 126px;
            max-height: 180px;
            object-fit: contain;
            border: 0;
            border-radius: 0;
            background: transparent;
            padding: 0;
            display: block;
        }

        .join-banner-visual[hidden] {
            display: none;
        }

        .join-qr-card {
            grid-column: 3;
            border-left: 1px solid rgba(255, 255, 255, 0.18);
            padding: 2px 0 0 14px;
            display: grid;
            gap: 8px;
            align-content: start;
            min-width: 0;
            justify-self: end;
            text-align: right;
        }

        .join-qr-label {
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #d7ecff;
            font-size: 1.05rem;
            line-height: 1;
        }

        .join-qr-image {
            width: min(100%, var(--join-qr-display-size));
            aspect-ratio: 1;
            object-fit: contain;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: #fff;
            padding: 6px;
            margin-left: auto;
        }

        .join-qr-caption {
            font-size: 0.74rem;
            color: var(--muted);
            line-height: 1.25;
            word-break: break-all;
        }

        .next-panel {
            min-height: 100%;
        }

        .next-highlight {
            margin: 10px 12px 6px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px;
            background: linear-gradient(165deg, rgba(255, 79, 154, 0.2), rgba(8, 16, 34, 0.45));
        }

        .next-artist {
            color: #ffe2f2;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .next-title {
            margin: 1px 0 3px;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: clamp(1.9rem, 2.9vw, 3rem);
            line-height: 0.92;
            text-transform: uppercase;
            color: #ffd3a3;
        }

        .next-singer {
            color: #fcebf7;
            font-size: 1rem;
        }

        .queue-title {
            margin: 4px 12px 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #d6edff;
            font-size: 1.28rem;
            line-height: 1;
        }

        .recent-title {
            margin: 0 12px 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #d6edff;
            font-size: 1.28rem;
            line-height: 1;
        }

        .queue-list,
        .recent-list {
            list-style: none;
            margin: 0;
            padding: 0 11px 8px;
            display: grid;
            gap: 0;
            align-content: start;
        }

        .queue-list {
            padding-bottom: 0;
        }

        .song-row {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) minmax(0, 1.45fr);
            align-items: center;
            min-height: 58px;
            border: 1px solid rgba(84, 126, 182, 0.35);
            border-top: 0;
            background: rgba(12, 23, 52, 0.76);
        }

        .song-row:first-child {
            border-top: 1px solid rgba(84, 126, 182, 0.35);
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .song-row:last-child {
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .song-row:nth-child(even) {
            background: rgba(16, 30, 63, 0.84);
        }

        .song-row-num,
        .song-row-singer,
        .song-row-song {
            padding: 11px 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.2;
        }

        .song-row-num {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            font-weight: 800;
            color: #d7e5ff;
            border-right: 1px solid rgba(84, 126, 182, 0.3);
        }

        .song-row-singer {
            font-size: 1.05rem;
            font-weight: 800;
            color: #f1f6ff;
        }

        .song-row-song {
            font-size: 1.08rem;
            font-weight: 700;
            color: #eaf1ff;
        }

        .song-row.song-row-empty {
            grid-template-columns: 1fr;
        }

        .song-row-empty-text {
            grid-column: 1 / -1;
            padding: 12px 11px;
            font-weight: 700;
            font-size: 1rem;
            color: #dce8ff;
        }

        .sponsor-strip {
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: var(--surface-strong);
            box-shadow: 0 12px 24px rgba(5, 10, 22, 0.3);
            padding-top: 3px;
        }

        .sponsor-strip .panel-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 10px 8px;
        }

        .sponsor-row {
            padding: 12px 14px 14px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px 16px;
        }

        .sponsor-card {
            border-radius: 0;
            border: 0;
            background: transparent;
            padding: 0;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: 12px;
            min-height: 84px;
        }

        .sponsor-logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
            background: transparent;
            border: 0;
            border-radius: 0;
            padding: 0;
        }

        .sponsor-title {
            font-weight: 800;
            font-size: 1.08rem;
            color: #f7fbff;
            line-height: 1.15;
        }

        .sponsor-subtitle {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 2px;
            line-height: 1.2;
        }

        .updated-at {
            margin-top: 10px;
            text-align: right;
            color: rgba(214, 230, 250, 0.7);
            font-size: 0.76rem;
        }

        @media (max-width: 1280px) {
            .topline {
                grid-template-columns: 1fr auto;
            }
        }

        @media (max-width: 1120px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .topline {
                grid-template-columns: 1fr;
            }

            .event-brand {
                max-width: min(100%, 320px);
            }

            .play-now-marquee {
                justify-self: end;
                min-width: 0;
            }

            .now-grid,
            .join-content {
                grid-template-columns: 1fr;
            }

            .join-banner-card,
            .join-qr-card {
                grid-column: auto;
                border-left: 0;
                border-top: 1px solid rgba(255, 255, 255, 0.18);
                padding: 10px 0 0;
                margin-top: 4px;
            }

            .manager-brand {
                border-left: 0;
                border-top: 1px solid rgba(255, 255, 255, 0.18);
                padding: 10px 0 0;
                margin-top: 4px;
            }

            .next-panel {
                min-height: 0;
            }

            .screen-shell {
                padding: 12px 10px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="screen-shell">
        <div class="topline">
            <div class="event-brand">
                <img id="brand-logo" class="event-brand-logo" alt="Logo evento" hidden>
                <div class="event-brand-meta">
                    <strong id="event-venue">Karaoke Night</strong>
                    <span id="event-code">Codice evento: {{ $eventNight->code }}</span>
                </div>
            </div>

            <div class="play-now-marquee" id="playback-status-master">WAITING</div>
        </div>

        <section class="ticker-wrap">
            <div class="ticker-track" id="ticker-track"></div>
        </section>

        <div class="layout">
            <div class="left-stack">
                <section class="panel now-panel">
                    <div class="panel-header">Ora in corso</div>
                    <div class="now-grid">
                        <div class="now-main">
                            <div class="now-artist" id="now-artist">—</div>
                            <div class="now-title" id="now-title">In attesa</div>
                            <div class="now-bottom">
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
                        </div>

                        <aside class="manager-brand">
                            <div class="manager-brand-label">Regia karaoke</div>
                            <img id="manager-logo" class="manager-brand-logo" alt="Logo organizzazione" src="{{ config('public_screen.global_brand.logo') ?: '/images/admin/karaoke-duo.svg' }}">
                        </aside>
                    </div>
                </section>

                <section class="panel join-panel">
                    <div class="panel-header">Partecipa alla serata! Puoi Prenotare dal telefono</div>
                    <div class="join-content">
                        <div class="join-main">
                            <div class="join-copy">Apri la pagina pubblica, scegli la canzone e sali sul palco quando arriva il tuo turno.</div>
                            <div class="join-code" id="join-code">{{ $eventNight->code }}</div>
                            <div class="join-url" id="join-url"></div>
                            <div class="join-meta">
                                <span class="join-chip" id="join-pin-chip"></span>
                                <span class="join-chip" id="join-cooldown-chip"></span>
                                <span class="join-chip" id="event-time-chip"></span>
                            </div>
                        </div>

                        <aside class="join-banner-card" id="join-banner-card" hidden>
                            <img id="join-banner-visual" class="join-banner-visual" alt="Banner principale sponsor" hidden>
                        </aside>

                        <aside class="join-qr-card">
                            <img id="join-qr-image" class="join-qr-image" alt="QR code prenotazione">
                        </aside>
                    </div>
                </section>

                <section class="sponsor-strip">
                    <div class="panel-header">Sponsor</div>
                    <div class="sponsor-row" id="sponsor-row"></div>
                </section>
            </div>

            <aside class="panel next-panel">
                <div class="panel-header">Prossima canzone</div>
                <div class="next-highlight">
                    <div class="next-artist" id="next-artist">Nessuna in coda</div>
                    <div class="next-title" id="next-title">—</div>
                    <div class="next-singer" id="next-singer"></div>
                </div>
                <br>
                <div class="queue-title">
                    <span>In coda</span>
                    <span id="queue-total">0</span>
                </div>

                <ul class="queue-list" id="next-list"></ul>
                <br>
                <div class="recent-title">Canzoni recenti</div>
                <ul class="recent-list" id="recent-list"></ul>
            </aside>
        </div>

        <div class="updated-at" id="updated-at"></div>
    </div>

    <script>
        const initialState = @json($state);
        const realtimeEnabled = @json($realtimeEnabled);
        const pollMs = @json($pollSeconds * 1000);
        const realtimeMaxConsecutiveErrors = @json($realtimeMaxConsecutiveErrors);
        const realtimeConnectTimeoutMs = @json($realtimeConnectTimeoutSeconds * 1000);
        const stateUrl = @json(route('public.screen.state', $eventNight->code));
        const streamUrl = @json(route('public.screen.stream', $eventNight->code));
        const fallbackJoinUrl = @json(route('public.join.show', $eventNight->code));
        const fallbackManagerLogo = @json(config('public_screen.global_brand.logo') ?: '/images/admin/karaoke-duo.svg');
        const qrServiceUrl = @json(config('public_screen.join_qr.service_url', 'https://api.qrserver.com/v1/create-qr-code/'));
        const qrSizeConfig = @json((int) config('public_screen.join_qr.size', 240));
        const normalizedQrSize = Number.isFinite(qrSizeConfig)
            ? Math.max(140, Math.min(560, Math.floor(qrSizeConfig)))
            : 240;
        const statusVariants = {
            soft: {
                fontSize: 'clamp(1.55rem, 2.5vw, 2.45rem)',
                letterSpacing: '0.075em',
                minWidth: 'clamp(190px, 23vw, 360px)',
                zoomScale: '1.04',
                duration: '2.35s',
            },
            medium: {
                fontSize: 'clamp(1.8rem, 3vw, 2.9rem)',
                letterSpacing: '0.1em',
                minWidth: 'clamp(210px, 27vw, 420px)',
                zoomScale: '1.08',
                duration: '1.7s',
            },
            stage: {
                fontSize: 'clamp(2.3rem, 4.4vw, 4rem)',
                letterSpacing: '0.13em',
                minWidth: 'clamp(280px, 36vw, 620px)',
                zoomScale: '1.12',
                duration: '1.35s',
            },
        };
        // Cambia rapidamente look dello stato: 'soft' | 'medium' | 'stage'
        const activeStatusVariant = 'stage';

        const elements = {
            brandLogo: document.getElementById('brand-logo'),
            eventVenue: document.getElementById('event-venue'),
            eventCode: document.getElementById('event-code'),
            nowArtist: document.getElementById('now-artist'),
            nowTitle: document.getElementById('now-title'),
            nowSinger: document.getElementById('now-singer'),
            playbackStatus: document.getElementById('playback-status'),
            playbackStatusMaster: document.getElementById('playback-status-master'),
            expectedEnd: document.getElementById('expected-end'),
            progressFill: document.getElementById('progress-fill'),
            progressElapsed: document.getElementById('progress-elapsed'),
            progressRemaining: document.getElementById('progress-remaining'),
            managerLogo: document.getElementById('manager-logo'),
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
            joinBannerCard: document.getElementById('join-banner-card'),
            joinBannerVisual: document.getElementById('join-banner-visual'),
            joinQrImage: document.getElementById('join-qr-image'),
            joinQrCaption: document.getElementById('join-qr-caption'),
            tickerTrack: document.getElementById('ticker-track'),
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
        let pollIntervalId = null;
        let pollingStarted = false;
        let source = null;
        let realtimeEverConnected = false;
        let consecutiveSseErrors = 0;
        let fallbackActivated = false;
        let firstEventTimeoutId = null;

        const resolveTimezone = () => appState?.event?.timezone || 'Europe/Rome';
        const applyStatusVariant = (variantName) => {
            const variant = statusVariants[variantName] || statusVariants.medium;
            document.documentElement.style.setProperty('--status-font-size', variant.fontSize);
            document.documentElement.style.setProperty('--status-letter-spacing', variant.letterSpacing);
            document.documentElement.style.setProperty('--status-min-width', variant.minWidth);
            document.documentElement.style.setProperty('--status-zoom-scale', variant.zoomScale);
            document.documentElement.style.setProperty('--status-anim-duration', variant.duration);
        };

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

        const buildQrCodeUrl = (targetUrl) => {
            const safeUrl = targetUrl || fallbackJoinUrl;
            const base = (typeof qrServiceUrl === 'string' && qrServiceUrl.trim() !== '')
                ? qrServiceUrl.trim()
                : 'https://api.qrserver.com/v1/create-qr-code/';
            const separator = base.includes('?') ? '&' : '?';

            return `${base}${separator}size=${normalizedQrSize}x${normalizedQrSize}&format=svg&margin=0&data=${encodeURIComponent(safeUrl)}`;
        };

        const updateJoinQr = (joinUrl) => {
            if (!elements.joinQrImage) {
                return;
            }

            const safeUrl = joinUrl || fallbackJoinUrl;
            elements.joinQrImage.src = buildQrCodeUrl(safeUrl);
            elements.joinQrImage.alt = `QR code per ${toDisplayUrl(safeUrl)}`;

            if (elements.joinQrCaption) {
                elements.joinQrCaption.textContent = toDisplayUrl(safeUrl);
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

        const renderRowList = (container, rows, emptyMessage, options = {}) => {
            const {
                numberBuilder = (_item, index) => index + 1,
                singerBuilder = (item) => item?.requested_by || '—',
                songBuilder = (item) => item?.title || songLine(item),
            } = options;

            container.innerHTML = '';

            if (!rows || rows.length === 0) {
                const li = document.createElement('li');
                li.className = 'song-row song-row-empty';
                const text = document.createElement('span');
                text.className = 'song-row-empty-text';
                text.textContent = emptyMessage;
                li.appendChild(text);
                container.appendChild(li);
                return;
            }

            rows.forEach((item, index) => {
                const li = document.createElement('li');
                li.className = 'song-row';

                const number = document.createElement('span');
                number.className = 'song-row-num';
                number.textContent = `${numberBuilder(item, index)}`;

                const singer = document.createElement('span');
                singer.className = 'song-row-singer';
                singer.textContent = singerBuilder(item, index);

                const song = document.createElement('span');
                song.className = 'song-row-song';
                song.textContent = songBuilder(item, index);

                li.appendChild(number);
                li.appendChild(singer);
                li.appendChild(song);
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
            elements.eventCode.textContent = `Codice evento: ${event.code || ''}`;
            elements.joinCode.textContent = event.code || '---';
            elements.joinUrl.textContent = `Partecipa su ${toDisplayUrl(joinUrl)}`;
            elements.joinPinChip.textContent = event.join_pin_required ? 'Accesso con PIN' : 'Accesso libero';
            elements.joinCooldownChip.textContent = cooldownMinutes > 0
                ? `Nuova richiesta ogni ${cooldownMinutes} min`
                : 'Richieste senza attesa';
            elements.eventTimeChip.textContent = timeRange;
            updateJoinQr(joinUrl);
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
            elements.playbackStatusMaster.textContent = statusLabel(playback.state);
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
                {
                    numberBuilder: (item, index) => item.position ?? (index + 1),
                    singerBuilder: (item) => item?.requested_by || '—',
                    songBuilder: (item) => item?.title || songLine(item),
                }
            );

            renderRowList(
                elements.recentList,
                recent,
                'Nessuna canzone riprodotta finora.',
                {
                    numberBuilder: (_item, index) => index + 1,
                    singerBuilder: (item) => item?.requested_by || '—',
                    songBuilder: (item) => item?.title || songLine(item),
                }
            );
        };

        const renderTicker = (messages, eventCode) => {
            const safeMessages = Array.isArray(messages)
                ? messages.filter((text) => typeof text === 'string' && text.trim() !== '').map((text) => text.trim())
                : [];

            if (safeMessages.length === 0) {
                safeMessages.push(`Benvenuti al Karaoke Night! Inserisci il codice evento ${eventCode || ''} per prenotare.`);
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
            let cards = Array.isArray(sponsors) ? sponsors.slice(0, 6) : [];

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

        const renderJoinMainBanner = (banner) => {
            if (!elements.joinBannerCard) {
                return;
            }

            if (!banner || !banner.is_active) {
                elements.joinBannerCard.hidden = true;
                if (elements.joinBannerVisual) {
                    elements.joinBannerVisual.hidden = true;
                    elements.joinBannerVisual.removeAttribute('src');
                }
                return;
            }

            elements.joinBannerCard.hidden = false;
            const visual = banner.image_url || banner.logo_url;
            if (elements.joinBannerVisual) {
                if (visual) {
                    elements.joinBannerVisual.hidden = false;
                    elements.joinBannerVisual.src = visual;
                } else {
                    elements.joinBannerVisual.hidden = true;
                    elements.joinBannerVisual.removeAttribute('src');
                }
            }
        };

        const updateTheme = (themePayload) => {
            const payload = themePayload || {};
            const config = payload.theme?.config || {};
            const primary = config.primaryColor || '#49dcff';
            const secondary = config.secondaryColor || '#0f1c3e';
            const highlight = config.highlightColor || '#ffc659';

            document.documentElement.style.setProperty('--accent-cyan', primary);
            document.documentElement.style.setProperty('--accent-pink', secondary);
            document.documentElement.style.setProperty('--accent-gold', highlight);

            if (payload.background_image_url) {
                document.documentElement.style.setProperty('--event-bg-image', `url('${payload.background_image_url}')`);
            } else {
                document.documentElement.style.setProperty('--event-bg-image', 'none');
            }

            if (payload.brand_logo_url) {
                elements.brandLogo.hidden = false;
                elements.brandLogo.src = payload.brand_logo_url;
            } else {
                elements.brandLogo.hidden = true;
                elements.brandLogo.removeAttribute('src');
            }

            const managerLogo = payload.manager_logo_url || fallbackManagerLogo;
            if (managerLogo) {
                elements.managerLogo.hidden = false;
                elements.managerLogo.src = managerLogo;
            } else {
                elements.managerLogo.hidden = true;
                elements.managerLogo.removeAttribute('src');
            }

            renderJoinMainBanner(payload.banner);
            renderTicker(payload.overlay_texts, appState?.event?.code);
            renderSponsors(payload.sponsor_banners, payload.banner);
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
            if (pollingStarted) {
                return;
            }

            pollingStarted = true;
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
            pollIntervalId = setInterval(poll, pollMs);
        };

        const resetConnectTimeout = () => {
            if (firstEventTimeoutId !== null) {
                clearTimeout(firstEventTimeoutId);
            }

            firstEventTimeoutId = setTimeout(() => {
                if (fallbackActivated || realtimeEverConnected) {
                    return;
                }

                consecutiveSseErrors += 1;
                maybeActivateFallback();
            }, realtimeConnectTimeoutMs);
        };

        const clearConnectTimeout = () => {
            if (firstEventTimeoutId === null) {
                return;
            }

            clearTimeout(firstEventTimeoutId);
            firstEventTimeoutId = null;
        };

        const maybeActivateFallback = () => {
            if (fallbackActivated) {
                return;
            }

            if (consecutiveSseErrors < realtimeMaxConsecutiveErrors) {
                return;
            }

            fallbackActivated = true;
            clearConnectTimeout();

            if (source) {
                source.close();
                source = null;
            }

            startPolling();
        };

        const startRealtime = () => {
            if (!realtimeEnabled || typeof EventSource === 'undefined') {
                startPolling();
                return;
            }

            if (source || fallbackActivated) {
                return;
            }

            source = new EventSource(streamUrl);
            resetConnectTimeout();

            source.onopen = () => {
                realtimeEverConnected = true;
                consecutiveSseErrors = 0;
                clearConnectTimeout();
            };

            source.addEventListener('snapshot', (event) => {
                const payload = JSON.parse(event.data);
                realtimeEverConnected = true;
                consecutiveSseErrors = 0;
                clearConnectTimeout();
                renderState(payload);
            });

            source.addEventListener('playback', (event) => {
                realtimeEverConnected = true;
                consecutiveSseErrors = 0;
                clearConnectTimeout();
                appState.playback = JSON.parse(event.data);
                updatePlayback(appState.playback);
            });

            source.addEventListener('queue', (event) => {
                realtimeEverConnected = true;
                consecutiveSseErrors = 0;
                clearConnectTimeout();
                appState.queue = JSON.parse(event.data);
                updateQueue(appState.queue);
            });

            source.addEventListener('theme', (event) => {
                realtimeEverConnected = true;
                consecutiveSseErrors = 0;
                clearConnectTimeout();
                appState.theme = JSON.parse(event.data);
                updateTheme(appState.theme);
            });

            source.addEventListener('error', () => {
                if (fallbackActivated) {
                    return;
                }

                consecutiveSseErrors += 1;
                maybeActivateFallback();
            });
        };

        document.documentElement.style.setProperty('--join-qr-display-size', `${normalizedQrSize}px`);
        applyStatusVariant(activeStatusVariant);
        renderState(initialState);
        startRealtime();
    </script>
</body>
</html>
