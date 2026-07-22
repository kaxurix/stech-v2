# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

S-Tech ("Soedirman Technophoria") is a competition registration platform: participants register a team, upload payment proof, get verified by an admin, and (once verified) submit their project. Backend is Laravel 13 (PHP 8.3), frontend is Vue 3 rendered through Inertia.js (no separate SPA/API — controllers return Inertia responses directly), styled with Tailwind CSS 4. Many user-facing strings (validation messages, status labels) are in Indonesian — match that convention when adding participant-facing text.

## Commands

Local dev (serve + queue worker + logs + vite, all concurrently):
```
composer dev
```

Individual pieces:
```
php artisan serve
npm run dev          # vite dev server
npm run build         # vite production build
```

Tests (PHPUnit, sqlite in-memory DB, config cache cleared first):
```
composer test
php artisan test
php artisan test --filter=TestName
php artisan test tests/Feature/SomeTest.php
```

Migrations:
```
php artisan migrate
php artisan migrate:fresh --seed
```

Docker (production-style stack: PHP-FPM app + Nginx + MySQL):
```
docker-compose up -d
```
The app container mounts `.env` and `storage` from the host and shares `public/build` + `vendor` with Nginx via the `stech-assets` named volume — after any composer/npm build change, that shared volume needs refreshing for Nginx to serve the new assets.

## Architecture

**Request flow**: `routes/web.php` → Controller → Inertia::render('PageName', [...props]) → matching component in `resources/js/Pages/`. There is no JSON API layer for the frontend; all data reaches Vue components as Inertia props.

**Auth & roles**: Standard Laravel session auth (`Auth::attempt`/`Auth::login`). A single `users.role` column (`peserta` | `admin`) distinguishes participants from admins — checked via `User::isAdmin()`. The `admin` route-middleware alias (`AdminMiddleware`, registered in `bootstrap/app.php`) guards `/admin/*`. Guests are redirected to `/` (`redirectGuestsTo('/')` in `bootstrap/app.php`), not a login page — the login form lives on the Welcome page itself.

**Core domain model** (one registration per user):
- `User` — hasOne `Registration`
- `Registration` — belongsTo `User`, hasOne `Payment`, hasOne `Submission`. Drives the whole workflow via its `status` field: `pending_payment` → `pending_verification` → `verified` (or `rejected`). `statusLabel()`/`statusColor()` on the model are the source of truth for how status renders in the UI — update both together.
- `Payment` — proof-of-payment file metadata; the actual file lives on the `local` disk (not public) under `payments/`, so it's only reachable through the admin-only `PaymentController::download`/`view` routes, never a direct public URL.
- `Submission` — project submission (title/github/drive/description); `SubmissionController::upload` only accepts submissions when `Registration.status === 'verified'`.

**Inertia shared props** (`app/Http/Middleware/HandleInertiaRequests.php`): every page gets `auth.user` (id/name/email/role or null), `flash` (type/message), and `errors`. Flash messages are set via `->with('flash', ['type' => ..., 'message' => ...])` from controllers — check this shape when adding new flash messages instead of Laravel's default `session('success')` convention (both patterns currently coexist in the controllers, prefer the `flash` shape for new code).

**Dead code, don't extend**: `DemoController` and the `DemoAuth` middleware implement a fake session-based login (`demo_logged_in` flag) used for an early stakeholder demo. They are not wired into `routes/web.php` or `bootstrap/app.php` — the real flow goes through `AuthController`/`AdminMiddleware`. `NgrokWarningMiddleware` (appended globally in `bootstrap/app.php`) is only relevant when tunneling local dev through ngrok.

**Deployment**: `docker-compose.yml` defines `stech-app` (PHP-FPM), `stech-web` (Nginx, config in `docker/nginx/`), and `stech-db` (MySQL 8). Nginx and the app share static assets through the `stech-assets` named volume rather than a bind mount.
