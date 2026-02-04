<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Pubblico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #0f172a; color: #f8fafc; }
        header { padding: 24px; background: #111827; }
        main { padding: 24px; max-width: 720px; margin: 0 auto; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        .card { background: #1f2937; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input[type="text"] { padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; width: 100%; }
        .actions { display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap; }
        .button { background: #38bdf8; color: #0f172a; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .button.secondary { background: #64748b; color: #f8fafc; }
        .hint { color: #cbd5f5; font-size: 14px; margin-top: 8px; }
    </style>
</head>
<body>
<header>
    <h1>Accesso pubblico</h1>
    <div>Inserisci il codice evento per partecipare o aprire lo schermo pubblico.</div>
</header>
<main>
    <div class="card">
        <label for="event-code">Codice evento</label>
        <input id="event-code" type="text" placeholder="es. EVENT1" autocomplete="off">
        <div class="actions">
            <button class="button" type="button" data-target="join">Partecipa</button>
            <button class="button secondary" type="button" data-target="screen">Schermo pubblico</button>
        </div>
        <div class="hint">Partecipa apre la pagina di richiesta. Lo schermo pubblico e' pensato per una visualizzazione condivisa.</div>
    </div>
</main>
<script>
    const input = document.getElementById('event-code');

    const normalize = (value) => value.trim();

    const navigate = (target) => {
        const code = normalize(input.value);
        if (!code) {
            input.focus();
            return;
        }
        const base = target === 'screen' ? '/screen/' : '/e/';
        window.location.href = `${base}${encodeURIComponent(code)}`;
    };

    document.querySelectorAll('button[data-target]').forEach((button) => {
        button.addEventListener('click', () => navigate(button.dataset.target));
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            navigate('join');
        }
    });
</script>
</body>
</html>
