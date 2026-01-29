# Karaoke Night Queue System

Laravel-based modular monolith for managing karaoke night queues.

## Architecture
- Modules live under `app/Modules/{Auth,Events,Songs,Queue,PublicScreen,Theming}`.
- DTOs, Actions, and Services keep controllers thin.
- Admin audit logging is provided via `AdminAuditLogger` and `audit_logs` migration.
- Real-time broadcasting is abstracted by `RealtimeBroadcasterInterface` with a null implementation stub.

## Requirements
- PHP 8.2+
- Composer
- Postgres (preferred) or MySQL

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
- Health endpoint: `GET /health` returns `{ "status": "ok" }`.
