# Implementation Plan

A phase-by-phase checklist for delivering the **Inventory Monitoring System v2** documentation and the underlying system milestones. Tick items as they're completed.

> **How to read this**: Each phase has a documentation deliverable (in `/docs`) and the system milestone it describes. Both must be done before the phase is "complete".

> **Engineering work still outstanding** — access control, data-safety fixes, and the remaining features — is tracked separately in [RemainingWork.md](RemainingWork.md).

---

## Phase 1 — Technology Stack

**Doc deliverable**: [TechStack.md](TechStack.md)
**Goal**: Lock in every framework, library, service, and dev tool.

- [x] List backend stack (Laravel 12, PHP 8.2, Sanctum, Socialite, DomPDF, PHPWord, picqer/barcode).
- [x] List frontend stack (Blade, Bootstrap 5, jQuery, Chart.js, SweetAlert2, jsPDF, Vite).
- [x] Document database engine and ORM (MySQL/MariaDB + Eloquent).
- [x] Document authentication (Sanctum, Socialite, CheckRole middleware).
- [x] Document SMS gateway (MacroDroid local Android gateway).
- [x] Document server environment + composer/npm tooling.
- [x] Document conventions (PK naming, money columns, status enums, soft deletes).

---

## Phase 2 — Database Schema

**Doc deliverable**: [DatabaseSchema.md](DatabaseSchema.md)
**Goal**: Define a fully normalized layout — no denormalized JSON columns where a relation belongs.

### 2.1 Documentation
- [x] ER diagram (ASCII).
- [x] Core tables (`users`, `categories`, `suppliers`, `products`, `raw_materials`, `textures`, `equipment`).
- [x] Pivot tables (`product_suppliers`, `product_raw_materials`, `product_textures`).
- [x] Transactional tables (`orders`, `order_items`, `purchase_orders`, `purchase_order_items`, `custom_designs`).
- [x] System tables (`notifications`, `personal_access_tokens`, `sessions`, `password_reset_tokens`).
- [x] Normalization rationale.

### 2.2 Database build-out
- [x] Migrations created for every table above.
- [x] Foreign keys & cascade behavior verified.
- [x] Soft deletes on `products`, `raw_materials`, `textures`, `equipment`.
- [x] Inventory counters (`units_on_display / sponsored / damaged / consumed`) added to stock-bearing entities.
- [x] `notifications` table (UUID PK, polymorphic `notifiable`) added.
- [x] `notifications_enabled` flag added to `users`.

---

## Phase 3 — Module Flow

**Doc deliverable**: [ModuleFlow.md](ModuleFlow.md)
**Goal**: Document the runtime behavior of every module — entry point, actors, steps, side effects.

### 3.1 Authentication
- [x] Registration flow (validation, OTP, gateway dispatch).
- [x] Login flow (rate limiting, role routing).
- [x] Google Socialite flow.
- [x] Password reset flow (session-stored code).
- [x] OTP verification flow.

### 3.2 Customer Modules
- [x] Shop & browse.
- [x] Cart (add / update / remove / count) — session-backed.
- [x] Checkout — transactional, stock validation, fanout notification.
- [x] Customization studio + pricing formula.
- [x] Orders, cancel, receipt (with barcode).
- [x] Profile & settings.

### 3.3 Staff Modules
- [x] Dashboard.
- [x] Orders + status workflow.
- [x] Products / raw materials / textures (read + edit only).
- [x] Inventory read-only consolidation.
- [x] Purchase Orders full procurement flow.
- [x] Profile & settings.

### 3.4 Admin Modules
- [x] Dashboard with KPIs.
- [x] Catalog management (categories, suppliers, products, raw materials, textures, equipment) — full CRUD.
- [x] Product ↔ Supplier and Product ↔ Texture assignment.
- [x] Inventory management + assign default supplier.
- [x] Order review (approve / reject with reason).
- [x] Sales analytics.
- [x] Reports (Materials & Equipment) with HTML preview + PDF + DOCX export.
- [x] User management (enable / disable).
- [x] Settings.

### 3.5 Cross-cutting
- [x] Notifications (poll, mark-read, fanout via `Notifier::staffAndAdmins`).
- [x] OTP & phone verification module.

---

## Phase 4 — User Guides

**Doc deliverable**: [UserGuide/](UserGuide/) folder
**Goal**: Per-role end-user documentation covering every UI feature.

### 4.1 Customer Guide
- [x] [CustomerUserGuide.md](UserGuide/CustomerUserGuide.md)
- [x] Sign-up + OTP verification walkthrough.
- [x] Browse + customize + cart + checkout walkthrough.
- [x] My Designs management.
- [x] Order tracking + receipt + cancellation rules.
- [x] Notifications, profile, settings, logout.
- [x] FAQ.

### 4.2 Staff Guide
- [x] [StaffUserGuide.md](UserGuide/StaffUserGuide.md)
- [x] Login + dashboard tour.
- [x] Orders operational workflow.
- [x] Products / raw materials / textures (edit-only) walkthrough.
- [x] Inventory + PO creation, status, delivery.
- [x] Notifications, profile, settings, logout.
- [x] "What you cannot do" section.

### 4.3 Admin Guide
- [x] [AdminUserGuide.md](UserGuide/AdminUserGuide.md)
- [x] Dashboard + KPIs.
- [x] Full catalog CRUD walkthrough.
- [x] Inventory + PO override.
- [x] Order review (approve / reject).
- [x] Sales analytics.
- [x] Reports (Materials & Equipment) export walkthrough.
- [x] User management + Settings.
- [x] Best-practices section.

---

## Phase 5 — Verification & Polish

**Goal**: Make sure the docs match reality and the system is shippable.

- [ ] Walk every route in [ModuleFlow.md](ModuleFlow.md) against `routes/web.php` and confirm coverage.
- [ ] Cross-check every table in [DatabaseSchema.md](DatabaseSchema.md) against `database/migrations/*` (column names, types, FKs).
- [ ] Verify every "Customer can…" claim in [CustomerUserGuide.md](UserGuide/CustomerUserGuide.md) against the running app.
- [ ] Same verification pass for Staff and Admin guides.
- [ ] Smoke-test: register → verify OTP → add to cart → checkout → staff approves → staff completes.
- [ ] Smoke-test: admin creates product → assigns supplier + texture → staff creates PO → marks delivered → stock increments.
- [ ] Smoke-test: admin exports Materials Report as PDF and DOCX.
- [ ] Update guides if behavior diverges from documentation.

---

## Phase 6 — Maintenance

**Goal**: Keep the docs living, not stale.

- [ ] When a new migration is added → update [DatabaseSchema.md](DatabaseSchema.md).
- [ ] When a new route group is added → update [ModuleFlow.md](ModuleFlow.md) and the relevant user guide.
- [ ] When a library is bumped or replaced → update [TechStack.md](TechStack.md).
- [ ] Tag the docs with the system version on each release.
