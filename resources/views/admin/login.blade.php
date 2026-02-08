<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accesso admin</title>
    <style>
        :root {
            --text: #f9fbff;
            --muted: #c3caef;
            --accent: #ff4fd8;
            --accent-cyan: #2ad8ff;
            --surface: rgba(15, 16, 44, 0.8);
            --border: rgba(255, 255, 255, 0.24);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: 'Poppins', 'Nunito Sans', 'Trebuchet MS', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 16% 20%, rgba(255, 79, 216, 0.22), transparent 35%),
                radial-gradient(circle at 86% 16%, rgba(42, 216, 255, 0.24), transparent 38%),
                linear-gradient(140deg, #150b2d, #0f1235 45%, #1f0f2c);
            padding: 20px;
        }

        .card {
            width: min(420px, 100%);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: var(--surface);
            box-shadow: 0 24px 40px rgba(8, 8, 24, 0.45);
            backdrop-filter: blur(8px);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 4px;
        }

        .subtitle {
            margin-top: 0;
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            font-weight: 700;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 14px;
            border-radius: 11px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(8, 10, 28, 0.75);
            color: var(--text);
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 3px rgba(42, 216, 255, 0.2);
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0 20px;
            color: var(--muted);
            font-size: 14px;
        }

        .checkbox input {
            margin: 0;
            width: auto;
        }

        .button {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(120deg, #ffd447, #fff4b8);
            color: #0a0a1f;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 14px;
            cursor: pointer;
            transition: transform 150ms ease, filter 150ms ease;
        }

        .button:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .error {
            color: #ffd5db;
            margin-bottom: 12px;
            border-radius: 10px;
            padding: 10px 12px;
            border: 1px solid rgba(255, 98, 134, 0.35);
            background: rgba(255, 98, 134, 0.16);
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Accesso admin</h1>
    <p class="subtitle">Gestisci la serata karaoke da una cabina di controllo unica.</p>
    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>

        <label class="checkbox">
            <input type="checkbox" name="remember" value="1"> Ricordami
        </label>

        <button class="button" type="submit">Accedi</button>
    </form>
</div>
</body>
</html>
