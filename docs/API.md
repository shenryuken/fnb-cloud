# FnB Cloud API & Architecture Guide

> **Version:** v2.x · **Base URL:** `https://<your-domain>/api`
> Versioned, Sanctum-authenticated REST API consumed by native/offline POS clients.

---

## 1. Architecture Overview

v2 introduces an **Actions / Services / DTOs** layer so business logic lives in one
place and is shared by both the Livewire web app and the REST API. Controllers and
Livewire components stay thin — they collect input and delegate.

```
                    ┌──────────────────────────┐
  Livewire (web) ──▶│                          │
                    │   Actions (commands)     │──▶ Services (calculations)
  API V1 (native) ─▶│   - CreateOrderAction    │    - OrderPricingService
                    │   - BuildOrderDataAction │    - VoucherService
  Sync (offline) ──▶│                          │    - LoyaltyService
                    └──────────────────────────┘
                                 │
                                 ▼
                          DTOs ──▶ Eloquent Models (tenant-scoped)
```

### Layer responsibilities

| Layer | Location | Responsibility |
|-------|----------|----------------|
| **DTOs** | `app/DTOs` | Immutable, framework-agnostic input objects (`CreateOrderData`, `CartItemData`). |
| **Actions** | `app/Actions` | Single-purpose commands that orchestrate a unit of work in a DB transaction. |
| **Services** | `app/Services` | Reusable, side-effect-free calculations (pricing, voucher validation, loyalty). |
| **Controllers** | `app/Http/Controllers/Api/V1` | HTTP translation only — validate, call an Action, return a Resource. |
| **Resources** | `app/Http/Resources` | Shape JSON responses (`OrderResource`). |
| **Form Requests** | `app/Http/Requests/Api/V1` | Validate and authorize API input. |

### Why this matters
Previously, order creation logic lived only inside `Pos::checkout()`, and the API had a
**simplified, divergent copy** that ignored discounts, vouchers, loyalty and tax. Now
both call `CreateOrderAction`, so web and API behavior can never drift apart.

---

## 2. Authentication (Laravel Sanctum)

The API uses **Sanctum personal access tokens** — per-device, revocable, and scoped.

### Login

```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "cashier@store.com",
  "password": "secret",
  "device_name": "iPad-POS-01"
}
```

**Response `200`:**
```json
{
  "token": "1|abcdef...",
  "token_type": "Bearer",
  "user": { "id": 12, "name": "Cashier", "email": "cashier@store.com", "tenant_id": 3 }
}
```

Send the token on every authenticated request:

```http
Authorization: Bearer 1|abcdef...
Accept: application/json
```

### Logout (revokes the current token)

```http
POST /api/v1/logout
Authorization: Bearer <token>
```

### Token abilities (scopes)
Tokens are issued with abilities so a device can be limited to what it needs:

| Ability | Grants |
|---------|--------|
| `orders:read` | List / view orders |
| `orders:write` | Create orders, sync drafts |
| `catalog:read` | Read menu / bootstrap |

---

## 3. Multi-Tenancy

Every authenticated request is scoped to the token owner's `tenant_id`. The
`TenantIdentification` middleware resolves the tenant from the Sanctum user (it falls
back to the `sanctum` guard when a bearer token is present), and the global
`TenantScope` filters all tenant-owned models automatically. **Clients never send a
tenant id** — it is derived from the token.

---

## 4. Endpoints

All V1 routes are prefixed with `/api/v1` and (except login/register) require
`auth:sanctum`.

### Catalog

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/menu` | Active categories → products → variants + addons. Supports `?since=<ISO8601>` delta. |

### Orders

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/orders` | Paginated list. Filters: `status`, `shift_id`, `per_page` (max 100). |
| `GET` | `/orders/{order}` | Single order with items, addons, components, customer. |
| `POST` | `/orders` | Create an order. Idempotent on `client_uuid`. |

#### `POST /api/v1/orders`

```json
{
  "client_uuid": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "order_type": "dine_in",
  "customer_id": 8,
  "table_id": 4,
  "items": [
    {
      "product_id": 21,
      "variant_id": 55,
      "quantity": 2,
      "addon_ids": [3, 7],
      "notes": "No onion"
    }
  ],
  "discount_type": "percent",
  "discount_value": 10,
  "voucher_code": "WELCOME10",
  "points_redeemed": 100,
  "payment_method": "cash",
  "amount_received": 50.00
}
```

> **Pricing is always recomputed server-side.** The client may send line items but the
> server resolves real product/variant/addon prices, applies discounts, vouchers,
> loyalty and tax via `BuildOrderDataAction` → `OrderPricingService`. Client-sent
> prices are ignored. Returns `201` with the created order, or `422` with a message on
> a domain error (invalid voucher, insufficient points, etc.).

---

## 5. Offline Sync

Offline clients capture order **drafts** locally (each with a generated `client_uuid`),
then replay them when connectivity returns. Sync is **idempotent and create-only** —
offline devices never edit shared records, so no merge conflicts are possible.

### Bootstrap (pull reference data)

```http
GET /api/v1/sync/bootstrap?since=2026-05-20T10:00:00Z
Authorization: Bearer <token>
```

Returns `categories` (with products/variants/addons), `customers`, and `taxes` changed
since the timestamp, plus a server `synced_at`. Store `synced_at` and pass it as `since`
next time to pull only deltas.

### Push order drafts

```http
POST /api/v1/sync/orders
Authorization: Bearer <token>

{
  "orders": [
    { "client_uuid": "f47ac10b-...", "items": [ { "product_id": 21, "quantity": 1 } ], "payment_method": "cash" }
  ]
}
```

**Response:**
```json
{
  "summary": { "synced": 1, "duplicate": 0, "failed": 0 },
  "results": [
    { "client_uuid": "f47ac10b-...", "status": "synced", "order_id": 1024 }
  ],
  "synced_at": "2026-05-22T12:34:56Z"
}
```

Each draft resolves to one of: `synced` (created), `duplicate` (already persisted — safe
retry), or `failed` (with a message). A single bad draft never aborts the batch.

### Recommended client sync loop
1. On reconnect, `POST /sync/orders` with all locally-queued drafts.
2. Remove drafts returned as `synced` **or** `duplicate` from the local queue.
3. Keep `failed` drafts for inspection / manual resolution.
4. `GET /sync/bootstrap?since=<last_synced_at>` to refresh catalog & customers.

---

## 6. Error Format

| Status | Meaning |
|--------|---------|
| `401` | Missing/invalid token |
| `403` | Authenticated but not allowed (ability/tenant) |
| `404` | Resource not found (or not in your tenant) |
| `422` | Validation or domain rule failure (`{ "message": "..." }` or `{ "errors": {...} }`) |

---

## 7. Versioning

The API is versioned by URL prefix (`/api/v1`). Breaking changes ship under a new
prefix (`/api/v2`) while the previous version keeps working, so native clients in the
field never break on deploy.
