# Changelog

All notable changes to **FnB Cloud** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Domain layer (Actions / Services / DTOs)** — order business logic extracted
  into `CreateOrderAction`, `BuildOrderDataAction`, `OrderPricingService`,
  `VoucherService`, and `LoyaltyService`, with `CreateOrderData` / `CartItemData`
  DTOs. Web (Livewire) and the API now share one canonical order-creation path.
- **Sanctum API authentication** — per-device, revocable, scoped personal access
  tokens replace the legacy single-token guard. Default abilities: `orders:read`,
  `orders:write`, `catalog:read`.
- **Versioned REST API (`/api/v1`)** — thin controllers delegating to the domain
  layer: menu catalog (with `?since=` delta), order list/show/create. Server-side
  authoritative pricing — client-sent prices are never trusted.
- **Offline sync (idempotent, create-only)** — `GET /api/v1/sync/bootstrap` delta
  pull and `POST /api/v1/sync/orders` batch replay, deduplicated on a per-order
  `client_uuid`. New `client_uuid`, `synced_at`, and `source` columns on orders.
- **API documentation** — `docs/API.md` covering architecture, auth, endpoints,
  and the offline sync contract.

### Changed
- `Pos::checkout()` now delegates persistence to `CreateOrderAction`, shrinking the
  component by ~220 lines while preserving transaction and row-lock semantics.

### Removed
- Legacy `Api\OrderController` and `Api\MenuController` (superseded by `Api\V1`),
  eliminating the divergent duplicate order logic.

## [1.0.0] - 2026-06-10

Initial stable release. This version marks the baseline feature set of the
multi-tenant restaurant POS platform.

### Added
- **Multi-tenancy** — tenant-scoped data via `BelongsToTenant` trait and global
  scope, with tenant identification middleware.
- **Authentication & security** — Laravel Fortify with two-factor authentication.
- **Role-based access control** — permissions, roles, and `permission:` route
  middleware. Default landlord roles (Super Admin, Admin, Staff) and restaurant
  roles (Owner, Manager, Kitchen Staff, Waiter, Cashier).
- **Landlord (platform) side** — global stats dashboard, tenant management,
  audit logs, and system health monitoring.
- **Point of Sale (POS)** — menu grid with categories, cart with variants,
  add-ons, set/combo products, dine-in and takeaway, discounts, vouchers,
  loyalty redemption, split payments, hold/recall orders, pay-later, and
  inline customer lookup/registration.
- **Order management** — status filters, order detail, void orders with manager
  PIN authorization and audit trail, collect payment, and unshifted orders.
- **Kitchen Display System (KDS)** — real-time tickets with prep timers and
  status flow (pending → preparing → ready → served).
- **Menu management** — categories, products (variants, add-on groups, set
  products, badges, tile colors), and add-ons.
- **Shifts** — open/close with starting cash, cash movements, and variance.
- **Tables** — floor management with clear-table flow.
- **Loyalty program** — configurable points, redemption rules, promo
  multipliers, customers, and vouchers.
- **Reports** — sales report, cashier report, and sales analysis.
- **Settings** — tenant profile, receipt customization, tax config, loyalty
  settings, quick notes, role/user management, and manager PIN setup.
- **User Guide** — in-app sectioned documentation.

[Unreleased]: https://github.com/shenryuken/fnb-cloud/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/shenryuken/fnb-cloud/releases/tag/v1.0.0
