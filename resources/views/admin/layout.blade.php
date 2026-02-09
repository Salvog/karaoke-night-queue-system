<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amministrazione | Karaoke Night</title>
    <style>
        :root {
            --bg-start: #150b2d;
            --bg-mid: #0f1235;
            --bg-end: #1f0f2c;
            --surface: rgba(18, 19, 44, 0.78);
            --surface-strong: rgba(14, 15, 34, 0.92);
            --surface-soft: rgba(250, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.16);
            --text: #f9f9ff;
            --muted: #b5b9dd;
            --accent: #ff4fd8;
            --accent-cyan: #2ad8ff;
            --accent-gold: #ffd447;
            --success: #33df9d;
            --danger: #ff6286;
            --shadow: 0 20px 40px rgba(8, 8, 24, 0.4);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: 'Poppins', 'Nunito Sans', 'Trebuchet MS', sans-serif;
            background:
                url("{{ asset('images/admin/karaoke-duo.svg') }}") 97% 78% / 186px 244px no-repeat,
                url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAxNjAgMTYwJz4KICA8ZGVmcz4KICAgIDxyYWRpYWxHcmFkaWVudCBpZD0naGFsbycgY3g9JzUwJScgY3k9JzQ0JScgcj0nNTglJz4KICAgICAgPHN0b3Agb2Zmc2V0PScwJScgc3RvcC1jb2xvcj0nI2ZmZmZmZicgc3RvcC1vcGFjaXR5PScwLjExJy8+CiAgICAgIDxzdG9wIG9mZnNldD0nNzAlJyBzdG9wLWNvbG9yPScjYjhjN2VhJyBzdG9wLW9wYWNpdHk9JzAuMDUnLz4KICAgICAgPHN0b3Agb2Zmc2V0PScxMDAlJyBzdG9wLWNvbG9yPScjN2Q4Y2FmJyBzdG9wLW9wYWNpdHk9JzAnLz4KICAgIDwvcmFkaWFsR3JhZGllbnQ+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9J2JvZHknIHgxPScwJScgeTE9JzAlJyB4Mj0nMTAwJScgeTI9JzEwMCUnPgogICAgICA8c3RvcCBvZmZzZXQ9JzAlJyBzdG9wLWNvbG9yPScjZjJmNmZmJyBzdG9wLW9wYWNpdHk9JzAuMjgnLz4KICAgICAgPHN0b3Agb2Zmc2V0PSc1NSUnIHN0b3AtY29sb3I9JyM5YWE5Y2EnIHN0b3Atb3BhY2l0eT0nMC4yMicvPgogICAgICA8c3RvcCBvZmZzZXQ9JzEwMCUnIHN0b3AtY29sb3I9JyM0YTU3NzknIHN0b3Atb3BhY2l0eT0nMC4xOScvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0nbWVzaCcgeDE9JzAlJyB5MT0nMCUnIHgyPScwJScgeTI9JzEwMCUnPgogICAgICA8c3RvcCBvZmZzZXQ9JzAlJyBzdG9wLWNvbG9yPScjZjdmYmZmJyBzdG9wLW9wYWNpdHk9JzAuMjQnLz4KICAgICAgPHN0b3Agb2Zmc2V0PScxMDAlJyBzdG9wLWNvbG9yPScjODY5NWJhJyBzdG9wLW9wYWNpdHk9JzAuMTUnLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9J3N0ZW0nIHgxPScwJScgeTE9JzAlJyB4Mj0nMTAwJScgeTI9JzEwMCUnPgogICAgICA8c3RvcCBvZmZzZXQ9JzAlJyBzdG9wLWNvbG9yPScjZWNmMmZmJyBzdG9wLW9wYWNpdHk9JzAuMjQnLz4KICAgICAgPHN0b3Agb2Zmc2V0PScxMDAlJyBzdG9wLWNvbG9yPScjN2U4ZmI1JyBzdG9wLW9wYWNpdHk9JzAuMTgnLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxjaXJjbGUgY3g9JzgwJyBjeT0nODAnIHI9JzcyJyBmaWxsPSd1cmwoI2hhbG8pJy8+CiAgPGcgdHJhbnNmb3JtPSdyb3RhdGUoLTIyIDgwIDgwKSc+CiAgICA8cmVjdCB4PSc0OScgeT0nMjQnIHdpZHRoPSc2MicgaGVpZ2h0PSc1Nicgcng9JzI4JyBmaWxsPSd1cmwoI2JvZHkpJyBzdHJva2U9JyNmNGY4ZmYnIHN0cm9rZS1vcGFjaXR5PScwLjI2JyBzdHJva2Utd2lkdGg9JzIuNCcvPgogICAgPHJlY3QgeD0nNTUnIHk9JzMxJyB3aWR0aD0nNTAnIGhlaWdodD0nNDInIHJ4PScyMCcgZmlsbD0ndXJsKCNtZXNoKScgc3Ryb2tlPScjZDRkZWVmJyBzdHJva2Utb3BhY2l0eT0nMC4yJyBzdHJva2Utd2lkdGg9JzEuNicvPgogICAgPHBhdGggZD0nTTYwIDM5aDQwTTU4IDQ2aDQ0TTU3IDUzaDQ2TTU4IDYwaDQ0TTYwIDY3aDQwJyBzdHJva2U9JyNmNWY5ZmYnIHN0cm9rZS1vcGFjaXR5PScwLjIyJyBzdHJva2Utd2lkdGg9JzEuOCcgc3Ryb2tlLWxpbmVjYXA9J3JvdW5kJy8+CiAgICA8cGF0aCBkPSdNNjQgODhjMCAxMSA3IDE4IDE2IDE4czE2LTcgMTYtMTgnIGZpbGw9J25vbmUnIHN0cm9rZT0nI2RjZTZmOCcgc3Ryb2tlLW9wYWNpdHk9JzAuMjMnIHN0cm9rZS13aWR0aD0nNCcgc3Ryb2tlLWxpbmVjYXA9J3JvdW5kJy8+CiAgICA8cGF0aCBkPSdNODAgODB2MzEnIHN0cm9rZT0ndXJsKCNzdGVtKScgc3Ryb2tlLXdpZHRoPSc3JyBzdHJva2UtbGluZWNhcD0ncm91bmQnLz4KICAgIDxwYXRoIGQ9J004MCAxMTF2MTEnIHN0cm9rZT0nI2RiZTVmNycgc3Ryb2tlLW9wYWNpdHk9JzAuMjEnIHN0cm9rZS13aWR0aD0nNCcgc3Ryb2tlLWxpbmVjYXA9J3JvdW5kJy8+CiAgICA8cmVjdCB4PSc2MicgeT0nMTIxJyB3aWR0aD0nMzYnIGhlaWdodD0nNycgcng9JzMuNScgZmlsbD0nI2RjZTZmOCcgZmlsbC1vcGFjaXR5PScwLjE4Jy8+CiAgPC9nPgo8L3N2Zz4K") 7% 84% / 196px 196px no-repeat,
                url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAxNjAgMTYwJz4KICA8ZGVmcz4KICAgIDxyYWRpYWxHcmFkaWVudCBpZD0naGFsbycgY3g9JzUwJScgY3k9JzQ0JScgcj0nNTglJz4KICAgICAgPHN0b3Agb2Zmc2V0PScwJScgc3RvcC1jb2xvcj0nI2ZmZmZmZicgc3RvcC1vcGFjaXR5PScwLjEnLz4KICAgICAgPHN0b3Agb2Zmc2V0PSc3MCUnIHN0b3AtY29sb3I9JyNmMGQ3YTcnIHN0b3Atb3BhY2l0eT0nMC4wNScvPgogICAgICA8c3RvcCBvZmZzZXQ9JzEwMCUnIHN0b3AtY29sb3I9JyNhODg3NTYnIHN0b3Atb3BhY2l0eT0nMCcvPgogICAgPC9yYWRpYWxHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0nYm9keScgeDE9JzAlJyB5MT0nMCUnIHgyPScxMDAlJyB5Mj0nMTAwJSc+CiAgICAgIDxzdG9wIG9mZnNldD0nMCUnIHN0b3AtY29sb3I9JyNmZmY1ZGYnIHN0b3Atb3BhY2l0eT0nMC4yNycvPgogICAgICA8c3RvcCBvZmZzZXQ9JzU1JScgc3RvcC1jb2xvcj0nI2QxYjY4YScgc3RvcC1vcGFjaXR5PScwLjIxJy8+CiAgICAgIDxzdG9wIG9mZnNldD0nMTAwJScgc3RvcC1jb2xvcj0nIzdmNjQ0MScgc3RvcC1vcGFjaXR5PScwLjE4Jy8+CiAgICA8L2xpbmVhckdyYWRpZW50PgogICAgPGxpbmVhckdyYWRpZW50IGlkPSdtZXNoJyB4MT0nMCUnIHkxPScwJScgeDI9JzAlJyB5Mj0nMTAwJSc+CiAgICAgIDxzdG9wIG9mZnNldD0nMCUnIHN0b3AtY29sb3I9JyNmZmY5ZWYnIHN0b3Atb3BhY2l0eT0nMC4yMicvPgogICAgICA8c3RvcCBvZmZzZXQ9JzEwMCUnIHN0b3AtY29sb3I9JyNiZWExNzMnIHN0b3Atb3BhY2l0eT0nMC4xNCcvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0nc3RlbScgeDE9JzAlJyB5MT0nMCUnIHgyPScxMDAlJyB5Mj0nMTAwJSc+CiAgICAgIDxzdG9wIG9mZnNldD0nMCUnIHN0b3AtY29sb3I9JyNmZmY2ZTEnIHN0b3Atb3BhY2l0eT0nMC4yMycvPgogICAgICA8c3RvcCBvZmZzZXQ9JzEwMCUnIHN0b3AtY29sb3I9JyNiMzhmNjAnIHN0b3Atb3BhY2l0eT0nMC4xNycvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICA8L2RlZnM+CiAgPGNpcmNsZSBjeD0nODAnIGN5PSc4MCcgcj0nNzInIGZpbGw9J3VybCgjaGFsbyknLz4KICA8ZyB0cmFuc2Zvcm09J3JvdGF0ZSgyNCA4MCA4MCknPgogICAgPHJlY3QgeD0nNDknIHk9JzI0JyB3aWR0aD0nNjInIGhlaWdodD0nNTYnIHJ4PScyOCcgZmlsbD0ndXJsKCNib2R5KScgc3Ryb2tlPScjZmZmN2U4JyBzdHJva2Utb3BhY2l0eT0nMC4yNCcgc3Ryb2tlLXdpZHRoPScyLjQnLz4KICAgIDxyZWN0IHg9JzU1JyB5PSczMScgd2lkdGg9JzUwJyBoZWlnaHQ9JzQyJyByeD0nMjAnIGZpbGw9J3VybCgjbWVzaCknIHN0cm9rZT0nI2Y0ZGZiZicgc3Ryb2tlLW9wYWNpdHk9JzAuMicgc3Ryb2tlLXdpZHRoPScxLjYnLz4KICAgIDxwYXRoIGQ9J002MCAzOWg0ME01OCA0Nmg0NE01NyA1M2g0Nk01OCA2MGg0NE02MCA2N2g0MCcgc3Ryb2tlPScjZmZmOGVkJyBzdHJva2Utb3BhY2l0eT0nMC4yJyBzdHJva2Utd2lkdGg9JzEuOCcgc3Ryb2tlLWxpbmVjYXA9J3JvdW5kJy8+CiAgICA8cGF0aCBkPSdNNjQgODhjMCAxMSA3IDE4IDE2IDE4czE2LTcgMTYtMTgnIGZpbGw9J25vbmUnIHN0cm9rZT0nI2ZmZWFjZCcgc3Ryb2tlLW9wYWNpdHk9JzAuMicgc3Ryb2tlLXdpZHRoPSc0JyBzdHJva2UtbGluZWNhcD0ncm91bmQnLz4KICAgIDxwYXRoIGQ9J004MCA4MHYzMScgc3Ryb2tlPSd1cmwoI3N0ZW0pJyBzdHJva2Utd2lkdGg9JzcnIHN0cm9rZS1saW5lY2FwPSdyb3VuZCcvPgogICAgPHBhdGggZD0nTTgwIDExMXYxMScgc3Ryb2tlPScjZmZlOGM3JyBzdHJva2Utb3BhY2l0eT0nMC4xOCcgc3Ryb2tlLXdpZHRoPSc0JyBzdHJva2UtbGluZWNhcD0ncm91bmQnLz4KICAgIDxyZWN0IHg9JzYyJyB5PScxMjEnIHdpZHRoPSczNicgaGVpZ2h0PSc3JyByeD0nMy41JyBmaWxsPScjZmZlNWMwJyBmaWxsLW9wYWNpdHk9JzAuMTYnLz4KICA8L2c+Cjwvc3ZnPgo=") 93% 16% / 170px 170px no-repeat,
                radial-gradient(circle at 8% 84%, rgba(169, 196, 255, 0.12), transparent 24%),
                radial-gradient(circle at 92% 16%, rgba(255, 227, 170, 0.1), transparent 22%),
                radial-gradient(circle at 93% 73%, rgba(132, 182, 255, 0.1), transparent 28%),
                radial-gradient(circle at 12% 18%, rgba(255, 79, 216, 0.18), transparent 35%),
                radial-gradient(circle at 84% 22%, rgba(42, 216, 255, 0.2), transparent 38%),
                radial-gradient(circle at 60% 75%, rgba(255, 212, 71, 0.1), transparent 44%),
                linear-gradient(140deg, var(--bg-start), var(--bg-mid) 45%, var(--bg-end));
            background-attachment: fixed;
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 999px;
            pointer-events: none;
            z-index: -1;
        }

        body::before {
            width: 340px;
            height: 340px;
            top: -120px;
            right: -80px;
            background: radial-gradient(circle, rgba(255, 79, 216, 0.4), rgba(255, 79, 216, 0));
        }

        body::after {
            width: 300px;
            height: 300px;
            bottom: -110px;
            left: -90px;
            background: radial-gradient(circle, rgba(42, 216, 255, 0.35), rgba(42, 216, 255, 0));
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            background: linear-gradient(90deg, rgba(15, 12, 35, 0.88), rgba(16, 17, 50, 0.8));
        }

        .topbar-inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 20px;
            font-weight: 700;
            line-height: 1.15;
        }

        .brand small {
            display: block;
            color: var(--muted);
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 4px;
            align-items: center;
            flex-wrap: wrap;
            padding: 0;
        }

        .nav-links a {
            position: relative;
            color: #d7ddea;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.01em;
            padding: 10px 14px 11px;
            border-radius: 2px;
            background: transparent;
            transition: color 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 5px;
            height: 2px;
            background: transparent;
            transition: background-color 150ms ease, transform 150ms ease;
            transform: scaleX(0.4);
            transform-origin: center;
        }

        .nav-links a:hover {
            color: #f2f5fb;
            background: rgba(176, 186, 206, 0.1);
        }

        .nav-links a:hover::after {
            background: rgba(205, 214, 230, 0.58);
            transform: scaleX(1);
        }

        .nav-links a.active {
            /*background: rgba(199, 207, 222, 0.14);
            box-shadow: inset 0 0 0 1px rgba(218, 225, 237, 0.32);*/
            color: #ffffff;
        }

        .nav-links a.active::after {
            background: rgba(235, 241, 251, 0.92);
            transform: scaleX(1);
        }

        .logout-form {
            margin: 0;
        }

        .logout-icon {
            border: 0;
            background: transparent;
            color: #ff5e79;
            padding: 2px;
            line-height: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 150ms ease, color 150ms ease, filter 150ms ease;
        }

        .logout-icon:hover {
            color: #ff7f96;
            filter: drop-shadow(0 0 8px rgba(255, 94, 121, 0.42));
            transform: translateY(-1px);
        }

        .logout-icon svg {
            width: 28px;
            height: 28px;
            display: block;
        }

        main {
            max-width: 1240px;
            margin: 0 auto;
            padding: 22px 18px 34px;
        }

        .status,
        .error-box {
            margin-bottom: 16px;
            border-radius: 12px;
            padding: 12px 14px;
            font-weight: 600;
            border: 1px solid;
        }

        .status {
            color: #c8fff0;
            border-color: rgba(51, 223, 157, 0.35);
            background: rgba(51, 223, 157, 0.14);
        }

        .error-box {
            color: #ffd5db;
            border-color: rgba(255, 98, 134, 0.35);
            background: rgba(255, 98, 134, 0.14);
        }

        .error-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 18px;
            border-radius: 18px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
        }

        h1, h2, h3 {
            margin-top: 0;
            color: #ffffff;
            letter-spacing: 0.01em;
        }

        h1 {
            font-size: clamp(1.5rem, 1.9vw, 2rem);
        }

        p {
            color: var(--muted);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 14px;
            overflow: hidden;
            background: var(--surface-strong);
            border: 1px solid var(--border);
        }

        th,
        td {
            padding: 9px 11px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.09);
            text-align: left;
            vertical-align: top;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        th {
            background: rgba(255, 255, 255, 0.06);
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 700;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .button {
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 7px 13px;
            font-size: 14px;
            font-weight: 700;
            color: #0a0a1f;
            background: linear-gradient(120deg, var(--accent-gold), #fff4b8);
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
        }

        .button:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .button.secondary {
            color: #edf3ff;
            background: linear-gradient(130deg, rgba(42, 216, 255, 0.3), rgba(95, 104, 255, 0.34));
            border-color: rgba(42, 216, 255, 0.45);
        }

        .button.success {
            color: #04240f;
            background: linear-gradient(130deg, #50f0b5, #9ff2cc);
        }

        .button.danger {
            color: #3c0412;
            background: linear-gradient(130deg, #ff8aa5, #ffc2cd);
        }

        .button[disabled] {
            opacity: 0.45;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .pill,
        .chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .panel {
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .panel.muted {
            background: rgba(255, 255, 255, 0.05);
            color: var(--muted);
        }

        .panel-row {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .value {
            font-weight: 700;
            color: #fff;
            font-size: 1.03rem;
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .grid.two {
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        }

        .grid.three {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .muted {
            color: var(--muted);
        }

        .helper {
            font-size: 13px;
            color: var(--muted);
            margin-top: 6px;
        }

        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            margin: 16px 0;
        }

        .form-grid {
            display: grid;
            gap: 12px;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        input[type="email"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            max-width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 11px;
            padding: 9px 11px;
            color: #f6f8ff;
            background: rgba(6, 8, 27, 0.72);
            font-size: 15px;
            box-sizing: border-box;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: rgba(42, 216, 255, 0.8);
            box-shadow: 0 0 0 3px rgba(42, 216, 255, 0.2);
        }

        input[readonly] {
            color: #fbe9ff;
            border-color: rgba(255, 79, 216, 0.35);
            background: rgba(255, 79, 216, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        @media (max-width: 760px) {
            main {
                padding: 20px 14px 30px;
            }

            .card {
                border-radius: 14px;
                padding: 16px;
            }

            .topbar-inner {
                padding: 14px 14px;
            }

            .brand {
                font-size: 18px;
            }

            .nav-links a {
                font-size: 15px;
                padding: 8px 12px;
            }

            .logout-icon svg {
                width: 26px;
                height: 26px;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand">
            Karaoke Control Room
            <small>Admin Experience</small>
        </div>
        <nav class="nav-links">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Panoramica</a>
            <a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}" @if(request()->routeIs('admin.events.*')) aria-current="page" @endif>Eventi</a>
            <a href="{{ route('admin.songs.index') }}" class="{{ request()->routeIs('admin.songs.*') ? 'active' : '' }}" @if(request()->routeIs('admin.songs.*')) aria-current="page" @endif>Canzoni</a>
            <a href="{{ route('admin.venues.index') }}" class="{{ request()->routeIs('admin.venues.*') ? 'active' : '' }}" @if(request()->routeIs('admin.venues.*')) aria-current="page" @endif>Location</a>
            @isset($eventNight)
                <a href="{{ route('admin.queue.show', $eventNight) }}" class="{{ request()->routeIs('admin.queue.*') ? 'active' : '' }}" @if(request()->routeIs('admin.queue.*')) aria-current="page" @endif>Coda</a>
                <a href="{{ route('admin.theme.show', $eventNight) }}" class="{{ request()->routeIs('admin.theme.*') ? 'active' : '' }}" @if(request()->routeIs('admin.theme.*')) aria-current="page" @endif>Tema/Annunci</a>
            @endisset
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
            @csrf
            <button class="logout-icon" type="submit" aria-label="Esci" title="Esci">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M10.09 15.59 11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59ZM19 3H7a2 2 0 0 0-2 2v3h2V5h12v14H7v-3H5v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/>
                </svg>
            </button>
        </form>
    </div>
</header>
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
    @if (trim($__env->yieldContent('without_content_card')))
        @yield('content')
    @else
        <div class="card">
            @yield('content')
        </div>
    @endif
</main>
</body>
</html>
