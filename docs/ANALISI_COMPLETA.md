# Analisi completa: funzionalità, bug e sicurezza

## Baseline eseguita
Comandi eseguiti:
- `composer install`
- `php artisan test`
- `composer run pint`
- `composer run stan`
- `composer audit`
- `gitleaks detect --no-banner` (opzionale)

Sintesi:
- Test applicativi: verdi.
- Pint: inizialmente applicava fix di stile non richiesti su molti file.
- PHPStan: crash per memory limit 128M.
- Composer audit: fallito per `HTTP 403` verso Packagist.
- Gitleaks: non installato nell’ambiente.

## Flussi funzionali verificabili

### Admin
- Login/logout admin su `/admin/login`.
- Gestione eventi (`/admin/events`).
- Gestione coda evento (`/admin/events/{id}/queue`).
- Gestione tema/ads (`/admin/events/{id}/theme-ads`).
- CRUD songs e venues.

### Public join
- Entry pubblica `/public`.
- Join evento `/e/{eventCode}`.
- Activate e request (`/e/{eventCode}/activate`, `/request`).
- Songs, ETA e my-requests.

### Public screen
- Schermo pubblico `/screen/{eventCode}`.
- Stato `/screen/{eventCode}/state`.
- Stream SSE `/screen/{eventCode}/stream`.
- Media `/media/{path}`.

## Bug/hardening implementati

### 1) Login admin senza throttling (brute-force)
1. Riproduzione: invii ripetuti a `POST /admin/login` con credenziali errate non avevano limite.
2. Test che falliva prima: `test_admin_login_is_rate_limited_after_too_many_attempts`.
3. Patch: rate limiter `admin-login` + middleware `throttle:admin-login` sulla route login.
4. Test green dopo patch: sesto tentativo -> HTTP 429.
5. Deploy note: nessuna migrazione; possibile aumento 429 per client malevoli.

### 2) Header di sicurezza mancanti
1. Riproduzione: risposta `/health` senza hardening header minimi.
2. Test che falliva prima: `SecurityHeadersTest`.
3. Patch: middleware `SecurityHeaders` aggiunto al gruppo `web`.
4. Test green dopo patch: header presenti.
5. Deploy note: compatibile con hosting condiviso, nessun downtime.

## OWASP quick check
- Input validation: presente in controller principali.
- Auth/admin: aggiunto throttling login.
- Session/cookie: `http_only` e `same_site=lax` già presenti; consigliato `SESSION_SECURE_COOKIE=true` in produzione HTTPS.
- CSRF: coperto dal middleware web Laravel.
- XSS: Blade escaping di default; evitare output raw non sanitizzato.
- SQLi: Eloquent/query builder (parametrizzazione).
- Upload: validazione `image` e size già presente.
- Path traversal: presenti controlli su path media/banner.
- Rate limiting: esistente su public join, esteso al login admin.

