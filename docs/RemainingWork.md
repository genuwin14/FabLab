# Remaining Work

What's left before the system is safe to put in front of real users. Everything here was verified against the code on 2026-08-04 — the modules themselves are built and work end to end; this is the gap between "demo runs" and "shippable".

**Out of scope by decision:** payment integration. Money continues to move outside the system, with staff recording a `payment_reference` when an order enters production.

Priorities: **P0** blocks release · **P1** correctness and data safety · **P2** features and UX · **P3** tech debt.

---

## P0 — Access control ✅ Done

The system had no authorization at all: every route sat behind `auth:sanctum` alone, so a signed-in customer could open every admin and staff page, approve their own order, disable an admin account, and delete catalog items.

- [x] **Add a role middleware.** `app/Http/Middleware/EnsureUserHasRole.php`, aliased as `role` in `bootstrap/app.php`.
- [x] **Group the routes by role.** `/admin/*` behind `role:admin`, `/staff/*` behind `role:staff,admin`, `/customer/*` behind `role:customer`.
- [x] **Decide the failure response.** A wrong-role GET redirects to the user's own landing page with an error message; state-changing verbs and JSON requests get a 403.
- [x] **Check the shared routes.** `/notifications/*` and `/verify-code` stay open to every signed-in user.
- [x] **Cover it with tests.** `tests/Feature/RoleAccessTest.php` — 13 tests over guests, customers, staff, admins, and the shared routes.

**Decision:** admins are allowed into the staff screens, because order fulfilment (`processing` → `ready_for_pickup` → `completed`) exists only under `/staff`; locking admins out would leave a shop with no staff account unable to finish an order. Staff remain locked out of everything under `/admin`.

---

## P1 — Correctness and data safety

- [ ] **Void an approved order.** Once an order is approved, nothing in the system can stop it — but materials have already been consumed. Add an admin action that cancels an approved order, records a reason, and returns product stock, raw materials, and texture stock.
- [ ] **Enforce order transitions server-side.** `Staff\OrderController::updateStatus` accepts any status in its list, so a crafted request can send `completed` back to `pending`. The UI offers one legal step at a time; the controller should too.
- [ ] **Restrict review to pending orders.** `Admin\OrderController::review` doesn't check the current status, so an already-approved order can be approved again — deducting materials a second time. The UI hides the button; the endpoint should refuse it.
- [ ] **Protect the delete cascades.** Deleting a category deletes its products *and* the order lines referencing them (`products.category_id` and `order_items.product_id` are both `onDelete('cascade')`). Deleting a supplier takes its purchase orders and raw materials with it. Block the delete when dependants exist, or make the user reassign first.
- [ ] **Separate email and phone verification.** `OtpController::verify` sets `phone_verified = true` when the code was delivered by email, so an unverified phone reads as verified.
- [ ] **Guard against negative stock on approval.** Approving an order deducts raw materials and textures with no check that they exist; stock can go negative silently. Warn the admin, or block it.

---

## P2 — Features and UX

- [ ] **Customer order detail page.** Customers get a list and a receipt PDF but no detail view; there's no route for one.
- [ ] **Low-stock alerts for raw materials and textures.** `ProductObserver` raises Low stock / Out of stock for products only. The other two item types appear on the watchlist but never notify.
- [ ] **Paginate and filter the admin user list.** `Admin\UserController::index` loads every account with `->get()` and offers search only — add pagination plus role and status filters.
- [ ] **Sales report export.** Materials and equipment export to PDF and DOCX; sales has charts but no export.
- [ ] **Persist the cart.** The cart lives in the session, so signing out or letting the session lapse empties it. A DB-backed cart would survive.
- [ ] **Ask Google sign-ups for a contact number.** They're created with an empty `contact_number` and never prompted, so the shop can't reach them.

---

## P3 — Tech debt

- [ ] **Move images out of the database.** Product, texture, and raw-material images are stored as base64 data URIs in their tables, so every list query drags the image bytes along and every page inlines them. User photos already use file storage — move the rest to `Storage` and migrate the existing rows.
- [ ] **Queue the notification fan-out.** Notifications are plain (non-queued) database notifications, and `Notifier::staffAndAdmins` loops over every staff and admin inline — so checkout waits on that write. The queue connection is already `database`; making the notifications `ShouldQueue` puts them on the worker.
- [ ] **Test the core flows.** Only the cart pricing tests exist. Worth covering: checkout deducts stock, approval deducts materials and emails the slip, PO delivery increments stock, cancellation returns stock.
- [ ] **Clean up `AuthController`.** It still carries exploratory comments from development ("... existing methods ...", commented-out reasoning in `authenticated()` and `verifyResetCode`).

---

## Ops reminders before deployment

Already documented in the [Setup Guide](Setup.md#12-production-deployment-notes), repeated here so they aren't missed:

- [ ] Rotate the seeded admin and staff passwords, or drop `UserSeeder` from `DatabaseSeeder`.
- [ ] Run the scheduler via cron so the overdue purchase-order check fires.
- [ ] Run a queue worker as a service.
- [ ] `APP_DEBUG=false`, real mail and SMS credentials, cached config/routes/views.
