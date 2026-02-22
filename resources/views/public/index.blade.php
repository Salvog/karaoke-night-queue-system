<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karaoke Night | Accesso pubblico</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-cyan: #49dcff;
            --accent-gold: #ffc659;
            --accent-pink: #ff4f9a;
            --surface: rgba(10, 16, 34, 0.84);
            --text: #f8fbff;
            --muted: rgba(225, 236, 255, 0.78);
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 18px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 16px;
            font-family: 'Manrope', 'Trebuchet MS', sans-serif;
            color: var(--text);
            background-image:
                radial-gradient(circle at 10% 18%, rgba(73, 220, 255, 0.24), transparent 35%),
                radial-gradient(circle at 86% 12%, rgba(255, 198, 89, 0.2), transparent 32%),
                radial-gradient(circle at 82% 78%, rgba(255, 79, 154, 0.18), transparent 34%),
                linear-gradient(130deg, #0a1024 0%, #101738 45%, #1b1230 100%);
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            width: 300px;
            height: 300px;
            border-radius: 999px;
            pointer-events: none;
            z-index: -1;
            filter: blur(2px);
        }

        body::before {
            top: -120px;
            left: -70px;
            background: radial-gradient(circle, rgba(73, 220, 255, 0.38), rgba(73, 220, 255, 0));
        }

        body::after {
            right: -110px;
            bottom: -110px;
            background: radial-gradient(circle, rgba(255, 79, 154, 0.34), rgba(255, 79, 154, 0));
        }

        .entry-shell {
            width: 100%;
            max-width: 620px;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: var(--radius-lg);
            box-shadow: 0 16px 32px rgba(5, 10, 22, 0.36);
            backdrop-filter: blur(7px);
            overflow: hidden;
        }

        .entry-header {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .entry-title {
            margin: 0;
            font-family: 'Bebas Neue', 'Impact', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 0.95;
            color: #def1ff;
            font-size: clamp(2rem, 7vw, 2.7rem);
        }

        .entry-subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.94rem;
            line-height: 1.45;
        }

        .entry-body {
            padding: 16px;
            display: grid;
            gap: 12px;
        }

        .label {
            margin: 0;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #d8ecff;
        }

        .code-input {
            width: 100%;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(5, 12, 26, 0.72);
            color: var(--text);
            font: inherit;
            padding: 11px 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            outline: none;
        }

        .code-input::placeholder {
            text-transform: none;
            letter-spacing: normal;
            color: rgba(225, 236, 255, 0.52);
        }

        .code-input:focus {
            border-color: rgba(73, 220, 255, 0.65);
            box-shadow: 0 0 0 3px rgba(73, 220, 255, 0.18);
        }

        .button {
            border: 0;
            border-radius: 999px;
            font: inherit;
            font-weight: 800;
            letter-spacing: 0.02em;
            cursor: pointer;
            padding: 10px 16px;
            line-height: 1;
            color: #042031;
            background: linear-gradient(90deg, var(--accent-cyan), #7be6ff);
            box-shadow: 0 8px 16px rgba(73, 220, 255, 0.3);
            justify-self: start;
        }

        .hint {
            margin: 0;
            color: #d4e9ff;
            font-size: 0.86rem;
            line-height: 1.45;
        }

        .hint-strong {
            color: #fff0cd;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            body {
                padding: 10px;
            }

            .entry-shell {
                border-radius: 14px;
            }

            .entry-header,
            .entry-body {
                padding: 12px;
            }

            .button {
                width: 100%;
                justify-self: stretch;
            }
        }
    </style>
</head>
<body>
<main class="entry-shell">
    <header class="entry-header">
        <h1 class="entry-title">Accesso pubblico</h1>
        <p class="entry-subtitle">Inserisci il codice evento per aprire subito la pagina di prenotazione brani.</p>
    </header>

    <section class="entry-body">
        <label class="label" for="event-code">Codice evento</label>
        <input id="event-code" class="code-input" type="text" placeholder="Es. EVENT1" autocomplete="off" inputmode="text" maxlength="12">
        <button class="button" type="button" id="join-button">Partecipa</button>
        <p class="hint"><span class="hint-strong">Nota:</span> se esiste una serata attiva, questa pagina reindirizza automaticamente all'evento in corso.</p>
    </section>
</main>

<script>
    const input = document.getElementById('event-code');
    const button = document.getElementById('join-button');

    const normalize = (value) => value.trim().toUpperCase();

    const navigateToJoin = () => {
        const code = normalize(input.value);

        if (!code) {
            input.focus();
            return;
        }

        window.location.href = `/e/${encodeURIComponent(code)}`;
    };

    button.addEventListener('click', navigateToJoin);

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            navigateToJoin();
        }
    });
</script>
</body>
</html>
