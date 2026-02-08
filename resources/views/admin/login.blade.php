<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accesso admin</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #07090f;
            --text: #f8fafc;
            --muted: rgba(226, 232, 240, 0.7);
            --accent: #ec4899;
            --accent-soft: #f472b6;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: radial-gradient(circle at top, rgba(236, 72, 153, 0.2), transparent 50%),
                radial-gradient(circle at 20% 10%, rgba(34, 211, 238, 0.2), transparent 45%),
                var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            color: var(--text);
        }
        .card {
            background: rgba(15, 23, 42, 0.92);
            padding: 36px;
            border-radius: 16px;
            width: min(380px, 90vw);
            box-shadow: 0 20px 40px rgba(8, 12, 24, 0.45);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        h1 { margin-top: 0; font-size: 24px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: var(--muted); }
        input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 16px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.7);
            color: var(--text);
        }
        input:focus {
            outline: 2px solid rgba(236, 72, 153, 0.6);
            border-color: rgba(236, 72, 153, 0.7);
        }
        .button {
            width: 100%;
            background: linear-gradient(135deg, var(--accent), var(--accent-soft));
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(236, 72, 153, 0.25);
        }
        .error { color: #fca5a5; margin-bottom: 12px; font-weight: 600; }
        .helper { color: var(--muted); font-size: 13px; margin-top: 8px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Accesso admin</h1>
    <p class="helper">Entra nella console di gestione della serata karaoke.</p>
    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>

        <label>
            <input type="checkbox" name="remember" value="1"> Ricordami
        </label>

        <button class="button" type="submit">Accedi</button>
    </form>
</div>
</body>
</html>
