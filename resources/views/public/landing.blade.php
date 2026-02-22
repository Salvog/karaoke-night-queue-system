<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Prenota il tuo brano</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-cyan: #49dcff;
            --accent-gold: #ffc659;
            --accent-pink: #ff4f9a;
            --surface: rgba(10, 16, 34, 0.82);
            --surface-strong: rgba(8, 12, 26, 0.9);
            --text: #f8fbff;
            --muted: rgba(225, 236, 255, 0.78);
            --success: #6cf6b4;
            --danger: #ff9eac;
            --warning: #ffd27e;
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 18px;
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
                radial-gradient(circle at 10% 18%, rgba(73, 220, 255, 0.24), transparent 35%),
                radial-gradient(circle at 86% 12%, rgba(255, 198, 89, 0.2), transparent 32%),
                radial-gradient(circle at 82% 78%, rgba(255, 79, 154, 0.18), transparent 34%),
                linear-gradient(130deg, #0a1024 0%, #101738 45%, #1b1230 100%);
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 999px;
            pointer-events: none;
            z-index: -1;
            filter: blur(2px);
        }

        body::before {
            top: -120px;
            left: -80px;
            background: radial-gradient(circle, rgba(73, 220, 255, 0.4), rgba(73, 220, 255, 0));
            animation: floatGlow 10s ease-in-out infinite;
        }

        body::after {
            right: -120px;
            bottom: -120px;
            background: radial-gradient(circle, rgba(255, 79, 154, 0.34), rgba(255, 79, 154, 0));
            animation: floatGlow 12s ease-in-out infinite reverse;
        }

        @keyframes floatGlow {
            0%,
            100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }

        .join-shell {
            max-width: 1140px;
            margin: 0 auto;
            padding: 18px 14px 28px;
            display: grid;
            gap: 14px;
        }

        .hero {
            display: grid;
            gap: 10px;
            padding: 14px;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: var(--radius-lg);
            box-shadow: 0 14px 28px rgba(5, 10, 22, 0.34);
            backdrop-filter: blur(7px);
        }

        .hero-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-title {
            margin: 0;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: clamp(2rem, 5vw, 2.8rem);
            line-height: 0.95;
            color: #dff2ff;
        }

        .hero-subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 0.96rem;
        }

        .event-code-pill {
            padding: 10px 12px;
            border-radius: var(--radius-md);
            border: 1px dashed rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.08);
            min-width: 190px;
        }

        .event-code-pill strong {
            display: block;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            font-size: 1.9rem;
            letter-spacing: 0.09em;
            line-height: 0.9;
            color: var(--accent-gold);
        }

        .event-code-pill span {
            color: var(--muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
        }

        .notice {
            border-radius: var(--radius-md);
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 12px 24px rgba(4, 9, 22, 0.28);
        }

        .notice-title {
            margin: 0 0 4px;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .notice-text {
            margin: 0;
            color: #e9f4ff;
            font-size: 0.92rem;
            line-height: 1.45;
        }

        .notice ul {
            margin: 6px 0 0;
            padding-left: 18px;
            display: grid;
            gap: 3px;
            color: #fff2f3;
            font-size: 0.92rem;
        }

        .notice--success {
            border-color: rgba(108, 246, 180, 0.46);
            background: linear-gradient(130deg, rgba(16, 82, 64, 0.5), rgba(18, 43, 42, 0.44));
        }

        .notice--success .notice-title {
            color: #bdfdde;
        }

        .notice--error {
            border-color: rgba(255, 158, 172, 0.54);
            background: linear-gradient(130deg, rgba(86, 24, 40, 0.56), rgba(45, 18, 26, 0.46));
        }

        .notice--error .notice-title {
            color: #ffc9d2;
        }

        .notice--warning {
            border-color: rgba(255, 210, 126, 0.54);
            background: linear-gradient(130deg, rgba(93, 55, 14, 0.5), rgba(38, 28, 13, 0.44));
        }

        .notice--warning .notice-title {
            color: #ffe7b8;
        }

        .notice--info {
            border-color: rgba(73, 220, 255, 0.5);
            background: linear-gradient(130deg, rgba(14, 58, 94, 0.48), rgba(12, 24, 48, 0.44));
        }

        .notice--info .notice-title {
            color: #c6f1ff;
        }

        .layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
            gap: 14px;
            align-items: stretch;
        }

        .card {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: var(--radius-lg);
            box-shadow: 0 14px 28px rgba(5, 10, 22, 0.36);
            backdrop-filter: blur(7px);
            overflow: hidden;
            min-width: 0;
        }

        .booking-card {
            grid-column: 1;
            grid-row: 1;
        }

        .my-requests-card {
            grid-column: 1;
            grid-row: 2;
        }

        .songs-card {
            grid-column: 2;
            grid-row: 1 / span 2;
            height: 100%;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr);
            border-color: rgba(255, 255, 255, 0.2);
            background:
                radial-gradient(circle at 100% -26%, rgba(255, 255, 255, 0.08), transparent 52%),
                linear-gradient(155deg, rgba(28, 31, 63, 0.72), rgba(20, 18, 40, 0.66));
        }

        .card--booking-focus {
            border-color: rgba(42, 216, 255, 0.56);
            background:
                radial-gradient(circle at 100% -24%, rgba(42, 216, 255, 0.3), transparent 54%),
                linear-gradient(155deg, rgba(16, 43, 76, 0.8), rgba(14, 24, 49, 0.82));
            box-shadow:
                0 18px 32px rgba(4, 10, 30, 0.46),
                0 0 0 1px rgba(42, 216, 255, 0.2),
                0 0 36px rgba(42, 216, 255, 0.24);
        }

        .card--booking-focus .card-header {
            border-bottom-color: rgba(42, 216, 255, 0.26);
        }

        .card--booking-focus .card-title {
            color: #b8f4ff;
            text-shadow: 0 0 10px rgba(42, 216, 255, 0.36), 0 0 22px rgba(42, 216, 255, 0.22);
            letter-spacing: 0.02em;
        }

        .card--booking-focus .card-subtitle {
            color: rgba(219, 249, 255, 0.86);
        }

        .card-header {
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .card-title {
            margin: 0;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 1.5rem;
            line-height: 0.95;
            color: #ddf0ff;
        }

        .card-subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 0.86rem;
            font-weight: 600;
        }

        .card-body {
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .form-row {
            display: grid;
            gap: 6px;
        }

        label {
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #d8ecff;
        }

        .field-hint {
            margin: 0;
            color: var(--muted);
            font-size: 0.8rem;
            line-height: 1.35;
        }

        .text-input {
            width: 100%;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(5, 12, 26, 0.7);
            color: var(--text);
            font: inherit;
            padding: 10px 11px;
            outline: none;
        }

        .text-input::placeholder {
            color: rgba(225, 236, 255, 0.5);
        }

        .text-input:focus {
            border-color: rgba(73, 220, 255, 0.65);
            box-shadow: 0 0 0 3px rgba(73, 220, 255, 0.18);
        }

        .button {
            border: 0;
            border-radius: 999px;
            font: inherit;
            font-weight: 800;
            letter-spacing: 0.02em;
            cursor: pointer;
            padding: 9px 14px;
            line-height: 1;
            transition: transform 120ms ease, opacity 120ms ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .button:active {
            transform: translateY(1px);
        }

        .button[disabled] {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .button-primary {
            color: #042031;
            background: linear-gradient(90deg, var(--accent-cyan), #7be6ff);
            box-shadow: 0 8px 16px rgba(73, 220, 255, 0.3);
        }

        .button-secondary {
            color: #e9f5ff;
            background: rgba(126, 153, 194, 0.25);
            border: 1px solid rgba(198, 220, 255, 0.32);
        }

        .pin-form {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: end;
        }

        .pin-form .form-row {
            flex: 1;
            min-width: 180px;
        }

        .cooldown-note {
            margin: 0;
            color: #d4e9ff;
            font-size: 0.86rem;
            border: 1px solid rgba(255, 210, 126, 0.35);
            background: rgba(255, 210, 126, 0.12);
            border-radius: var(--radius-sm);
            padding: 10px;
            line-height: 1.35;
        }

        .search-row {
            display: grid;
            gap: 6px;
        }

        .search-meta {
            margin: 0;
            color: var(--muted);
            font-size: 0.83rem;
            min-height: 18px;
        }

        .song-results {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
        }

        .song-result {
            border: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            background: transparent;
            padding: 8px 10px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
        }

        .song-main {
            min-width: 0;
            display: grid;
            gap: 2px;
        }

        .song-title {
            margin: 0;
            font-size: 0.9rem;
            color: #ecf6ff;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .song-artist {
            margin: 0;
            color: #b8d7f5;
            font-size: 0.78rem;
            line-height: 1.28;
            overflow-wrap: anywhere;
        }

        .song-request-form {
            margin: 0;
        }

        .song-request-form .button {
            width: auto;
            min-width: 92px;
            padding-top: 7px;
            padding-bottom: 7px;
            font-size: 0.82rem;
        }

        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }

        .page-info {
            color: #d8ebff;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .empty-state {
            border-radius: var(--radius-sm);
            border: 1px dashed rgba(255, 255, 255, 0.26);
            padding: 10px;
            color: var(--muted);
            font-size: 0.88rem;
            text-align: center;
        }

        .my-requests-meta {
            margin: 0;
            color: var(--muted);
            font-size: 0.83rem;
            min-height: 18px;
        }

        .request-list {
            display: grid;
            gap: 10px;
        }

        .request-item {
            border-radius: var(--radius-sm);
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(6, 12, 24, 0.68);
            padding: 10px;
            display: grid;
            gap: 6px;
        }

        .request-item--queued {
            border-color: rgba(73, 220, 255, 0.48);
        }

        .request-item--playing {
            border-color: rgba(255, 198, 89, 0.58);
            background: linear-gradient(120deg, rgba(62, 43, 10, 0.5), rgba(11, 19, 32, 0.7));
        }

        .request-item--played {
            border-color: rgba(108, 246, 180, 0.38);
        }

        .request-item--skipped,
        .request-item--canceled {
            border-color: rgba(255, 158, 172, 0.42);
            opacity: 0.95;
        }

        .request-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }

        .request-title {
            margin: 0;
            font-size: 0.98rem;
            color: #f4f9ff;
            overflow-wrap: anywhere;
            line-height: 1.25;
        }

        .request-artist {
            margin: 0;
            color: #b4d3f2;
            font-size: 0.84rem;
            overflow-wrap: anywhere;
        }

        .request-badge {
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #f2f8ff;
            background: rgba(255, 255, 255, 0.08);
        }

        .request-badge--queued {
            border-color: rgba(73, 220, 255, 0.5);
            color: #d5f8ff;
            background: rgba(73, 220, 255, 0.16);
        }

        .request-badge--playing {
            border-color: rgba(255, 198, 89, 0.6);
            color: #fff0cd;
            background: rgba(255, 198, 89, 0.2);
        }

        .request-badge--played {
            border-color: rgba(108, 246, 180, 0.52);
            color: #d8ffe8;
            background: rgba(108, 246, 180, 0.16);
        }

        .request-badge--skipped,
        .request-badge--canceled {
            border-color: rgba(255, 158, 172, 0.56);
            color: #ffd9df;
            background: rgba(255, 158, 172, 0.16);
        }

        .request-data {
            margin: 0;
            color: #d2e7fc;
            font-size: 0.8rem;
            line-height: 1.35;
        }

        .request-note {
            margin: 0;
            color: #edf6ff;
            font-size: 0.86rem;
            line-height: 1.4;
            font-weight: 600;
        }

        .request-note--muted {
            color: #c6def7;
            font-weight: 500;
        }

        .songs-card .card-header {
            border-bottom-color: rgba(255, 255, 255, 0.14);
        }

        .songs-card .card-body {
            gap: 10px;
            grid-template-rows: auto minmax(0, 1fr) auto;
            min-height: 0;
        }

        .songs-card .song-results {
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 10px;
            background: rgba(8, 16, 36, 0.58);
            padding: 0;
            overflow: auto;
            align-content: start;
            grid-template-columns: 1fr;
            gap: 0;
        }

        .songs-card .song-result {
            border-bottom-color: rgba(255, 255, 255, 0.11);
            background: rgba(255, 255, 255, 0.03);
            padding: 9px 10px;
        }

        .songs-card .song-result:last-child {
            border-bottom: 0;
        }

        .songs-card .song-result:nth-child(even) {
            background: rgba(255, 255, 255, 0.055);
        }

        .songs-card .song-result:hover {
            background: rgba(42, 216, 255, 0.12);
        }

        @media (max-width: 980px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }

            .booking-card,
            .my-requests-card,
            .songs-card {
                grid-column: auto;
                grid-row: auto;
                height: auto;
            }

            .songs-card {
                display: block;
            }

            .songs-card .song-results {
                max-height: none;
            }
        }

        @media (max-width: 640px) {
            .join-shell {
                padding: 12px 10px 22px;
                gap: 10px;
            }

            .hero,
            .card,
            .notice {
                border-radius: 14px;
            }

            .hero-title {
                font-size: 2rem;
            }

            .event-code-pill {
                width: 100%;
            }

            .card-body,
            .card-header {
                padding-left: 11px;
                padding-right: 11px;
            }

            .song-results {
                grid-template-columns: 1fr;
            }

            .song-result {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .song-request-form .button {
                width: 100%;
            }

            .pagination {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<main class="join-shell">
    <header class="hero">
        <div class="hero-top">
            <div>
                <h1 class="hero-title">Prenota il tuo brano</h1>
                <p class="hero-subtitle">{{ $eventNight->venue?->name ?? 'Karaoke Night' }}</p>
            </div>
            <div class="event-code-pill">
                <span>Codice evento</span>
                <strong>{{ $eventNight->code }}</strong>
            </div>
        </div>
    </header>

    @if (session('status'))
        <section class="notice notice--success" role="status" aria-live="polite">
            <p class="notice-title">Prenotazione aggiornata</p>
            <p class="notice-text">{{ session('status') }}</p>
        </section>
    @endif

    @if ($errors->any())
        <section class="notice notice--error" role="alert" aria-live="assertive">
            <p class="notice-title">Controlla i dati inseriti</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="notice notice--warning" role="note">
        <p class="notice-title">Se non riesci a prenotare</p>
        <p class="notice-text">Ricarica questa pagina e riprova. Se il problema continua, chiedi supporto allo staff in sala.</p>
    </section>

    @if ($eventNight->join_pin)
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">Attivazione con PIN</h2>
                <p class="card-subtitle">Necessaria prima della prima prenotazione</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('public.join.activate', $eventNight->code) }}" class="pin-form">
                    @csrf
                    <div class="form-row">
                        <label for="pin">PIN evento</label>
                        <input id="pin" name="pin" type="password" class="text-input" autocomplete="one-time-code" placeholder="Inserisci il PIN">
                    </div>
                    <button class="button button-secondary" type="submit">Attiva accesso</button>
                </form>
            </div>
        </section>
    @endif

    <section class="notice notice--info" id="client-message" role="status" aria-live="polite" hidden>
        <p class="notice-title" id="client-message-title">Informazione</p>
        <p class="notice-text" id="client-message-text"></p>
    </section>

    <div class="layout-grid">
        <section class="card card--booking-focus booking-card">
            <div class="card-header">
                <h2 class="card-title">Prenota una canzone</h2>
                <p class="card-subtitle">
                    Inserisci il tuo nome e cerca il brano da prenotare!
                    @php($cooldownMinutes = (int) ceil($eventNight->request_cooldown_seconds / 60))
                    @if ($cooldownMinutes > 0)
                        @php($cooldownLabel = $cooldownMinutes === 1 ? 'minuto' : 'minuti')
                        <br>Dopo una prenotazione puoi inserirne un'altra dopo {{ $cooldownMinutes }} {{ $cooldownLabel }}.
                    @endif
                </p>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <label for="display-name">Nome *</label>
                    <input
                        id="display-name"
                        name="display_name"
                        type="text"
                        class="text-input"
                        maxlength="80"
                        autocomplete="name"
                        placeholder="Es. Marco"
                        value="{{ old('display_name', $participantName ?? '') }}"
                        required
                    >
                </div>

                <div class="search-row">
                    <label for="song-search">Cerca canzone</label>
                    <input id="song-search" type="text" class="text-input" placeholder="Digita titolo o artista...">
                </div>
            </div>
        </section>

        <section class="card songs-card">
            <div class="card-header">
                <h2 class="card-title">Brani disponibili</h2>
                <p class="card-subtitle">Griglia rapida per prenotare</p>
            </div>
            <div class="card-body">
                <p class="search-meta" id="search-meta">Carico il catalogo canzoni...</p>

                <div id="song-results" class="song-results"></div>

                <div class="pagination">
                    <button class="button button-secondary" id="prev-page" type="button">Precedente</button>
                    <span id="page-info" class="page-info"></span>
                    <button class="button button-secondary" id="next-page" type="button">Successiva</button>
                </div>

                <noscript>
                    <p class="empty-state">Per usare la ricerca e prenotare un brano devi abilitare JavaScript.</p>
                </noscript>
            </div>
        </section>

        <section class="card my-requests-card">
            <div class="card-header">
                <h2 class="card-title">Le tue ultime prenotazioni</h2>
                <p class="card-subtitle"></p>
            </div>
            <div class="card-body">
                <p class="my-requests-meta" id="my-requests-meta">Aggiornamento in corso...</p>
                <div id="my-requests-list" class="request-list"></div>
            </div>
        </section>
    </div>
</main>

<script>
    window.PublicJoinBootstrap = {
        joinToken: @json($joinToken),
        eventCode: @json($eventNight->code),
        requestUrl: @json(route('public.join.request', $eventNight->code)),
        searchUrl: @json(route('public.join.songs', $eventNight->code)),
        etaUrl: @json(route('public.join.eta', $eventNight->code)),
        myRequestsUrl: @json(route('public.join.my-requests', $eventNight->code)),
        csrfToken: @json(csrf_token()),
        initialMyRequestsPayload: @json($myRequestsInitialPayload ?? ['data' => [], 'meta' => []]),
    };
</script>
<script src="{{ asset('js/public-join.js') }}" defer></script>
</body>
</html>
