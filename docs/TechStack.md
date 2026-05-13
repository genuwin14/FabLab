# P1 — Technology Stack

This document describes every framework, library, service, and tool used by the **Inventory Monitoring System v2**. It is intended as the single, authoritative reference for the project's technology choices.

---

## 1. Application Architecture

- **Pattern**: MVC (Model–View–Controller)
- **Role separation**: Folders are cloned per role (`Admin`, `Staff`, `Customer`) rather than sharing controllers/views — each role has its own controllers under `app/Http/Controllers/{Role}` and its own views under `resources/views/{role}`.
- **Auth-gated**: All role pages live behind `auth:sanctum` and the `CheckRole` middleware.

## 2. Backend

| Layer | Technology | Version |
| :--- | :--- | :--- |
| Language | PHP | ^8.2 |
| Framework | [Laravel](https://laravel.com/) | ^12.0 |
| ORM | Eloquent (Laravel built-in) | — |
| Auth (web + API) | [Laravel Sanctum](https://laravel.com/docs/sanctum) | ^4.0 |
| Social Login | [Laravel Socialite](https://laravel.com/docs/socialite) (Google) | ^5.24 |
| PDF Generation (server) | [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) | ^3.1 |
| DOCX Generation | [PHPOffice/PHPWord](https://github.com/PHPOffice/PHPWord) | ^1.4 |
| Barcode Generation | [picqer/php-barcode-generator](https://github.com/picqer/php-barcode-generator) | ^3.2 |
| REPL / Console | Laravel Tinker | ^2.10 |

## 3. Frontend

| Layer | Technology |
| :--- | :--- |
| Templating | Blade (Laravel native) |
| CSS Framework | Bootstrap 5 (CDN) + custom CSS |
| Icons | FontAwesome 6, Bootstrap Icons |
| JS Runtime | Vanilla JS + jQuery 3.6 |
| Charts | [Chart.js](https://www.chartjs.org/) |
| Modals & alerts | [SweetAlert2](https://sweetalert2.github.io/) |
| Client-side PDF | [jsPDF](https://github.com/parallax/jsPDF) + `jspdf-autotable` |
| Bundler | Vite |

## 4. Database

- **Engine**: MySQL (or MariaDB)
- **Layout**: Fully normalized (see [DatabaseSchema.md](DatabaseSchema.md))
- **Soft Deletes**: Enabled on `products`, `raw_materials`, `textures`, `equipment`
- **Notifications**: Uses Laravel's default `notifications` table (UUID primary key, polymorphic `notifiable`)

## 5. Authentication & Roles

- **Mechanism**: Laravel Sanctum (stateful SPA-style cookie sessions + optional API tokens)
- **Roles**: `admin`, `staff`, `customer` (stored as enum on `users.role`)
- **Default Role**: All new signups receive `customer` automatically
- **Email-domain routing**:
  - `@admin.com` → Admin Dashboard
  - `@staff.com` → Staff Dashboard
  - Anything else → Customer Shop
- **Middleware**: `CheckRole`, `RedirectIfAuthenticated`, `PreventBackHistory`
- **OTP/Phone verification**: 6-digit code delivered via local SMS gateway (see §6)
- **Rate Limiting**: 3 login attempts per 3-minute window per email/IP

## 6. Communication Services

### Email
- Laravel `Mail` facade over SMTP
- Used for password-reset codes and email-mode OTP verification

### SMS (Local Gateway)
- **Tool**: [MacroDroid](https://www.macrodroid.com/) running on an Android handset on the LAN
- **Mechanism**: The Laravel app sends an HTTP `GET` to the device's web-server endpoint (e.g. `http://192.168.0.114:8080/sms`). MacroDroid dispatches the actual SMS through the phone's SIM card.
- **Use case**: Registration OTP, phone re-verification

### In-App Notifications
- Built on Laravel's native `notifications` table
- Polled by the frontend (`/notifications/poll`) for real-time-ish updates
- Per-user opt-out via `users.notifications_enabled`

## 7. Server Environment

- **Web Server**: Apache or Nginx via XAMPP / Laragon (development) or comparable production stack
- **PHP**: 8.2+
- **Dependency Managers**: Composer (PHP), NPM (JS assets)
- **Queue**: `database` driver (default) for any async notification dispatch

## 8. Development Tools

| Purpose | Tool |
| :--- | :--- |
| Version Control | Git |
| Code Style | Laravel Pint |
| Tests | PHPUnit ^11.5 |
| Mocking | Mockery ^1.6 |
| Faker | fakerphp/faker ^1.23 |
| Tail logs | `laravel/pail` |
| Local dev runner | `composer dev` (concurrently runs `serve`, `queue:listen`, `pail`, `vite`) |

## 9. Conventions

- **PK naming**: Each domain table uses a domain-specific primary key (`product_id`, `supplier_id`, `order_id`, …) rather than the generic `id`. Pivot tables use plain `id`.
- **Foreign keys**: Always cascade on delete unless the parent is optional (e.g. `textures.supplier_id` uses `nullOnDelete`).
- **Money columns**: `decimal(12, 2)` for product/order totals; `decimal(10, 2)` for raw materials & textures.
- **Status enums**: Defined inline at the migration level for `users.status`, `users.role`, `orders.status`, `purchase_orders.status`.
- **Images**: Products store Base64 in a `longText` column; raw materials and textures store either a path or Base64.
