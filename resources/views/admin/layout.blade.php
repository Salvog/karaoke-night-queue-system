<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amministrazione | Karaoke Night</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #0f1021;
            --bg-soft: #1b1b32;
            --card: #101526;
            --card-soft: #121a30;
            --text: #f8fafc;
            --muted: #cbd5f5;
            --accent: #ff4dd8;
            --accent-2: #20d9ff;
            --accent-3: #7c5cff;
            --success: #22c55e;
            --danger: #ef4444;
            --border: rgba(148, 163, 184, 0.2);
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            background: radial-gradient(circle at top, #23245b 0%, #0f1021 45%, #090b18 100%);
            color: var(--text);
        }
        nav {
            background: linear-gradient(120deg, rgba(255, 77, 216, 0.25), rgba(32, 217, 255, 0.2)), var(--bg);
            padding: 16px 24px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 20px rgba(5, 7, 23, 0.55);
        }
        nav .nav-links { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        nav a { color: #fff; text-decoration: none; font-weight: 600; letter-spacing: 0.01em; }
        nav a:hover { color: var(--accent-2); }
        nav form { display: inline; margin: 0; }
        main { padding: 32px 24px 56px; max-width: 1200px; margin: 0 auto; }
        h1, h2, h3 { color: #fff; }
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .subtitle { margin: 6px 0 0; color: var(--muted); font-size: 14px; }
        .card {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.05), rgba(16, 21, 38, 0.95));
            padding: 24px;
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(8, 11, 30, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(6px);
        }
        .status { margin-bottom: 16px; color: var(--accent-2); font-weight: 600; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 16, 33, 0.8);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        th, td { padding: 12px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background: rgba(255, 255, 255, 0.04); font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); }
        td { color: #e2e8f0; }
        .actions, .form-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
        .button {
            background: linear-gradient(120deg, var(--accent), var(--accent-3));
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.02em;
            box-shadow: 0 10px 20px rgba(255, 77, 216, 0.25);
        }
        .button.secondary { background: rgba(148, 163, 184, 0.2); color: #f1f5f9; box-shadow: none; }
        .button.danger { background: linear-gradient(120deg, #f87171, var(--danger)); }
        .button.success { background: linear-gradient(120deg, #34d399, var(--success)); }
        .pill { padding: 4px 12px; border-radius: 999px; background: rgba(124, 92, 255, 0.2); font-size: 12px; font-weight: 700; color: #e0e7ff; }
        .panel {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.05), rgba(16, 24, 45, 0.95));
            border-radius: 14px;
            padding: 16px;
            border: 1px solid var(--border);
            color: #e2e8f0;
        }
        .panel.muted { background: rgba(255, 255, 255, 0.03); color: var(--muted); }
        .panel-row { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.2em; color: var(--muted); margin-bottom: 6px; }
        .value { font-weight: 700; color: #fff; }
        .grid { display: grid; gap: 16px; }
        .grid.two { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .grid.three { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .muted { color: var(--muted); }
        .helper { font-size: 13px; color: var(--muted); margin-top: 6px; }
        .chip { background: rgba(32, 217, 255, 0.2); color: #b6f0ff; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .divider { border-top: 1px solid var(--border); margin: 16px 0; }
        .form-grid {
            display: grid;
            gap: 16px;
            max-width: 920px;
        }
        .form-field { display: flex; flex-direction: column; gap: 8px; }
        input[type="text"], input[type="number"], input[type="datetime-local"], textarea, select {
            width: 100%;
            max-width: 420px;
            box-sizing: border-box;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            font-size: 14px;
            background: rgba(15, 23, 42, 0.6);
            color: #f8fafc;
        }
        input[readonly] { opacity: 0.8; }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent-2);
            box-shadow: 0 0 0 3px rgba(32, 217, 255, 0.2);
        }
        textarea { min-height: 120px; resize: vertical; }
        @media (min-width: 768px) {
            .form-grid { grid-template-columns: repeat(2, minmax(260px, 1fr)); }
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
