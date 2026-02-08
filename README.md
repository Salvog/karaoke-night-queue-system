# Karaoke Night Queue System

Laravel-based modular monolith for managing karaoke night queues.

## Architecture
- Modules live under `app/Modules/{Admin,Auth,PublicJoin,PublicScreen,Queue}`.
- DTOs, Actions, and Services keep controllers thin.
- Admin audit logging is provided via `AdminAuditLogger` and `audit_logs` migration.
- Real-time broadcasting is abstracted by `RealtimeBroadcasterInterface` with a null implementation stub.

## Requirements
- PHP 8.2+
- Composer
- MySQL (default) or Postgres

## Local setup
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

## Demo data
The default seeder creates a realistic dataset for UI/UX validation and includes admin credentials:
- Admin: `admin@example.com` / `password`
- Staff: `staff@example.com` / `password`

You can re-run the demo data at any time:
```bash
php artisan db:seed --class=DemoDataSeeder
```

## Environment variables
Key settings to review in `.env`:
- `APP_URL`: Base URL used for asset links.
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database connection.
- `SESSION_DRIVER`: Defaults to database-backed sessions.
- `CACHE_DRIVER`: Use a shared cache (e.g., Redis or Memcached) for multi-instance deployments; avoid `array` or local `file` drivers if you need cross-instance realtime state.
- `PUBLIC_SCREEN_REALTIME_ENABLED`: Toggle SSE updates for public screens.
- `PUBLIC_JOIN_RATE_LIMIT_IP`, `PUBLIC_JOIN_RATE_LIMIT_PARTICIPANT`, `PUBLIC_JOIN_RATE_LIMIT_DECAY`: Public join rate limits.

## UI screenshots
For automated UI screenshots with Playwright in this environment, prefer Firefox because Chromium headless can crash due to missing system services.
Example (run your web server first):
```bash
python - <<'PY'
import asyncio
from playwright.async_api import async_playwright

async def main():
    async with async_playwright() as p:
        browser = await p.firefox.launch()
        page = await browser.new_page(viewport={"width": 1280, "height": 720})
        await page.goto("http://127.0.0.1:8000/screen/EVENT1", wait_until="networkidle")
        await page.screenshot(path="public-screen.png", full_page=True)
        await browser.close()

asyncio.run(main())
PY
```

## Verifica BE/FE
Per preparare l'ambiente di verifica end-to-end:
1. Avvia il progetto con le istruzioni di **Local setup**.
2. Accedi all'area admin con le credenziali demo (`admin@example.com` / `password`).
3. Verifica il flusso FE (creazione evento, gestione coda, landing pubblica) e il flusso BE (test automatici e comandi CI).

Comandi consigliati per la verifica backend:
```bash
php artisan test
composer run stan
composer run pint
```

## Docker
```bash
docker compose up --build
```

## CI-friendly commands
```bash
composer install --no-interaction --prefer-dist
php artisan migrate --force
php artisan test
composer run stan
composer run pint
```

## Routing
- Admin area routes: `routes/admin.php` (mounted under `/admin` with session auth middleware).
- Public area routes: `routes/public.php` (mounted under `/public`, includes the public landing at `/public`).
- Public join flow: `routes/public-join.php` (landing at `/e/{eventCode}` plus activate/request POST endpoints).
- Public screen: `routes/public-screen.php` (screen at `/screen/{eventCode}`, SSE stream at `/screen/{eventCode}/stream`).
- Health endpoint: `GET /health` returns `{ "status": "ok" }`.

## Admin event management
- Create/edit event nights with venue, date/time, break seconds, request cooldown in minutes, optional PIN, and status (draft/active/closed).
- Per-event theme configuration supports background image uploads, overlay texts, and ad banner CRUD from the Theme/Ads screen.

## Public join flow
- Landing (`GET /e/{eventCode}`) issues a device cookie and a join token (stored client-side).
- Optional PIN activation uses `event_nights.join_pin`.
- Song requests enforce per-participant cooldown via `event_nights.request_cooldown_seconds` (configured in minutes in the admin UI).
- Song search (`GET /e/{eventCode}/songs`) returns paginated JSON filtered by title/artist.
- ETA lookup (`GET /e/{eventCode}/eta`) returns JSON with estimated wait time before a new request starts.

## Public screen
- Screen (`GET /screen/{eventCode}`) shows now playing, next/recent queue, and theme/banner overlays.
- Real-time updates stream via SSE (`GET /screen/{eventCode}/stream`) when enabled; clients fall back to polling every 5 seconds.
- Configure counts and realtime settings in `config/public_screen.php` (set `PUBLIC_SCREEN_REALTIME_ENABLED=false` to disable SSE).

## Queue automation
Use the queue engine command to auto-advance playback (schedule it with cron or Laravel scheduler):
```bash
php artisan queue:advance
```

### Scheduler runtime
The scheduler must run continuously so `queue:advance` executes every five seconds (configured in `app/Console/Kernel.php`).

**Traditional hosts (cron):**
```bash
* * * * * php /path/to/artisan schedule:run >> /var/log/laravel-scheduler.log 2>&1
```

**Containerized hosts (long-running worker):**
```bash
php artisan schedule:work
```

You can also supervise `php artisan queue:advance` directly with an equivalent cadence if you prefer.

**Runtime verification:**
- Check your application logs for entries indicating the scheduler is running and `queue:advance` is being executed.
- Confirm active events advance by verifying `QueueEngine::advanceIfNeeded` is called for active event nights.
