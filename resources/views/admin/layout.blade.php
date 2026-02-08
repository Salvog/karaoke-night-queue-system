<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amministrazione | Karaoke Night</title>
    <style>
        :root {
            --bg-start: #150b2d;
            --bg-mid: #0f1235;
            --bg-end: #1f0f2c;
            --surface: rgba(18, 19, 44, 0.78);
            --surface-strong: rgba(14, 15, 34, 0.92);
            --surface-soft: rgba(250, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.16);
            --text: #f9f9ff;
            --muted: #b5b9dd;
            --accent: #ff4fd8;
            --accent-cyan: #2ad8ff;
            --accent-gold: #ffd447;
            --success: #33df9d;
            --danger: #ff6286;
            --shadow: 0 20px 40px rgba(8, 8, 24, 0.4);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: 'Poppins', 'Nunito Sans', 'Trebuchet MS', sans-serif;
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 79, 216, 0.18), transparent 35%),
                radial-gradient(circle at 84% 22%, rgba(42, 216, 255, 0.2), transparent 38%),
                radial-gradient(circle at 60% 75%, rgba(255, 212, 71, 0.1), transparent 44%),
                linear-gradient(140deg, var(--bg-start), var(--bg-mid) 45%, var(--bg-end));
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 999px;
            pointer-events: none;
            z-index: -1;
            animation: glow-float 14s ease-in-out infinite;
        }

        body::before {
            width: 340px;
            height: 340px;
            top: -120px;
            right: -80px;
            background: radial-gradient(circle, rgba(255, 79, 216, 0.4), rgba(255, 79, 216, 0));
        }

        body::after {
            width: 300px;
            height: 300px;
            bottom: -110px;
            left: -90px;
            background: radial-gradient(circle, rgba(42, 216, 255, 0.35), rgba(42, 216, 255, 0));
            animation-delay: 2s;
        }

        @keyframes glow-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-16px); }
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            background: linear-gradient(90deg, rgba(15, 12, 35, 0.88), rgba(16, 17, 50, 0.8));
        }

        .topbar-inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.15;
        }

        .brand small {
            display: block;
            color: var(--muted);
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: border-color 150ms ease, background-color 150ms ease, transform 150ms ease;
        }

        .nav-links a:hover {
            border-color: rgba(255, 79, 216, 0.45);
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }

        .logout-form {
            margin: 0;
        }

        main {
            max-width: 1240px;
            margin: 0 auto;
            padding: 28px 20px 44px;
        }

        .status,
        .error-box {
            margin-bottom: 16px;
            border-radius: 12px;
            padding: 12px 14px;
            font-weight: 600;
            border: 1px solid;
        }

        .status {
            color: #c8fff0;
            border-color: rgba(51, 223, 157, 0.35);
            background: rgba(51, 223, 157, 0.14);
        }

        .error-box {
            color: #ffd5db;
            border-color: rgba(255, 98, 134, 0.35);
            background: rgba(255, 98, 134, 0.14);
        }

        .error-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 22px;
            border-radius: 18px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
        }

        h1, h2, h3 {
            margin-top: 0;
            color: #ffffff;
            letter-spacing: 0.01em;
        }

        h1 {
            font-size: clamp(1.5rem, 1.9vw, 2rem);
        }

        p {
            color: var(--muted);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 14px;
            overflow: hidden;
            background: var(--surface-strong);
            border: 1px solid var(--border);
        }

        th,
        td {
            padding: 11px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.09);
            text-align: left;
            vertical-align: top;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        th {
            background: rgba(255, 255, 255, 0.06);
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 700;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .button {
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            color: #0a0a1f;
            background: linear-gradient(120deg, var(--accent-gold), #fff4b8);
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
        }

        .button:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .button.secondary {
            color: #edf3ff;
            background: linear-gradient(130deg, rgba(42, 216, 255, 0.3), rgba(95, 104, 255, 0.34));
            border-color: rgba(42, 216, 255, 0.45);
        }

        .button.success {
            color: #04240f;
            background: linear-gradient(130deg, #50f0b5, #9ff2cc);
        }

        .button.danger {
            color: #3c0412;
            background: linear-gradient(130deg, #ff8aa5, #ffc2cd);
        }

        .button[disabled] {
            opacity: 0.45;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .pill,
        .chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .panel {
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .panel.muted {
            background: rgba(255, 255, 255, 0.05);
            color: var(--muted);
        }

        .panel-row {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .value {
            font-weight: 700;
            color: #fff;
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .grid.two {
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        }

        .grid.three {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .muted {
            color: var(--muted);
        }

        .helper {
            font-size: 12px;
            color: var(--muted);
            margin-top: 6px;
        }

        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            margin: 16px 0;
        }

        .form-grid {
            display: grid;
            gap: 12px;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        input[type="email"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            max-width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 11px;
            padding: 10px 12px;
            color: #f6f8ff;
            background: rgba(6, 8, 27, 0.72);
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: rgba(42, 216, 255, 0.8);
            box-shadow: 0 0 0 3px rgba(42, 216, 255, 0.2);
        }

        input[readonly] {
            color: #fbe9ff;
            border-color: rgba(255, 79, 216, 0.35);
            background: rgba(255, 79, 216, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        @media (max-width: 760px) {
            main {
                padding: 20px 14px 30px;
            }

            .card {
                border-radius: 14px;
                padding: 16px;
            }

            .topbar-inner {
                padding: 12px 14px;
            }

            .brand {
                font-size: 16px;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand">
            Karaoke Control Room
            <small>Admin Experience</small>
        </div>
        <nav class="nav-links">
            <a href="{{ route('admin.dashboard') }}">Panoramica</a>
            <a href="{{ route('admin.events.index') }}">Eventi</a>
            <a href="{{ route('admin.songs.index') }}">Canzoni</a>
            <a href="{{ route('admin.venues.index') }}">Location</a>
            @isset($eventNight)
                <a href="{{ route('admin.queue.show', $eventNight) }}">Coda</a>
                <a href="{{ route('admin.theme.show', $eventNight) }}">Tema/Annunci</a>
            @endisset
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
            @csrf
            <button class="button secondary" type="submit">Esci</button>
        </form>
    </div>
</header>
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="error-box">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        @yield('content')
    </div>
</main>
</body>
</html>
