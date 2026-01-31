<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Karaoke Night</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        nav { background: #0f172a; padding: 16px 24px; color: #fff; display: flex; align-items: center; justify-content: space-between; }
        nav .nav-links { display: flex; gap: 16px; align-items: center; }
        nav a { color: #fff; text-decoration: none; font-weight: 600; }
        nav form { display: inline; margin: 0; }
        main { padding: 32px 24px; max-width: 1200px; margin: 0 auto; }
        h1, h2, h3 { color: #0f172a; }
        .card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08); }
        .status { margin-bottom: 16px; color: #0f766e; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f9fafb; font-size: 13px; text-transform: uppercase; letter-spacing: 0.02em; color: #6b7280; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .button { background: #111827; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .button.secondary { background: #4b5563; }
        .button.danger { background: #b91c1c; }
        .button.success { background: #16a34a; }
        .pill { padding: 4px 10px; border-radius: 999px; background: #e5e7eb; font-size: 12px; font-weight: 600; }
        .panel { background: #fff; border-radius: 10px; padding: 16px; border: 1px solid #e5e7eb; }
        .panel.muted { background: #f9fafb; color: #6b7280; }
        .panel-row { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
        .label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 4px; }
        .value { font-weight: 600; color: #111827; }
        .grid { display: grid; gap: 16px; }
        .grid.two { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .grid.three { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .muted { color: #6b7280; }
        .helper { font-size: 13px; color: #6b7280; margin-top: 6px; }
        .chip { background: #eef2ff; color: #3730a3; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .divider { border-top: 1px solid #e5e7eb; margin: 16px 0; }
        .form-grid { display: grid; gap: 12px; }
        input[type="text"], input[type="number"], input[type="datetime-local"], textarea, select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
        }
        textarea { min-height: 120px; resize: vertical; }
    </style>
</head>
<body>
<nav>
    <div class="nav-links">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.events.index') }}">Events</a>
        <a href="{{ route('admin.songs.index') }}">Songs</a>
        <a href="{{ route('admin.venues.index') }}">Venues</a>
        @isset($eventNight)
            <a href="{{ route('admin.queue.show', $eventNight) }}">Queue</a>
            <a href="{{ route('admin.theme.show', $eventNight) }}">Theme/Ads</a>
        @endisset
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="button secondary" type="submit">Logout</button>
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
