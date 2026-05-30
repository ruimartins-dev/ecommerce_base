# B2B Ministore — Project Foundation
A B2B mini-store technical challenge built on **Laravel 13 / PHP 8.4**, fully
containerized with Docker. This repository currently contains **only the
architecture and infrastructure foundation** — no business features (catalog,
products, orders, checkout, API endpoints) are implemented yet.
Everything runs inside containers. No local PHP, Composer, Node or MySQL is
required — only Docker.
---
## Tech stack
| Concern            | Technology                          |
|--------------------|-------------------------------------|
| Framework          | Laravel 13                          |
| Language           | PHP 8.4 (php-fpm)                    |
| Web server         | Nginx                               |
| Database           | MySQL 8                             |
| Cache / Queue / WS | Redis 7                             |
| Frontend           | Blade + Bootstrap 5 + Vite          |
| Realtime           | Laravel Reverb (websockets)         |
| API auth           | Laravel Sanctum                     |
| Tests              | PHPUnit (Laravel default)           |
---
## Requirements
- Docker Engine 24+
- Docker Compose v2
That is all. Composer, Node and the PHP toolchain live inside the images.
---
## Services
| Service     | Description                                   | Port (host) |
|-------------|-----------------------------------------------|-------------|
| `app`       | php-fpm application (artisan + composer)       | —           |
| `nginx`     | Serves the app                                 | 80          |
| `mysql`     | MySQL 8 database (persistent volume)           | 3306        |
| `redis`     | Cache / queue / broadcasting backend           | 6379        |
| `queue`     | Dedicated `queue:work` worker                  | —           |
| `scheduler` | Dedicated `schedule:work` runner               | —           |
| `reverb`    | Websocket server (`reverb:start`)              | 8080        |
| `node`      | Vite dev server (HMR)                          | 5173        |
All PHP services share a single image (`docker/php/Dockerfile`) for consistency.
---
## Quick start
```bash
# 1. Create your local env file (optional: compose has matching defaults)
cp .env.example .env
# 2. Build and start the whole stack
docker compose up -d --build
# 3. Run the database migrations
docker compose exec app php artisan migrate
# 4. (optional) Build front-end assets once
docker compose exec node npm run build
```
Open the app at: **http://localhost**
> On the very first boot the `app` container runs `composer install` and
> generates the application key automatically. The `queue`, `scheduler` and
> `reverb` containers wait until `app` is healthy before starting.
---
## Common commands
### Migrations
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh   # rebuild schema
```
The default migrations already include the **sessions**, **cache** and
**jobs** tables required for database sessions and queue support.
### Tests
```bash
docker compose exec app php artisan test
```
### Queue worker
A dedicated `queue` container runs automatically:
```bash
docker compose logs -f queue
```
Run an extra worker manually if needed:
```bash
docker compose exec app php artisan queue:work
```
`QUEUE_CONNECTION=redis`, so all jobs are processed through Redis.
### Scheduler
A dedicated `scheduler` container runs `php artisan schedule:work`
(no system cron required). Define scheduled tasks in `routes/console.php`.
```bash
docker compose logs -f scheduler
```
### Reverb (realtime)
A dedicated `reverb` container runs the websocket server on port `8080`:
```bash
docker compose logs -f reverb
```
Start it manually if needed:
```bash
docker compose exec app php artisan reverb:start
```
- `BROADCAST_CONNECTION=reverb`
- Server-side broadcasts reach Reverb at `reverb:8080` (internal network).
- The browser connects to `localhost:8080` (`VITE_REVERB_*` variables).
No events are implemented yet — the infrastructure is ready for them.
---
## Front-end (Bootstrap + Vite)
- Styles: `resources/sass/app.scss` (imports Bootstrap 5).
- Scripts: `resources/js/app.js` (imports Bootstrap JS + Laravel Echo).
- Layout: `resources/views/layouts/app.blade.php` (structure only).
During development the `node` container serves Vite with HMR:
```bash
docker compose logs -f node      # http://localhost:5173
```
For a production build:
```bash
docker compose exec node npm run build
```
---
## Project structure (architecture foundation)
```
app/
├── Actions/         # single-purpose action classes
├── DTOs/            # data transfer objects
├── Enums/           # enums
├── Events/          # domain/broadcast events
├── Exceptions/      # custom exceptions
├── Jobs/            # queued jobs
├── Policies/        # authorization policies
├── Repositories/    # data access abstraction
├── Services/        # business logic
├── Support/         # helpers / shared utilities
└── Http/
    ├── Controllers/
    │   ├── Admin/        # backoffice controllers
    │   ├── Customer/     # frontoffice controllers
    │   └── API/          # REST API controllers
    └── Requests/
        ├── Admin/
        ├── Customer/
        └── API/
```
These folders are intentionally empty (kept via `.gitkeep`) and ready for the
upcoming feature implementation.
---
## Architecture conventions
- Fat models, skinny controllers.
- Business logic lives in **Services** / **Actions**, not controllers.
- Validation via **Form Requests**.
- Authorization via **Policies**.
- Async work via **Events + Listeners** and **Jobs**.
- No logic in Blade; use dependency injection; strict typing where appropriate.
---
## Acceptance checklist
```bash
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app php artisan queue:work
docker compose exec app php artisan reverb:start
```
The app opens at http://localhost with no broken containers.
