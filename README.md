01# Mini Loja B2B — Laravel 13 Technical Challenge

A B2B e-commerce platform built with **Laravel 13**, delivered as a technical
challenge. It pairs an **admin backoffice** (catalog, products, customers,
orders) with a **customer frontoffice** (browse → cart → checkout → order
tracking), exposes a **token-authenticated REST API**, and processes
side-effects **asynchronously** over Redis queues with **realtime order
updates** pushed through Laravel Reverb.

The codebase favours a **service-oriented, event-driven architecture**: thin
controllers delegate to services, domain events fan out to queued listeners and
jobs, and authorization is centralised in policies. The whole stack is
**fully Dockerized**, so an evaluator only needs Docker to clone, boot and run
the project end-to-end.

---

## 1. Features Implemented

### Authentication & Authorization
- Session-based login / logout (Laravel Breeze scaffolding).
- Clear **admin vs. customer** separation enforced by a `role` middleware.
- Role middleware fed from a `RoleEnum` (no magic strings).
- Model **Policies** for every resource; admins bypass via a `Gate::before` hook.

### Backoffice (Admin)
- **Catalog management** — CRUD, activation toggle, product assignment.
- **Category management** — hierarchical (parent/children) CRUD.
- **Product management** — CRUD with activation toggle.
- **Customer management** — create/edit and block/unblock (no hard delete).
- **Address management** — CRUD scoped to customers.
- **Order management** — list, detail and status transitions.
- **Audit log** — read-only view over the asynchronously written audit trail.

### Frontoffice (Customer)
- Product **catalog** with **search** and **category filtering**.
- Product **detail** pages (slug-based routing).
- Session-based **cart** (add, update qty, remove, clear).
- **Checkout** workflow (cart review → address selection → order creation).
- **Order history** and **order detail** (own orders only).

### REST API
- **Sanctum** personal-access-token authentication.
- Full **CRUD** for catalogs, categories, products, customers and addresses.
- Request **validation** via Form Requests.
- **Filtering** (search, category, active, etc.) and **pagination**.

### Async Processing
- **Redis queues** with a dedicated worker container.
- Async order-status pipeline (event → listeners → queued jobs).
- Queued **customer notifications**.
- Queued **audit logging**.

### Realtime
- Live **order status updates** over websockets using **Laravel Reverb** and
  Laravel Echo, on a private per-order channel.

### Testing
- **Unit tests** (services, enums, helpers).
- **Feature tests** (admin, customer, auth, API, security, role access).
- **Async tests** (queued notifications & audit trail).
- **Realtime tests** (broadcast assertions).
- **Regression tests** (duplicate submission, order snapshot preservation).

---

## 2. Tech Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| Framework | **Laravel 13** (`^13.8`) | Application core |
| Language | **PHP 8.4** (requires `^8.3`) | Runs inside the PHP-FPM image |
| Database | **MySQL 8.0** | Persistent named volume + healthcheck |
| Cache / Queue / Broadcast backend | **Redis 7** | Single backend for cache, queues and Reverb scaling |
| API auth | **Laravel Sanctum** (`^4.0`) | Bearer personal access tokens |
| Realtime | **Laravel Reverb** (`^1.0`) | First-party websocket server |
| Frontend | **Blade + Bootstrap 5.3** | Compiled with **Vite** |
| Realtime client | **Laravel Echo** + **pusher-js** | Browser subscriptions |
| Containers | **Docker + Docker Compose** | Reproducible multi-service stack |
| Testing | **PHPUnit 12** | `php artisan test` |

---

## 3. Architecture Overview

The application is intentionally layered. Controllers stay thin and orchestrate;
business rules live in **Services**; **Form Requests** validate and authorize
input; **Policies** centralise authorization; **Events** broadcast that
something happened; **Listeners** and **Jobs** handle the consequences off the
request cycle; **API Resources** shape JSON output.

**Order status change pipeline (HTTP request stays fast):**

```txt
Controller
  → OrderStatusService            (validates + persists the transition)
    → OrderStatusChanged Event    (single source of truth for side-effects)
      ├─ HandleOrderStatusChanged Listener → NotifyCustomerOrderStatusChangedJob → Notification
      └─ RecordAuditLog Listener           → RecordAuditLogJob                    → AuditLog
```

**Realtime broadcast flow:**

```txt
Admin changes order status
  → OrderStatusChanged (ShouldBroadcast, queued)
    → Reverb (private channel: orders.{id})
      → Laravel Echo in the browser
        → Customer order page updates automatically (no refresh)
```

Queues run on **Redis** in a dedicated `queue` worker container; the
`scheduler` container runs `schedule:work`; the `reverb` container serves
websockets — so no work blocks the web request.

---

## 4. Project Structure

```txt
app/Services            Business logic (Cart, Checkout, OrderStatus, Product, Address)
app/Policies            Authorization rules per model (admins bypass via Gate::before)
app/Http/Requests       Form Requests: validation + authorization
app/Http/Resources      API Resources: JSON response shaping
app/Http/Controllers    Admin / Customer / API / Auth controllers (thin)
app/Events              Domain events (e.g. OrderStatusChanged — ShouldBroadcast)
app/Listeners           Sync listeners that dispatch queued jobs
app/Jobs                Queued work (notifications, audit logging)
app/Notifications       Customer order-status notification
routes                  web, auth, admin, customer, api, channels
resources/views         Blade templates (Bootstrap UI)
resources/js, sass      Echo bootstrap + Bootstrap assets (Vite)
database                Migrations, factories, seeders
docs/api.md             Full REST API reference
tests                   Unit + Feature (Async, Realtime, Regression, Security, ...)
```

---

## 5. Requirements

- **Docker**
- **Docker Compose**

> **No local PHP, Composer, Node or MySQL installation is required.**
> Everything runs inside Docker.

---

## 6. Installation & Setup

```bash
git clone <repository-url>
cd ecommerce_base

cp .env.example .env

docker compose up -d
```

The `app` container bootstraps itself on first boot: it installs Composer
dependencies, generates the application key and fixes permissions before the
`nginx`, `queue`, `scheduler` and `reverb` containers start (they wait on the
app's healthcheck). The `node` container installs npm packages and serves Vite
automatically.

Once the stack is healthy, prepare the database:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

> If you prefer to build assets once instead of using the Vite dev server:
>
> ```bash
> docker compose exec app npm run build
> ```

---

## 7. Running the Application

With the stack up, open:

```txt
http://localhost
```

| Service | URL / Port |
|---------|-----------|
| Web app (Nginx) | http://localhost |
| Vite dev server | http://localhost:5173 |
| Reverb websockets | localhost:8080 |
| MySQL | localhost:3306 |
| Redis | localhost:6379 |

All services run under Docker Compose (`app`, `nginx`, `mysql`, `redis`,
`queue`, `scheduler`, `reverb`, `node`).

---

## 8. Demo Credentials

Seeded by `migrate:fresh --seed`.

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@example.com` | `password` |
| Customer | `customer@example.com` | `password` |

The seeder also creates the demo customer (`Acme Industrial Supplies`) with
default/warehouse addresses, plus a handful of extra customers, catalogs,
categories and products.

---

## 9. Running Tests

```bash
docker compose exec app php artisan test
```

The suite covers:

- **Unit tests** — services, enums and helpers in isolation.
- **Feature tests** — admin & customer flows, auth, role access and API.
- **Async tests** — queued notifications and audit-trail writes.
- **Realtime tests** — `OrderStatusChanged` broadcast assertions.
- **Regression tests** — duplicate order submission and order snapshot
  preservation.

Run a focused subset, e.g. the API tests:

```bash
docker compose exec app php artisan test --filter=Api
```

---

## 10. REST API

The API is documented in full in [`docs/api.md`](docs/api.md). It uses Sanctum
personal access tokens, API Resources, Form Requests and Policies. All routes
are prefixed with `/api` and exchange JSON (always send
`Accept: application/json`).

### Authentication flow

1. `POST /api/login` → receive a bearer token.
2. Send `Authorization: Bearer <token>` on every protected request.
3. `POST /api/logout` revokes the current token.

```bash
# 1) Log in and obtain a token
curl -s -X POST http://localhost/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password","device_name":"cli"}'
```

```bash
# 2) Use the token
curl -s "http://localhost/api/products?search=phone&active=1&per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

### Resources

| Resource | Endpoints |
|----------|-----------|
| Catalogs | `GET/POST /api/catalogs`, `GET/PUT/PATCH/DELETE /api/catalogs/{id}` |
| Categories | `GET/POST /api/categories`, `GET/PUT/PATCH/DELETE /api/categories/{id}` |
| Products | `GET/POST /api/products`, `GET/PUT/PATCH/DELETE /api/products/{id}` |
| Customers | `GET/POST /api/customers`, `GET/PUT/PATCH/DELETE /api/customers/{id}` |
| Addresses | `GET/POST /api/addresses`, `GET/PUT/PATCH/DELETE /api/addresses/{id}` |

### Response behaviour

- **Validation** — `422` with a `message` and field-level `errors`.
- **Pagination** — collections return `data`, `links` and `meta`; control with
  `page` and `per_page`.
- **Filtering** — e.g. products support `search`, `sku`, `name`, `category`,
  `active`; non-admins only ever receive active products.
- **Authorization** — `401` when unauthenticated, `403` when forbidden.

See [`docs/api.md`](docs/api.md) for request/response examples per resource.

---

## 11. Queues & Async Processing

Order side-effects never block the admin's HTTP request. Status changes flow
through a single domain event into queued workers backed by **Redis**:

```txt
OrderStatusService
  → OrderStatusChanged Event
    → Listeners (HandleOrderStatusChanged, RecordAuditLog)
      → Queued Jobs (NotifyCustomerOrderStatusChangedJob, RecordAuditLogJob)
        → Notification + Audit log
```

A dedicated `queue` container runs the worker automatically. To work the queue
manually or manage failures:

```bash
docker compose exec app php artisan queue:work
docker compose exec app php artisan queue:failed
docker compose exec app php artisan queue:retry all
```

---

## 12. Realtime Updates

Realtime order tracking is powered by **Laravel Reverb** (websockets) and
**Laravel Echo** on the client.

**Flow:**

```txt
Customer opens their order page (subscribes to private channel orders.{id})
  → Admin changes the order status
    → OrderStatusChanged is broadcast (queued) via Reverb
      → Echo receives "order.status.changed"
        → The customer's UI updates automatically — no page refresh
```

The channel is private: subscription is only authorized for the owning customer
or an admin (see `routes/channels.php`), and the payload is intentionally
minimal (no prices, addresses or line items).

The `reverb` container starts the websocket server automatically. To run it
manually:

```bash
docker compose exec app php artisan reverb:start
```

---

## 13. Technical Decisions

- **Sanctum** — lightweight token auth ideal for first-party API integrations,
  without the overhead of a full OAuth2 server.
- **Redis queues** — keep web requests fast by deferring notifications and
  audit writes; Redis also backs cache and Reverb scaling, keeping the stack
  lean.
- **Reverb** — Laravel's first-party websocket server, so realtime needs no
  third-party broadcasting service and integrates natively with events.
- **Service-oriented architecture** — business rules live in services, keeping
  controllers thin and logic reusable and testable.
- **Order snapshots** — order items capture product price/details at purchase
  time, so historical orders stay accurate even when products later change.
- **Policies** — authorization is centralised per model with an admin bypass,
  shared by both web and API layers.
- **Form Requests** — validation and authorization are declared once and reused
  across controllers.

---

## 14. Testing Notes

- **Factories** generate realistic models for every test.
- **`RefreshDatabase`** keeps each test isolated against a clean schema.
- **`Queue::fake`** asserts jobs are dispatched without running workers.
- **`Notification::fake`** verifies customer notifications without sending them.
- **`Broadcast`/event fakes** assert `OrderStatusChanged` is broadcast on the
  correct private channel without a live Reverb server.

---

## 15. Known Limitations

- Payment processing is out of scope — checkout creates orders but does not
  integrate a payment gateway.
- Email is configured to the `log` driver locally; notifications are written to
  `storage/logs` rather than delivered.

---

## 16. Useful Commands

```bash
# Stack lifecycle
docker compose up -d
docker compose down

# Database
docker compose exec app php artisan migrate:fresh --seed

# Tests
docker compose exec app php artisan test

# Queues
docker compose exec app php artisan queue:work
docker compose exec app php artisan queue:retry all

# Realtime
docker compose exec app php artisan reverb:start

# Assets
docker compose exec app npm run build

# Optimize / clear caches
docker compose exec app php artisan optimize
docker compose exec app php artisan optimize:clear
```

---

## 17. Final Notes

This README reflects the **actual implemented project**: a Dockerized,
service-oriented Laravel 13 B2B store with a backoffice, a customer
frontoffice, a Sanctum REST API, Redis-backed async processing and Reverb
realtime updates, all covered by an automated test suite. Clone, `docker
compose up -d`, seed the database, and the application is ready to evaluate.

