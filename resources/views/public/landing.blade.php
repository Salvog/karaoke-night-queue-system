<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Join</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #0f172a; color: #f8fafc; }
        header { padding: 24px; background: #111827; }
        main { padding: 24px; max-width: 900px; margin: 0 auto; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        .card { background: #1f2937; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .song { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #374151; }
        .song:last-child { border-bottom: none; }
        .button { background: #38bdf8; color: #0f172a; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .button.secondary { background: #64748b; color: #f8fafc; }
        .status { color: #38bdf8; margin-bottom: 12px; }
        .errors { color: #fca5a5; margin-bottom: 12px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input[type="password"] { padding: 8px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; width: 200px; }
        .cooldown { font-size: 14px; color: #cbd5f5; }
    </style>
</head>
<body>
<header>
    <h1>Join {{ $eventNight->venue?->name ?? 'Karaoke Night' }}</h1>
    <div>Event code: <strong>{{ $eventNight->code }}</strong></div>
</header>
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($eventNight->join_pin)
        <div class="card">
            <form method="POST" action="{{ route('public.join.activate', $eventNight->code) }}">
                @csrf
                <label for="pin">Enter PIN to activate</label>
                <input id="pin" name="pin" type="password" autocomplete="one-time-code">
                <button class="button secondary" type="submit">Activate</button>
            </form>
        </div>
    @endif

    <div class="card">
        <h2>Request a song</h2>
        @if ($eventNight->request_cooldown_seconds > 0)
            <p class="cooldown">You can request a song every {{ $eventNight->request_cooldown_seconds }} seconds.</p>
        @endif
        @foreach ($songs as $song)
            <div class="song">
                <div>
                    <strong>{{ $song->title }}</strong><br>
                    <span>{{ $song->artist }}</span>
                </div>
                <form method="POST" action="{{ route('public.join.request', $eventNight->code) }}">
                    @csrf
                    <input type="hidden" name="song_id" value="{{ $song->id }}">
                    <input type="hidden" name="join_token" value="{{ $joinToken }}">
                    <button class="button" type="submit">Request</button>
                </form>
            </div>
        @endforeach
    </div>
</main>
<script>
    localStorage.setItem('{{ config('public_join.join_token_storage_key', 'join_token') }}', @json($joinToken));
    document.querySelectorAll('input[name="join_token"]').forEach((input) => {
        input.value = @json($joinToken);
    });
</script>
</body>
</html>
