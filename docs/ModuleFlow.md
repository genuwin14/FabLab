# P3 — Module Flow

This document describes the end-to-end flow of every functional module in the Inventory Monitoring System v2. For each module: **entry point → actors → steps → side effects → exit**.

---

## 1. Authentication & Registration

### 1.1 Registration
**Entry**: `GET /register` → `AuthController@showRegisterForm`

1. User submits the registration form (email, password, fullname, contact, etc.).
2. Validator enforces:
   - Email domain is `@gmail.com` or `@my.cspc.edu.ph`.
   - Password contains upper, lower, number, special.
   - Contact number normalized to `+63…` format.
3. `users` row inserted with `role = customer`, `phone_verified = false`.
4. A 6-digit OTP is generated, stored on the user row, and dispatched via the local SMS gateway (MacroDroid).
5. User is redirected to the OTP verification page (`/verify-code`).

**Exit**: Once OTP is verified, user is logged in and redirected by role (see §1.3).

### 1.2 Login
**Entry**: `GET /login` → `AuthController@showLoginForm`

1. User submits credentials. Rate limiter: **3 attempts per 3 minutes** per email/IP.
2. On success, Sanctum issues a stateful cookie session.
3. Redirect determined by email domain (§1.3).

**Google OAuth path**: `GET /login/google` → Socialite → callback (`/login/google/callback`). If the user doesn't exist, the account is auto-created with role `customer`, email verified, and `phone_verified = true` — there is no phone step, and `contact_number` is left empty.

### 1.3 Post-login Role Routing

Routing is driven by the `users.role` column, not by the email address. Registration always writes `role = customer`; staff and admin accounts are provisioned directly (see `UserSeeder`).

| `users.role` | Redirect target |
| :--- | :--- |
| `admin` | `/admin/dashboard` |
| `staff` | `/staff/dashboard` |
| `customer` | `/customer/shop` |

`EnsureUserHasRole` (aliased as `role` in `bootstrap/app.php`) then gates each route group:

| Group | Middleware | Allowed |
| :--- | :--- | :--- |
| `/admin/*` | `role:admin` | Admins |
| `/staff/*` | `role:staff,admin` | Staff and admins — order fulfilment lives only here |
| `/customer/*` | `role:customer` | Customers |
| `/notifications/*`, `/verify-code` | none beyond `auth:sanctum` | Every signed-in user |

A wrong-role GET is redirected to the user's own landing page with an error message; anything else (state-changing verbs, JSON requests) gets a 403. Covered by `tests/Feature/RoleAccessTest.php`.

### 1.4 Password Reset
**Entry**: `GET /forgot-password`

1. User submits email → system checks the account exists.
2. A 6-digit code is generated, stored in the **session**, and emailed via `Mail::raw`.
3. User enters the code on `/forgot-password/verify` — verified against the session.
4. On success, user proceeds to `/reset-password` to set a new password.

### 1.5 Logout
- `POST /logout` invalidates the session and regenerates the CSRF token, then redirects to `/login`. Protected pages are unreachable afterwards because `auth:sanctum` rejects the request; there is no cache-busting middleware, so a back-button press may still render a page from the browser's own cache.

---

## 2. Customer Modules

### 2.1 Shop / Browse
**Route**: `GET /customer/shop` → `Customer\ShopController@index`

- Lists products (paginated), filtered by category / search query.
- Each product card exposes: add-to-cart, customize (if `is_customizable`), view detail.

### 2.2 Cart
**Routes**: `GET/POST /customer/cart/*` → `Customer\CartController`

| Action | Effect |
| :--- | :--- |
| `add` | Validates stock; if customization data is included, also `updateOrCreate`s a `custom_designs` row and recomputes price via `calculateCustomPrice()`. Stores item under a synthetic `cartKey` so the same product with different designs are separate lines. Pushes a `CustomDesignSubmitted` notification to staff/admins when a brand-new design is saved. |
| `update` | Adjusts quantity, re-checks stock. |
| `remove` | Removes a cart line. |
| `count` | Returns cart item count (used by navbar badge). |
| `checkout` | Wraps the next step in a DB transaction. |

> **Cart storage**: Session-based (`session('cart')`), not a database table. Persistence only happens at checkout.

### 2.3 Checkout
**Route**: `POST /customer/cart/checkout`

1. Customer selects which cart lines to check out.
2. Transaction begins.
3. Server re-validates stock for every selected line.
4. `orders` row is created (`status = pending`, unique `order_number = 'ORDR-…'`).
5. For each line, an `order_items` row is created (with `custom_design_id` if applicable) and `products.stock` is decremented.
6. Transaction commits.
7. A `NewOrderPlaced` notification is fanned out to all staff and admins (`Notifier::staffAndAdmins`).
8. Checked-out items are removed from session cart.

**Exit**: Redirect to `/customer/orders` with success flash.

### 2.4 Customization Studio
**Routes**: `GET /customer/customize`, `POST /customer/customize/save`, `DELETE /customer/customize/{id}`, `GET /customer/my-designs`

- Loads a product (must have `is_customizable = true`) plus its allowed textures via `product_textures`.
- User edits a "recipe" (text, shapes, logos, features such as `led_lighting`).
- Saving inserts/updates a `custom_designs` row (`recipe` JSON + Base64 `snapshot`).
- "My Designs" lists the customer's saved designs.
- Pricing formula (server authoritative): `basePrice + 50/text + 30/shape + 150/logo + 500 if led_lighting`.

### 2.5 Orders & Receipts
**Routes**: `GET /customer/orders`, `POST /customer/orders/{id}/cancel`, `GET /customer/orders/{id}/receipt`

- Lists the customer's orders with status.
- Cancel allowed only while `status ∈ {pending, approved}` (server enforces).
- Receipt view shows order line items + barcode (`picqer/php-barcode-generator`).

### 2.6 Profile & Settings
- `PUT /customer/profile` — update profile fields.
- `GET/PUT /customer/settings` — toggle `notifications_enabled`.

---

## 3. Staff Modules

> Staff has **operational** access — they can read most things but only edit a narrower set than Admin.

### 3.1 Dashboard
`GET /staff/dashboard` → snapshot of today's orders, low-stock items, recent purchase orders.

### 3.2 Orders (Operational)
**Routes**: `GET /staff/orders`, `POST /staff/orders/{id}/update-status`

Status progression handled by staff:
```
pending  →  approved  →  processing  →  ready_for_pickup  →  completed
                                                          ↘
                                                            cancelled (with reason)
```

### 3.3 Products / Raw Materials / Textures (read + edit)
- Index views: `GET /staff/{products|raw-materials|textures}`.
- Edit endpoints: `PUT /staff/{products|raw-materials|textures}/{id}`.
- Staff **cannot** create new items or delete (those routes only exist under Admin).

### 3.4 Inventory (read-only)
- `GET /staff/inventory` — consolidated view of on-hand, on-display, damaged, consumed across products / raw materials / textures.

### 3.5 Purchase Orders (full procurement)
**Routes**: `GET /staff/purchase`, `GET /staff/purchase/create`, `POST /staff/purchase`, `GET /staff/purchase/{id}`, `PUT /staff/purchase/{id}/status`

1. Staff selects a supplier and adds line items (any mix of product / raw material / texture).
2. PO is created (`po_number` generated, `status = draft`).
3. PO transitions: `draft → sent → confirmed → delivered → cancelled`.
4. On `delivered`, stock counters on the referenced product/raw_material/texture are incremented.

### 3.6 Sales (read-only)
`GET /staff/sales` — aggregate of completed orders.

### 3.7 Profile & Settings
`PUT /staff/profile`, `GET/PUT /staff/settings`.

---

## 4. Admin Modules

Admin has the full superset of Staff capabilities plus catalog management, user management, and system reports.

### 4.1 Dashboard
`GET /admin/dashboard` — KPIs: total sales, active customers, pending orders, low-stock counts, charts (Chart.js).

### 4.2 Catalog Management
| Resource | Routes |
| :--- | :--- |
| Categories | `GET/POST/PUT/DELETE /admin/categories[/{id}]` |
| Suppliers | `GET/POST/PUT/DELETE /admin/suppliers[/{id}]` |
| Products | `GET/POST/PUT/DELETE /admin/products[/{id}]` |
| Raw Materials | `GET/POST/PUT/DELETE /admin/raw-materials[/{id}]` |
| Textures | `GET/POST/PUT/DELETE /admin/textures[/{id}]` |
| Equipment | `GET/POST/PUT/DELETE /admin/equipment[/{id}]` |

### 4.3 Product Relationships
- `GET/POST /admin/products/{id}/suppliers` — attach suppliers with cost / MOQ / lead-time (writes to `product_suppliers`).
- `GET/POST /admin/products/{id}/textures` — attach allowed textures (writes to `product_textures`).

### 4.4 Inventory
- `GET /admin/inventory` — full view.
- `POST /admin/inventory/assign-supplier` — bulk-assign default supplier.

### 4.5 Purchase Orders
Same workflow as Staff under `/admin/purchase/*` — admins can also override status changes.

### 4.6 Orders (Review)
- `GET /admin/orders` — all orders, all statuses.
- `POST /admin/orders/{id}/review` — admin approval / rejection (with `reason`).

### 4.7 Sales
- `GET /admin/sales` — sales analytics + charts.

### 4.8 Reports
**Routes**: `GET /admin/reports/{materials|equipment}` plus `…/preview`, `…/pdf`, `…/docx`.

1. Admin opens the report page and filters by department / date range.
2. Preview renders as HTML.
3. Export buttons stream either:
   - **PDF** — server-rendered via `barryvdh/laravel-dompdf`.
   - **DOCX** — generated via `PHPOffice/PHPWord`.

### 4.9 User Management
- `GET /admin/users` — list users.
- `POST /admin/users/{id}/status` — enable / disable an account (toggles `users.status`).

### 4.10 Settings
`GET/PUT /admin/settings` — global system toggles (e.g. notification defaults).

---

## 5. Notifications (Cross-cutting)

**Routes** (all auth-protected):
| Route | Purpose |
| :--- | :--- |
| `GET /notifications` | Full notification page |
| `GET /notifications/poll` | Lightweight JSON poll for navbar badge / dropdown |
| `POST /notifications/read-all` | Mark all as read |
| `POST /notifications/{id}/read` | Mark one as read |
| `DELETE /notifications/{id}` | Delete |

**Producers**:
- `CustomDesignSubmitted` — fires from `CartController@add` when a brand-new design is saved.
- `NewOrderPlaced` — fires from `CartController@checkout` after commit.
- `Notifier::staffAndAdmins(...)` is the shared fan-out helper.

**Recipient filtering**: Users with `notifications_enabled = false` are excluded by the `Notifier`.

---

## 6. Phone OTP Verification

**Routes**: `GET/POST /verify-code`, `POST /verify-code/resend` → `Auth\OtpController`

1. After registration (or when re-verifying), system stores a 6-digit code on `users.phone_verification_code`.
2. Code is delivered via the local SMS gateway:
   - Laravel sends `GET http://{device-ip}:{port}/sms?to=…&msg=…`
   - MacroDroid on the device receives it and dispatches the SMS through the SIM.
3. User submits the code → checked against the stored value → on match, `phone_verified = true` and the verification code is cleared.
4. Resend: regenerates the code and re-dispatches via the same gateway.

---

## 7. Sequence Summaries (Happy Path)

### 7.1 Customer Purchase
```
Customer → Shop → Cart (add) → [optional: Customize → Save Design] →
Checkout → Order created (pending) → Notification fanned to Staff+Admin →
Staff: pending→approved→processing→ready_for_pickup→completed
```

### 7.2 Stock Replenishment
```
Admin/Staff → Purchase Orders → Create PO (draft) → Send →
Supplier confirms → Delivered → stock counters incremented →
PO marked completed
```

### 7.3 Reporting
```
Admin → Reports → Filter (department/date) → Preview →
Export (PDF via dompdf | DOCX via PHPWord) → file streamed to browser
```
