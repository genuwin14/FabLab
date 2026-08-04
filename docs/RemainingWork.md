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

## P1 — Correctness and data safety ✅ Done

- [x] **Void an approved order.** `POST /admin/orders/{id}/cancel` cancels an `approved`, `processing`, or `ready_for_pickup` order with a required reason, returning product stock, raw materials, and textures. A `completed` order stays closed. The orders list grows a **Cancel** button that reuses the review modal in cancel-only mode.
- [x] **Enforce order transitions server-side.** `Staff\OrderController` now holds the pipeline as data (`approved → processing → ready_for_pickup → completed`) and refuses anything that isn't the next step.
- [x] **Restrict review to pending orders.** Reviewing anything else is refused, so materials can't be deducted twice.
- [x] **Protect the delete cascades.** Categories holding products (soft-deleted included), suppliers carrying purchase orders or raw materials, and materials or textures appearing on a purchase order line can no longer be deleted; the refusal names what's blocking.
- [x] **Separate email and phone verification.** An emailed code sets `email_verified_at` only. Sign-in still accepts either channel, so email-verified accounts keep working.
- [x] **Guard against negative stock on approval.** Approval is refused when the order would take a material or texture below zero, naming the shortfall.

Stock movements moved into `app/Services/OrderStockService.php` — requirements are aggregated across order lines, so a material used by two different products in one order is checked and moved once. Covered by `OrderWorkflowTest`, `CatalogDeletionGuardTest`, and `OtpVerificationTest`.

**Also fixed along the way:** `email_verified_at` was missing from the `User` model's `$fillable`, so the `'email_verified_at' => now()` in the Google sign-up path was silently discarded and those accounts never read as email-verified.

---

## P2 — Features and UX ✅ Done

- [x] **Customer order detail.** The detail view already existed as a per-order drawer — what was missing was reaching it. The receipt is now offered in the drawer and in the table view (the card view already had it), and an order-status notification links to `#order-{id}`, which opens that order's drawer on arrival instead of dropping the customer on the whole list.
- [x] **Low-stock alerts for raw materials and textures.** `ProductObserver` became `StockLevelObserver`, watching all three stock-bearing models through a shared `TracksStockLevel` trait, and the two alert notifications now take any of them.
- [x] **Paginate and filter the admin user list.** Pagination with a selectable page size, plus role and status filters alongside the existing search.
- [x] **Sales report export.** PDF preview, PDF download, and DOCX download, carrying whatever range is on screen.
- [x] **Persist the cart.** A `cart_items` table keyed per user, so a cart survives sign-out, session expiry, and switching device. Carts still sitting in a session are absorbed on the customer's next visit rather than dropped.
- [x] **Ask Google sign-ups for a contact number.** A skippable prompt after the Google callback, and again on later sign-ins while the number is still missing.

Sales figures moved into `app/Services/SalesReport.php`, shared by the admin page, the staff page and the exports, so a document can't disagree with the screen it came from.

**Also fixed along the way:** the monthly sales grouping used `DATE_FORMAT()`, which is MySQL-only — on the SQLite setup the guide recommends for local work, any range wider than about ten weeks threw a fatal SQL error. It now buckets by day (`DATE()`, understood by both) and rolls up to months in PHP.

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
