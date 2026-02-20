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
- GD extension (optional, recommended for full image-upload test coverage)

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

Notes:
- `DemoDataSeeder` runs only in `local` and `testing` environments.
- It is idempotent for core entities (admin users, venues, themes, banners, songs, events).
- It refreshes runtime data (participants, queue requests, playback state) for the seeded demo events.

## Environment variables
Key settings to review in `.env`:
- `APP_URL`: Base URL used for asset links.
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database connection.
- `SESSION_DRIVER`: Defaults to database-backed sessions.
- `CACHE_DRIVER`: Use a shared cache (e.g., Redis or Memcached) for multi-instance deployments; avoid `array` or local `file` drivers if you need cross-instance realtime state.
- `PUBLIC_SCREEN_REALTIME_ENABLED`: Toggle SSE updates for public screens.
- `PUBLIC_SCREEN_REALTIME_DISABLE_ON_CLI_SERVER`: Disable SSE automatically when using PHP built-in server.
- `PUBLIC_SCREEN_GLOBAL_BRAND_NAME`: Global manager/organization label shown on the public screen.
- `PUBLIC_SCREEN_GLOBAL_BRAND_LOGO`: Global manager/organization logo URL/path shown on the public screen.
- `PUBLIC_SCREEN_QR_SERVICE_URL`: QR generation endpoint used for the join QR (default: `https://api.qrserver.com/v1/create-qr-code/`).
- `PUBLIC_SCREEN_QR_SIZE`: Size (px) used for generated join QR images.
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

## Testing
- Prefer `php artisan test` in this project.
- The custom `test` command forces a safe test runtime (`APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).
- There is an additional hard guard in `tests/CreatesApplication.php`: tests abort if the DB is not `sqlite/:memory:` to prevent accidental data loss.
- If GD is not installed, image-upload tests in `AdminThemeAssetsTest` are skipped automatically.

## Routing
- Admin area routes: `routes/admin.php` (mounted under `/admin` with session auth middleware).
- Public area routes: `routes/public.php` (mounted under `/public`, includes the public landing at `/public`).
- Public join flow: `routes/public-join.php` (landing at `/e/{eventCode}` plus activate/request POST endpoints).
- Public screen: `routes/public-screen.php` (screen at `/screen/{eventCode}`, SSE stream at `/screen/{eventCode}/stream`).
- Admin queue live snapshot: `GET /admin/events/{eventNight}/queue/state` (auth required).
- Health endpoint: `GET /health` returns `{ "status": "ok" }`.

## Admin event management
- Create/edit event nights with venue, date/time, break/cooldown, optional PIN, and status (draft/active/closed).
- Event code is auto-generated and read-only in the admin form.
- Event creation defaults:
  - start datetime: current day at `19:00`
  - end datetime: next day at `02:00`
  - break between songs: `40` seconds
  - request cooldown input: `20` minutes (stored as `request_cooldown_seconds`)
- On larger viewports, event form fields are presented in a multi-column layout (at least two fields per row).
- Per-event theme configuration supports background image uploads, overlay texts, and ad banner CRUD from the Theme/Ads screen.

## Public join flow
- Landing (`GET /e/{eventCode}`) issues a device cookie and a join token (stored client-side).
- Optional PIN activation uses `event_nights.join_pin`.
- Song requests enforce per-participant cooldown via `event_nights.request_cooldown_seconds`.
- Cooldown messaging for participants is shown in minutes.
- Song search (`GET /e/{eventCode}/songs`) returns paginated JSON filtered by title/artist.
- ETA lookup (`GET /e/{eventCode}/eta`) returns JSON with estimated wait time before a new request starts.

## Public screen
- Screen (`GET /screen/{eventCode}`) shows now playing, compact next/recent queue lists, join details, join QR code, global manager branding, ticker, and sponsor cards.
- Real-time updates stream via SSE (`GET /screen/{eventCode}/stream`) when enabled; clients fall back to polling based on `config/public_screen.php` (`poll_seconds`).
- During the configured break window, playback exposes `playback.intermission.*` and the public UI shows a visible “stacco tecnico” countdown before the next song.
- Public screen requests (`/screen/{eventCode}`, `/screen/{eventCode}/state`, `/screen/{eventCode}/stream`) also trigger queue auto-advance checks.
- The top-left logo remains event-specific (`brand_logo_path`), while the right-side “regia karaoke” block uses global branding (`public_screen.global_brand.*`).
- Join QR is generated from `event.join_url` through the configured QR service (`public_screen.join_qr.*`).
- Configure queue counts/realtime and screen branding in `config/public_screen.php` (set `PUBLIC_SCREEN_REALTIME_ENABLED=false` to disable SSE).

## Queue automation
Playback advancement is request-driven (no scheduler required for normal operation):
- Public screen endpoints run auto-advance checks before returning state.
- Admin queue management uses `GET /admin/events/{eventNight}/queue/state` polling to keep playback/queue/history updated live.
- As long as at least one screen/admin client is active, the queue progresses automatically.

Optional fallback command (manual/maintenance/no active clients):
```bash
php artisan queue:advance
```

Optional scheduler wiring is still available in `app/Console/Kernel.php` if you want proactive background advancement even without connected clients.
