<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Karaoke Night</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Inter", "Segoe UI", Arial, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        nav { background: #111827; padding: 14px 24px; color: #fff; display: flex; align-items: center; gap: 16px; }
        nav a { color: #fff; text-decoration: none; font-weight: 600; opacity: 0.9; }
        nav a:hover { opacity: 1; }
        nav form { margin-left: auto; }
        main { padding: 28px; }
        h1, h2, h3 { margin-top: 0; }
        label { font-weight: 600; display: block; margin-bottom: 6px; }
        input, select, textarea { width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 14px; }
        textarea { min-height: 120px; resize: vertical; }
        small.help { display: block; margin-top: 6px; color: #6b7280; }
        .card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); }
        .card-panel { background: #f9fafb; padding: 16px; border-radius: 10px; border: 1px solid #e5e7eb; }
        .status { margin-bottom: 16px; color: #0f766e; font-weight: 600; }
        .error-box { margin-bottom: 16px; padding: 12px 16px; border-radius: 8px; background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 14px; }
        th { background: #f9fafb; color: #374151; font-weight: 600; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .button { background: #111827; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .button.secondary { background: #4b5563; }
        .button.danger { background: #b91c1c; }
        .button.outline { background: #fff; color: #111827; border: 1px solid #d1d5db; }
        .pill { padding: 2px 10px; border-radius: 999px; background: #e5e7eb; font-size: 12px; font-weight: 600; }
        .badge { padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge.success { background: #dcfce7; color: #15803d; }
        .muted { color: #6b7280; }
        .section { margin-bottom: 24px; }
        .section-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .split { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    </style>
</head>
<body>
<nav>
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.events.index') }}">Events</a>
    <a href="{{ route('admin.songs.index') }}">Songs</a>
    <a href="{{ route('admin.venues.index') }}">Venues</a>
    @isset($eventNight)
        <a href="{{ route('admin.queue.show', $eventNight) }}">Queue</a>
        <a href="{{ route('admin.theme.show', $eventNight) }}">Theme/Ads</a>
    @endisset
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="button secondary" type="submit">Logout</button>
    </form>
</nav>
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
