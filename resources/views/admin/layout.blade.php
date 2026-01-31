<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Karaoke Night</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f7f7f7; }
        nav { background: #1f2937; padding: 12px 20px; color: #fff; }
        nav a { color: #fff; margin-right: 16px; text-decoration: none; font-weight: 600; }
        nav form { display: inline; }
        main { padding: 24px; }
        .card { background: #fff; padding: 16px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .status { margin-bottom: 16px; color: #0f766e; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .button { background: #111827; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .button.secondary { background: #4b5563; }
        .button.danger { background: #b91c1c; }
        .pill { padding: 2px 8px; border-radius: 999px; background: #e5e7eb; font-size: 12px; }
    </style>
</head>
<body>
<nav>
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.events.index') }}">Events</a>
    <a href="{{ route('admin.venues.index') }}">Venues</a>
    <a href="{{ route('admin.songs.index') }}">Songs</a>
    @isset($eventNight)
        <a href="{{ route('admin.queue.show', $eventNight) }}">Queue</a>
        <a href="{{ route('admin.theme.show', $eventNight) }}">Theme/Ads</a>
    @endisset
    <form method="POST" action="{{ route('admin.logout') }}" style="float: right;">
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
