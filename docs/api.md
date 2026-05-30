# REST API

Authenticated REST API for external integrations, built with **Laravel Sanctum**
(personal access tokens), **API Resources**, **Form Requests** and **Policies**.

All endpoints are prefixed with `/api`. Requests and responses use JSON; send
`Accept: application/json` so errors are always returned as JSON.

## Conventions

The API uses Laravel-native, consistent response shapes:

| Situation                  | HTTP | Body |
|----------------------------|------|------|
| Single resource            | 200/201 | `{ "data": { ... } }` |
| Collection (paginated)     | 200 | `{ "data": [ ... ], "links": { ... }, "meta": { ... } }` |
| Deleted                    | 204 | _(empty)_ |
| Unauthenticated            | 401 | `{ "message": "Unauthenticated." }` |
| Forbidden                  | 403 | `{ "message": "This action is unauthorized." }` |
| Not found                  | 404 | `{ "message": "..." }` |
| Validation failed          | 422 | `{ "message": "...", "errors": { "field": ["..."] } }` |

## Authentication flow

1. `POST /api/login` with credentials → receive a bearer token.
2. Send the token on every protected request: `Authorization: Bearer <token>`.
3. `POST /api/logout` revokes the token used for the current request.

### Login

`POST /api/login` (public, rate-limited 6/min)

```bash
curl -s -X POST http://localhost/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password","device_name":"postman"}'
```

`201 Created`

```json
{
  "data": {
    "token": "1|J9p...plaintext",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "is_active": true,
      "role": { "slug": "admin", "name": "Administrator" }
    }
  }
}
```

Invalid credentials or a disabled account return `422` with an `email` error.
Passwords and other secrets are never returned.

### Authenticated user

`GET /api/me`

```bash
curl -s http://localhost/api/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

### Logout

`POST /api/logout`

```bash
curl -s -X POST http://localhost/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

`200 OK` → `{ "message": "Logged out." }`

## Authorization model

Authorization is enforced by model **Policies** (admins bypass via a global
`Gate::before` hook):

| Resource   | Read (index/show)              | Write (create/update/delete) |
|------------|--------------------------------|------------------------------|
| Catalogs   | admin only                     | admin only |
| Categories | any authenticated user         | admin only |
| Products   | any authenticated user (active only for non-admins) | admin only |
| Customers  | admin (or own profile for show/update) | admin only (own profile for update) |
| Addresses  | owner or admin                 | owner or admin |

A non-admin caller hitting an admin-only operation receives `403`.

## Pagination

All collection endpoints are paginated. Control the page and page size with the
`page` and `per_page` query parameters; the response always carries `links`
(`first`, `last`, `prev`, `next`) and `meta` (`current_page`, `per_page`,
`total`):

```bash
curl -s "http://localhost/api/products?page=2&per_page=15" \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"
```

## Endpoints

| Method & path | Description |
|---------------|-------------|
| `GET /api/catalogs` | List catalogs (paginated) |
| `POST /api/catalogs` | Create a catalog |
| `GET /api/catalogs/{id}` | Show a catalog |
| `PUT/PATCH /api/catalogs/{id}` | Update a catalog |
| `DELETE /api/catalogs/{id}` | Delete a catalog |
| `GET /api/categories` | List categories (paginated). Filters: `search`, `active`, `roots` |
| `POST /api/categories` | Create a category |
| `GET /api/categories/{id}` | Show a category (with parent & children) |
| `PUT/PATCH /api/categories/{id}` | Update a category |
| `DELETE /api/categories/{id}` | Delete a category |
| `GET /api/products` | List products (paginated). Filters: `search`, `sku`, `name`, `category`, `active`, `per_page` |
| `POST /api/products` | Create a product |
| `GET /api/products/{id}` | Show a product |
| `PUT/PATCH /api/products/{id}` | Update a product |
| `DELETE /api/products/{id}` | Delete a product |
| `GET /api/customers` | List customers (paginated, admin). Filters: `search`, `blocked` |
| `POST /api/customers` | Create a customer |
| `GET /api/customers/{id}` | Show a customer |
| `PUT/PATCH /api/customers/{id}` | Update a customer |
| `DELETE /api/customers/{id}` | Delete a customer |
| `GET /api/addresses` | List addresses (paginated, scoped to caller). Admin filter: `customer` |
| `POST /api/addresses` | Create an address |
| `GET /api/addresses/{id}` | Show an address |
| `PUT/PATCH /api/addresses/{id}` | Update an address |
| `DELETE /api/addresses/{id}` | Delete an address |

## Products

### List with filters

```bash
curl -s "http://localhost/api/products?search=phone&active=1&per_page=20" \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"
```

```json
{
  "data": [
    {
      "id": 1,
      "sku": "ABC123",
      "slug": "smart-phone",
      "name": "Smart Phone",
      "price": 499.9,
      "stock": 12,
      "low_stock_threshold": 5,
      "is_active": true,
      "is_out_of_stock": false,
      "is_low_stock": false,
      "image_url": null,
      "categories": [],
      "catalogs": []
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 20, "total": 37 }
}
```

Available filters: `search` (name/sku/description), `sku`, `name`,
`category` (id or slug), `active` (admin only), `per_page`.
Non-admins only ever receive **active** products; inactive products return
`404` on direct access.

### Create

```bash
curl -s -X POST http://localhost/api/products \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -d '{
        "sku": "API-SKU-001",
        "name": "API Widget",
        "description": "Created through the API.",
        "price": 49.90,
        "stock": 30,
        "low_stock_threshold": 5,
        "is_active": true,
        "categories": [1],
        "catalogs": [1]
      }'
```

`201 Created` → `{ "data": { ... } }`

### Validation example

Negative price/stock are rejected:

```json
{
  "message": "The price field must be at least 0. (and 1 more error)",
  "errors": {
    "price": ["The price cannot be negative."],
    "stock": ["The stock cannot be negative."]
  }
}
```

## Categories

Hierarchy is supported: `show` returns `parent` and `children`. Circular parent
assignment is rejected on update:

```json
{
  "message": "You cannot move a category under one of its own descendants.",
  "errors": {
    "parent_id": ["You cannot move a category under one of its own descendants."]
  }
}
```

## Addresses

Ownership is enforced: a customer only sees and mutates their own addresses, and
`customer_id` is forced to the caller's own profile (it cannot be spoofed).

```bash
curl -s -X POST http://localhost/api/addresses \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -d '{
        "recipient_name": "Jane Doe",
        "address_line_1": "1 Main St",
        "postal_code": "12345",
        "city": "Lisbon",
        "country": "Portugal",
        "is_default": true
      }'
```

## Verifying

```bash
docker compose exec app php artisan route:list --path=api
docker compose exec app php artisan test --filter=Api
docker compose exec app php artisan optimize:clear
```

