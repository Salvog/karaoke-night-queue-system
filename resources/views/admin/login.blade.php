<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accesso admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #111827; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 32px; border-radius: 8px; width: 360px; box-shadow: 0 6px 18px rgba(0,0,0,0.2); }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input { width: 100%; padding: 8px; margin-bottom: 16px; border: 1px solid #d1d5db; border-radius: 4px; }
        .button { width: 100%; background: #2563eb; color: #fff; border: none; padding: 10px; border-radius: 4px; font-weight: 600; }
        .error { color: #b91c1c; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Accesso admin</h1>
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
