<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amministrazione | Karaoke Night</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #0b0b1f;
            --surface: rgba(18, 24, 53, 0.88);
            --surface-strong: rgba(20, 27, 58, 0.96);
            --surface-soft: rgba(255, 255, 255, 0.08);
            --text: #f8fafc;
            --text-muted: #cbd5f5;
            --accent: #38bdf8;
            --accent-strong: #f472b6;
            --accent-secondary: #a855f7;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #f43f5e;
            --outline: rgba(148, 163, 184, 0.25);
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            background: radial-gradient(circle at top, #1e1b4b 0%, #0b0b1f 45%, #050510 100%);
            color: var(--text);
            min-height: 100vh;
        }
        nav {
            background: linear-gradient(120deg, rgba(56, 189, 248, 0.25), rgba(168, 85, 247, 0.2));
            padding: 18px 24px;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            backdrop-filter: blur(12px);
        }
        nav .nav-links { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
        nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 999px;
            transition: background 0.2s ease, color 0.2s ease;
        }
        nav a:hover { background: rgba(56, 189, 248, 0.2); color: #fff; }
        nav form { display: inline; margin: 0; }

        main { padding: 32px 24px 48px; max-width: 1200px; margin: 0 auto; }
        h1, h2, h3 { color: var(--text); letter-spacing: 0.01em; }
        h1 { font-size: 30px; margin-bottom: 12px; }
        .card {
            background: var(--surface);
            padding: 24px;
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }
        .status { margin-bottom: 16px; color: var(--accent); font-weight: 600; }
        table { width: 100%; border-collapse: collapse; background: var(--surface-strong); border-radius: 14px; overflow: hidden; }
        th, td { padding: 12px 14px; border-bottom: 1px solid rgba(148, 163, 184, 0.2); text-align: left; }
        th {
            background: rgba(15, 23, 42, 0.45);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
        }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .button {
            background: linear-gradient(120deg, var(--accent), var(--accent-secondary));
            color: #0b0b1f;
            border: none;
            padding: 10px 16px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 10px 18px rgba(56, 189, 248, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .button:hover { transform: translateY(-1px); box-shadow: 0 14px 24px rgba(56, 189, 248, 0.3); }
        .button.secondary { background: rgba(148, 163, 184, 0.2); color: var(--text); box-shadow: none; }
        .button.danger { background: linear-gradient(120deg, #fb7185, var(--danger)); color: #fff; }
        .button.success { background: linear-gradient(120deg, #22c55e, #4ade80); color: #052e16; }
        .pill {
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.2);
            color: var(--text);
            font-size: 12px;
            font-weight: 700;
        }
        .panel {
            background: var(--surface-strong);
            border-radius: 16px;
            padding: 18px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.2);
        }
        .panel.muted { background: rgba(148, 163, 184, 0.08); color: var(--text-muted); }
        .panel-row { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.14em; color: var(--text-muted); margin-bottom: 6px; }
        .value { font-weight: 700; color: var(--text); }
        .grid { display: grid; gap: 16px; }
        .grid.two { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .grid.three { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .muted { color: var(--text-muted); }
        .helper { font-size: 13px; color: var(--text-muted); margin-top: 6px; }
        .chip {
            background: rgba(244, 114, 182, 0.2);
            color: #fbcfe8;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .divider { border-top: 1px solid rgba(148, 163, 184, 0.2); margin: 16px 0; }

        .form-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr;
            max-width: 960px;
        }
        .form-field { display: flex; flex-direction: column; gap: 6px; }
        input[type="text"], input[type="number"], input[type="datetime-local"], textarea, select {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--outline);
            background: rgba(15, 23, 42, 0.7);
            color: var(--text);
            font-size: 14px;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25);
        }
        input[readonly] { color: var(--text-muted); background: rgba(15, 23, 42, 0.5); }
        textarea { min-height: 120px; resize: vertical; }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(auto-fit, minmax(240px, 320px));
                justify-content: start;
            }
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-links">
        <a href="{{ route('admin.dashboard') }}">Panoramica</a>
        <a href="{{ route('admin.events.index') }}">Eventi</a>
        <a href="{{ route('admin.songs.index') }}">Canzoni</a>
        <a href="{{ route('admin.venues.index') }}">Location</a>
        @isset($eventNight)
            <a href="{{ route('admin.queue.show', $eventNight) }}">Coda</a>
            <a href="{{ route('admin.theme.show', $eventNight) }}">Tema/Annunci</a>
        @endisset
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="button secondary" type="submit">Esci</button>
    </form>
</nav>
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    <div class="card">
        @yield('content')
    </div>
</main>
</body>
</html>
