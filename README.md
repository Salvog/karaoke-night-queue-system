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
php artisan migrate
php artisan serve
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
- Public area routes: `routes/public.php` (mounted under `/public`).
- Public join flow: `routes/public-join.php` (landing at `/e/{eventCode}` plus activate/request POST endpoints).
- Public screen: `routes/public-screen.php` (screen at `/screen/{eventCode}`, SSE stream at `/screen/{eventCode}/stream`).
- Health endpoint: `GET /health` returns `{ "status": "ok" }`.

## Public join flow
- Landing (`GET /e/{eventCode}`) issues a device cookie and a join token (stored client-side).
- Optional PIN activation uses `event_nights.join_pin`.
- Song requests enforce per-participant cooldown via `event_nights.request_cooldown_seconds`.

## Public screen
- Screen (`GET /screen/{eventCode}`) shows now playing, next/recent queue, and theme/banner overlays.
- Real-time updates stream via SSE (`GET /screen/{eventCode}/stream`) when enabled; clients fall back to polling every 5 seconds.
- Configure counts and realtime settings in `config/public_screen.php` (set `PUBLIC_SCREEN_REALTIME_ENABLED=false` to disable SSE).
