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
    const joinTokenKey = @json(config('public_join.join_token_storage_key', 'join_token'));
    const joinToken = @json($joinToken);
    const eventCode = @json($eventNight->code);
    const requestUrl = @json(route('public.join.request', $eventNight->code));
    const searchUrl = @json(route('public.join.songs', $eventNight->code));
    const etaUrl = @json(route('public.join.eta', $eventNight->code));
    const myRequestsUrl = @json(route('public.join.my-requests', $eventNight->code));
    const csrfToken = @json(csrf_token());
    const initialMyRequestsPayload = @json($myRequestsInitialPayload ?? ['data' => [], 'meta' => []]);

    localStorage.setItem(joinTokenKey, joinToken);

    const elements = {
        displayNameInput: document.getElementById('display-name'),
        searchInput: document.getElementById('song-search'),
        results: document.getElementById('song-results'),
        meta: document.getElementById('search-meta'),
        prevPage: document.getElementById('prev-page'),
        nextPage: document.getElementById('next-page'),
        pageInfo: document.getElementById('page-info'),
        clientMessage: document.getElementById('client-message'),
        clientMessageTitle: document.getElementById('client-message-title'),
        clientMessageText: document.getElementById('client-message-text'),
        myRequestsList: document.getElementById('my-requests-list'),
        myRequestsMeta: document.getElementById('my-requests-meta'),
    };

    const state = {
        query: '',
        page: 1,
        lastPage: 1,
        perPage: 10,
        pendingSearch: null,
        myRequestsPollTimer: null,
    };

    const storageKeys = {
        displayName: `public_join_display_name:${eventCode}`,
    };

    const playbackLabelMap = {
        idle: 'In attesa',
        playing: 'In corso',
        paused: 'In pausa',
    };

    const getDisplayName = () => {
        return (elements.displayNameInput.value || '').trim();
    };

    const setClientMessage = (variant, title, message) => {
        const variants = ['notice--success', 'notice--error', 'notice--warning', 'notice--info'];
        variants.forEach((className) => elements.clientMessage.classList.remove(className));

        const normalizedVariant = ['success', 'error', 'warning', 'info'].includes(variant) ? variant : 'info';
        elements.clientMessage.classList.add(`notice--${normalizedVariant}`);
        elements.clientMessageTitle.textContent = title;
        elements.clientMessageText.textContent = message;
        elements.clientMessage.hidden = false;
    };

    const clearClientMessage = () => {
        elements.clientMessage.hidden = true;
        elements.clientMessageTitle.textContent = '';
        elements.clientMessageText.textContent = '';
    };

    const formatClientTime = (isoString) => {
        if (!isoString) {
            return null;
        }

        const date = new Date(isoString);

        if (Number.isNaN(date.getTime())) {
            return null;
        }

        return date.toLocaleTimeString('it-IT', {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const extractErrorMessage = async (response, fallbackMessage) => {
        try {
            const payload = await response.json();

            if (payload?.message && typeof payload.message === 'string') {
                return payload.message;
            }

            if (payload?.errors && typeof payload.errors === 'object') {
                const first = Object.values(payload.errors).flat()[0];
                if (typeof first === 'string' && first.trim() !== '') {
                    return first;
                }
            }
        } catch (error) {
            // Ignore parser failures and use fallback.
        }

        return fallbackMessage;
    };

    const renderSearchEmpty = (message) => {
        elements.results.innerHTML = '';
        const empty = document.createElement('div');
        empty.className = 'empty-state';
        empty.textContent = message;
        elements.results.appendChild(empty);
    };

    const renderResults = (payload) => {
        elements.results.innerHTML = '';

        if (!Array.isArray(payload.data) || payload.data.length === 0) {
            renderSearchEmpty('Nessuna canzone trovata. Prova con un altro titolo o artista.');
        } else {
            payload.data.forEach((song) => {
                const container = document.createElement('article');
                container.className = 'song-result';

                const main = document.createElement('div');
                main.className = 'song-main';

                const title = document.createElement('h3');
                title.className = 'song-title';
                title.textContent = song.title || 'Titolo non disponibile';

                const artist = document.createElement('p');
                artist.className = 'song-artist';
                artist.textContent = song.artist || 'Artista sconosciuto';

                main.appendChild(title);
                main.appendChild(artist);

                const form = document.createElement('form');
                form.className = 'song-request-form';
                form.method = 'POST';
                form.action = requestUrl;
                form.dataset.songTitle = song.title || 'questo brano';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;

                const songInput = document.createElement('input');
                songInput.type = 'hidden';
                songInput.name = 'song_id';
                songInput.value = String(song.id);

                const joinInput = document.createElement('input');
                joinInput.type = 'hidden';
                joinInput.name = 'join_token';
                joinInput.value = joinToken;

                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'display_name';
                nameInput.value = getDisplayName();

                const button = document.createElement('button');
                button.className = 'button button-primary';
                button.type = 'submit';
                button.textContent = 'Prenota';

                form.appendChild(csrfInput);
                form.appendChild(songInput);
                form.appendChild(joinInput);
                form.appendChild(nameInput);
                form.appendChild(button);

                container.appendChild(main);
                container.appendChild(form);
                elements.results.appendChild(container);
            });
        }

        elements.pageInfo.textContent = `Pagina ${payload.meta.current_page} di ${payload.meta.last_page}`;
        elements.prevPage.disabled = payload.meta.current_page <= 1;
        elements.nextPage.disabled = payload.meta.current_page >= payload.meta.last_page;

        if ((payload.meta.total || 0) === 0) {
            elements.meta.textContent = 'Nessun risultato disponibile.';
        } else {
            elements.meta.textContent = `Trovate ${payload.meta.total} canzoni.`;
        }
    };

    const fetchSongs = async () => {
        const params = new URLSearchParams({
            q: state.query,
            page: String(state.page),
            per_page: String(state.perPage),
        });

        elements.meta.textContent = 'Caricamento canzoni...';

        let response;

        try {
            response = await fetch(`${searchUrl}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            });
        } catch (error) {
            const message = 'Connessione non disponibile. Ricarica la pagina e riprova.';
            renderSearchEmpty(message);
            elements.pageInfo.textContent = '';
            elements.prevPage.disabled = true;
            elements.nextPage.disabled = true;
            elements.meta.textContent = message;
            return;
        }

        if (!response.ok) {
            const message = await extractErrorMessage(response, 'Impossibile caricare le canzoni adesso.');
            renderSearchEmpty(message);
            elements.pageInfo.textContent = '';
            elements.prevPage.disabled = true;
            elements.nextPage.disabled = true;
            elements.meta.textContent = message;
            return;
        }

        const payload = await response.json();
        state.lastPage = payload.meta?.last_page || 1;
        renderResults(payload);
    };

    const scheduleSearch = () => {
        if (state.pendingSearch) {
            window.clearTimeout(state.pendingSearch);
        }

        state.pendingSearch = window.setTimeout(() => {
            state.page = 1;
            fetchSongs();
        }, 250);
    };

    const renderMyRequests = (payload) => {
        const list = Array.isArray(payload?.data) ? payload.data : [];
        const latestList = [...list]
            .sort((a, b) => {
                const aTimestamp = Date.parse(a?.requested_at || '');
                const bTimestamp = Date.parse(b?.requested_at || '');

                if (Number.isNaN(aTimestamp) && Number.isNaN(bTimestamp)) {
                    return 0;
                }

                if (Number.isNaN(aTimestamp)) {
                    return 1;
                }

                if (Number.isNaN(bTimestamp)) {
                    return -1;
                }

                return bTimestamp - aTimestamp;
            })
            .slice(0, 3);
        const meta = payload?.meta || {};

        elements.myRequestsList.innerHTML = '';

        if (latestList.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'Non hai ancora prenotazioni personali.';
            elements.myRequestsList.appendChild(empty);
        } else {
            latestList.forEach((item) => {
                const requestItem = document.createElement('article');
                requestItem.className = `request-item request-item--${item.status || 'queued'}`;

                const top = document.createElement('div');
                top.className = 'request-top';

                const left = document.createElement('div');

                const title = document.createElement('h3');
                title.className = 'request-title';
                title.textContent = item.title || 'Brano';

                const artist = document.createElement('p');
                artist.className = 'request-artist';
                artist.textContent = item.artist || 'Artista sconosciuto';

                left.appendChild(title);
                left.appendChild(artist);

                const badge = document.createElement('span');
                badge.className = `request-badge request-badge--${item.status || 'queued'}`;
                badge.textContent = item.status_label || 'In coda';

                top.appendChild(left);
                top.appendChild(badge);

                const dataLine = document.createElement('p');
                dataLine.className = 'request-data';

                const infoParts = [];

                if (item.queue_position) {
                    infoParts.push(`Posizione in coda: #${item.queue_position}`);
                }

                if (item.requested_at_label) {
                    infoParts.push(`Prenotata alle ${item.requested_at_label}`);
                }

                if (item.scheduled_at_label && item.status === 'queued') {
                    infoParts.push(`Turno stimato: ${item.scheduled_at_label}`);
                }

                if (item.played_at_label && (item.status === 'played' || item.status === 'skipped' || item.status === 'canceled')) {
                    infoParts.push(`Aggiornata alle ${item.played_at_label}`);
                }

                dataLine.textContent = infoParts.length > 0 ? infoParts.join(' • ') : 'Informazioni in aggiornamento';

                const note = document.createElement('p');
                note.className = 'request-note';
                note.textContent = item.timeline_note || 'Stato in aggiornamento.';

                if (item.status === 'played' || item.status === 'skipped' || item.status === 'canceled') {
                    note.classList.add('request-note--muted');
                }

                requestItem.appendChild(top);
                requestItem.appendChild(dataLine);
                requestItem.appendChild(note);

                elements.myRequestsList.appendChild(requestItem);
            });
        }

        const statusLabel = playbackLabelMap[meta.playback_state] || 'In aggiornamento';
        const updatedAtLabel = formatClientTime(meta.updated_at);
        const suffix = updatedAtLabel ? ` • Aggiornato alle ${updatedAtLabel}` : '';
        elements.myRequestsMeta.textContent = `Stato serata: ${statusLabel}${suffix}`;
    };

    const fetchMyRequests = async ({ silent = false } = {}) => {
        const params = new URLSearchParams({
            join_token: joinToken,
        });

        if (!silent) {
            elements.myRequestsMeta.textContent = 'Aggiornamento prenotazioni personali...';
        }

        let response;

        try {
            response = await fetch(`${myRequestsUrl}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            });
        } catch (error) {
            if (!silent) {
                const empty = document.createElement('div');
                empty.className = 'empty-state';
                empty.textContent = 'Connessione non disponibile. Ricarica la pagina per aggiornare le tue prenotazioni.';
                elements.myRequestsList.innerHTML = '';
                elements.myRequestsList.appendChild(empty);
                elements.myRequestsMeta.textContent = 'Aggiornamento non disponibile';
            }

            return;
        }

        if (!response.ok) {
            const message = await extractErrorMessage(response, 'Impossibile aggiornare le tue prenotazioni adesso.');

            if (!silent) {
                const empty = document.createElement('div');
                empty.className = 'empty-state';
                empty.textContent = `${message} Ricarica la pagina per riprovare.`;
                elements.myRequestsList.innerHTML = '';
                elements.myRequestsList.appendChild(empty);
                elements.myRequestsMeta.textContent = 'Aggiornamento non disponibile';
            }

            return;
        }

        const payload = await response.json();
        renderMyRequests(payload);
    };

    elements.displayNameInput.addEventListener('input', () => {
        clearClientMessage();
        localStorage.setItem(storageKeys.displayName, getDisplayName());
    });

    elements.searchInput.addEventListener('input', (event) => {
        state.query = event.target.value.trim();
        scheduleSearch();
    });

    elements.prevPage.addEventListener('click', () => {
        if (state.page > 1) {
            state.page -= 1;
            fetchSongs();
        }
    });

    elements.nextPage.addEventListener('click', () => {
        if (state.page < state.lastPage) {
            state.page += 1;
            fetchSongs();
        }
    });

    elements.results.addEventListener('submit', async (event) => {
        if (!event.target.matches('.song-request-form')) {
            return;
        }

        event.preventDefault();
        clearClientMessage();

        const form = event.target;
        const button = form.querySelector('button[type="submit"]');
        const hiddenNameInput = form.querySelector('input[name="display_name"]');
        const displayName = getDisplayName();

        if (displayName.length < 2) {
            setClientMessage('error', 'Nome mancante', 'Inserisci il tuo nome (almeno 2 caratteri) prima di prenotare.');
            elements.displayNameInput.focus();
            return;
        }

        hiddenNameInput.value = displayName;

        if (button) {
            button.disabled = true;
            button.textContent = 'Controllo attesa...';
        }

        let shouldSubmit = false;

        try {
            const response = await fetch(`${etaUrl}?join_token=${encodeURIComponent(joinToken)}`, {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
                const message = await extractErrorMessage(response, 'Impossibile calcolare l\'attesa in questo momento.');
                shouldSubmit = confirm(`${message}\n\nVuoi comunque confermare la prenotazione?`);
            } else {
                const payload = await response.json();
                const songTitle = form.dataset.songTitle || 'questo brano';
                shouldSubmit = confirm(`Attesa stimata prima del tuo turno: ${payload.eta_label}.\n\nConfermi la prenotazione di "${songTitle}" a nome "${displayName}"?`);
            }
        } catch (error) {
            shouldSubmit = confirm('Connessione momentaneamente instabile. Vuoi confermare comunque la prenotazione?');
        }

        if (shouldSubmit) {
            form.submit();
            return;
        }

        if (button) {
            button.disabled = false;
            button.textContent = 'Prenota';
        }
    });

    const hydrateDisplayName = () => {
        const current = getDisplayName();

        if (current.length >= 2) {
            localStorage.setItem(storageKeys.displayName, current);
            return;
        }

        const savedName = (localStorage.getItem(storageKeys.displayName) || '').trim();

        if (savedName.length >= 2) {
            elements.displayNameInput.value = savedName;
        }
    };

    const startMyRequestsPolling = () => {
        if (state.myRequestsPollTimer) {
            window.clearInterval(state.myRequestsPollTimer);
        }

        state.myRequestsPollTimer = window.setInterval(() => {
            fetchMyRequests({ silent: true });
        }, 20000);
    };

    hydrateDisplayName();
    renderMyRequests(initialMyRequestsPayload);
    fetchMyRequests({ silent: true });
    startMyRequestsPolling();
    fetchSongs();
</script>
</body>
</html>
