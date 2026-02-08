<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amministrazione | Karaoke Night</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #07090f;
            --bg-accent: #111827;
            --surface: rgba(17, 24, 39, 0.88);
            --surface-strong: #111827;
            --card-border: rgba(148, 163, 184, 0.2);
            --text: #f8fafc;
            --text-muted: #cbd5f5;
            --accent: #f472b6;
            --accent-strong: #ec4899;
            --accent-cyan: #22d3ee;
            --accent-lime: #a3e635;
            --accent-orange: #f59e0b;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            background: radial-gradient(circle at top, rgba(236, 72, 153, 0.18), transparent 45%),
                radial-gradient(circle at 20% 20%, rgba(34, 211, 238, 0.2), transparent 35%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        nav {
            background: linear-gradient(120deg, rgba(30, 41, 59, 0.95), rgba(30, 64, 175, 0.8));
            padding: 18px 24px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.35);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        nav .nav-links { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        nav a { color: #fff; text-decoration: none; font-weight: 600; letter-spacing: 0.02em; }
        nav a:hover { color: var(--accent-cyan); }
        nav form { display: inline; margin: 0; }
        main { padding: 36px 24px; max-width: 1200px; margin: 0 auto; }
        h1, h2, h3 { color: #fff; }
        .card {
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.95), rgba(2, 6, 23, 0.95));
            padding: 24px;
            border-radius: 18px;
            border: 1px solid var(--card-border);
            box-shadow: 0 20px 40px rgba(8, 12, 24, 0.45);
        }
        .status {
            margin-bottom: 16px;
            color: var(--accent-lime);
            font-weight: 600;
            background: rgba(163, 230, 53, 0.12);
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid rgba(163, 230, 53, 0.35);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--card-border);
        }
        th, td { padding: 12px 14px; border-bottom: 1px solid rgba(148, 163, 184, 0.18); text-align: left; }
        th {
            background: rgba(15, 23, 42, 0.9);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(226, 232, 240, 0.72);
        }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .button {
            background: linear-gradient(135deg, var(--accent-strong), var(--accent));
            color: #fff;
            border: none;
            padding: 9px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 22px rgba(236, 72, 153, 0.25);
        }
        .button.secondary {
            background: rgba(148, 163, 184, 0.15);
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.3);
            box-shadow: none;
        }
        .button.danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            box-shadow: 0 10px 22px rgba(239, 68, 68, 0.25);
        }
        .button.success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 10px 22px rgba(34, 197, 94, 0.25);
        }
        .pill {
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.2);
            font-size: 12px;
            font-weight: 600;
            color: #c7d2fe;
        }
        .panel {
            background: var(--surface);
            border-radius: 14px;
            padding: 18px;
            border: 1px solid var(--card-border);
        }
        .panel.muted { background: rgba(15, 23, 42, 0.6); color: rgba(226, 232, 240, 0.7); }
        .panel-row { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
        .label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(226, 232, 240, 0.6); margin-bottom: 4px; }
        .value { font-weight: 600; color: #fff; }
        .grid { display: grid; gap: 16px; }
        .grid.two { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .grid.three { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .muted { color: rgba(226, 232, 240, 0.7); }
        .helper { font-size: 13px; color: rgba(226, 232, 240, 0.65); margin-top: 6px; }
        .chip { background: rgba(59, 130, 246, 0.2); color: #bfdbfe; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .divider { border-top: 1px solid rgba(148, 163, 184, 0.2); margin: 16px 0; }
        .form-grid { display: grid; gap: 16px; }
        .form-field { display: flex; flex-direction: column; gap: 6px; }
        input[type="text"], input[type="number"], input[type="datetime-local"], textarea, select {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 11px 12px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            font-size: 14px;
            background: rgba(15, 23, 42, 0.65);
            color: #f8fafc;
        }
        input::placeholder, textarea::placeholder { color: rgba(148, 163, 184, 0.7); }
        input:focus, textarea:focus, select:focus {
            outline: 2px solid rgba(244, 114, 182, 0.6);
            border-color: rgba(244, 114, 182, 0.7);
        }
        textarea { min-height: 120px; resize: vertical; }
        @media (min-width: 768px) {
            .form-grid { grid-template-columns: repeat(2, minmax(240px, 1fr)); }
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
