# Admin User Guide

Admins own the catalog, decide which orders go into production, run procurement alongside staff, and produce the reports the shop files.

**Read this first:** the [User Guides hub](README.md) lays out the whole order journey and who touches it. Your decision — approve or reject — is the gate every order passes through, and it's the moment materials are consumed.

| | |
| :--- | :--- |
| You sign in at | `/login`, and land on `/admin/dashboard` |
| Your pages | Dashboard, Orders, Products, Categories, Suppliers, Raw Materials, Textures, Equipment, Inventory, Purchase Orders, Sales, Reports, Users |
| The other guides | [Customer Guide](CustomerUserGuide.md) · [Staff Guide](StaffUserGuide.md) |

---

## 1. Signing in

Sign in at `/login`. Admin accounts are created directly in the database — the public sign-up form only ever produces customers, whatever the email address. The seeder in `database/seeders/UserSeeder.php` creates one admin and one staff account for a fresh install; change those passwords before the system goes anywhere near real use.

---

## 2. Where you fit in the workflow

```
Customer checks out          YOU                        Staff                      Customer
  status: pending      →     approve / reject     →     processing            →    collects
  product stock down         materials down             ready_for_pickup           completed
                             slip emailed
```

You are also half of procurement: purchase orders are shared ground with staff, and the two of you see the same inventory watchlist and the same sales numbers.

---

## 3. Dashboard

`/admin/dashboard` is the commercial view of the shop.

| Panel | What it shows |
| :--- | :--- |
| **Revenue** | All-time and today's revenue from **completed** orders, plus month-on-month growth |
| **Orders** | Orders awaiting review, and month-on-month order growth |
| **Low stock** | A single count across products, raw materials, and textures below threshold |
| **Catalog counters** | Total products, customers, suppliers, and open purchase orders (`draft`, `sent`, or `confirmed`) |
| **New customers** | Sign-ups this month |
| **Stock trend** | The five most-ordered products over the last 30 days, with stock levels reconstructed from completed sales |
| **Top products** | Best sellers by quantity |

Revenue counts `completed` orders only — an approved order that hasn't been handed over yet is not yet revenue.

---

## 4. Reviewing orders

`/admin/orders` lists every order in the system, newest first, 10 per page. Search by order number, payment reference, or customer name; filter by status or by date (today / this week / this month).

**Only `pending` orders carry a Review button.** Everything else opens read-only, showing the customer, the lines, any customization with its preview, the total, the payment reference staff recorded, and the cancellation reason if there is one.

### Approving

Open **Review**, check the lines and any customization, then **Approve**. In one step the system:

1. Sets the order to `approved`.
2. **Deducts raw materials** — for every line, the product's bill of materials × the quantity ordered.
3. **Deducts texture stock** — one unit per ordered unit, for designs that use a texture.
4. **Emails the customer their transaction slip** (the PDF receipt with the order-number barcode).
5. Notifies the customer that the status changed.

Product stock was already deducted at checkout, so approval does not touch it again.

Review runs once. An order that has already been reviewed can't be reviewed again — otherwise a second approval would deduct its materials twice.

### Rejecting

Choose **Reject** and write a **reason** — it's required, and the customer reads it on their order. The order becomes `cancelled` and product stock is returned. Materials were never consumed at this point, so there's nothing else to give back.

### If materials are short

Approval is **refused** when the order would take any material or texture below zero. The message names what's short and by how much, for example *"Fabric (needs 6 m, 5 in stock)"*. Restock it — usually via a [purchase order](#12-purchase-orders) — and approve again.

### What to check before approving

- The quantities look sane for the customer and the product.
- Any customization is something the shop can actually produce.
- The finished-goods stock covers it; the materials check is automatic.

### Cancelling after approval

Orders that are `approved`, `processing`, or `ready_for_pickup` carry a **Cancel** button in the orders list. It asks for a reason, then returns everything the order took — product stock, raw materials, and textures — and notifies the customer.

A `completed` order can't be cancelled: it's already in the customer's hands.

**Hand-off:** approving hands the order to staff, who take it through `processing` → `ready_for_pickup` → `completed` ([Staff Guide §4](StaffUserGuide.md#4-processing-orders)). The customer watches the same statuses ([Customer Guide §9](CustomerUserGuide.md#9-tracking-your-orders)).

---

## 5. Categories

`/admin/categories` — create, edit, and delete the categories products are filed under. A category has a name and an optional description.

> **A category with products in it can't be deleted.** The database cascade would take those products *and* the order lines referencing them, so the delete is refused while any product — including soft-deleted ones — is still filed under it. Move them to another category first.

---

## 6. Suppliers

`/admin/suppliers` — name, contact person, email (unique), phone, address.

Suppliers are what tie procurement together: products link to them with an agreed cost, raw materials and textures each name one, and every purchase order belongs to one.

> **A supplier still carrying purchase orders or raw materials can't be deleted** — the cascade would take that history with it. The refusal message says what's blocking. Reassign those first, delete second. (Textures are safe either way: they survive with their supplier field cleared.)

The same protection covers raw materials and textures: neither can be deleted while it appears on a purchase order line, since that would rewrite the purchase history.

---

## 7. Products

`/admin/products` — full control. Search by name, SKU, or brand; filter by category or stock status; page size 10 to 100.

### Creating a product

Required: **name**, **SKU** (unique), **category**, **price**, **stock**, **unit**.
Optional: brand, description, low-stock threshold, the **units on display / sponsored / damaged / consumed** buckets, **department** (Digital Customization Center, Book Production, or Woodworks), **status**, an image (max 2 MB), and the **customizable** flag.

Status decides shop visibility: only **active** or **functional** products with a price above zero appear to customers. *Maintenance* and *broken* keep an item in the catalog but out of the shop.

Saving takes you straight to supplier assignment, because a product with no supplier can never be pre-filled into a purchase order.

### Suppliers for a product

For each supplier you attach: **cost**, **minimum order quantity**, **lead time in days**, and optionally the **default** flag. Only one supplier can be the default, and the default is what procurement uses — the [inventory watchlist](#11-inventory-watchlist) groups by it and pre-filled POs price against it.

### Textures for a product

Tick the textures a customer may apply to this product in the design studio. If you tick none, the studio offers **every** texture, which is rarely what you want for a physical product.

### Bill of materials

Edit the product and add raw materials with a **quantity required** per unit. That recipe is what gets deducted when you approve an order. A product with no BOM consumes no materials on approval.

### Deleting

Deleting from the product page is a **soft delete**: the row stays, so order history and reports still resolve. Prefer this to any route that hard-deletes.

---

## 8. Raw materials

`/admin/raw-materials` — create, edit, delete. Fields: name, **supplier** (required), cost per unit, stock quantity, low-stock threshold, unit, description, image, the four unit buckets, and department.

Stock falls automatically when you approve orders for products that list the material in their BOM, and rises when a purchase order containing it is marked `delivered`. Staff can correct the figures ([Staff Guide §8](StaffUserGuide.md#8-raw-materials)).

Raw materials raise a low-stock alert the moment they cross their threshold, the same as products and textures.

---

## 9. Textures

`/admin/textures` — create, edit, delete. A texture is both a finish in the design studio and a stock item.

Fields: name, description, image, supplier, cost per unit, stock quantity, low-stock threshold, unit, the unit buckets, department, and **price modifier** — the surcharge shown on the swatch when a customer selects it, and charged on top of the element fees when they order.

A price modifier applies from the moment you set it, but only to designs added to a cart afterwards. Orders already placed keep the price they were charged.

---

## 10. Equipment

`/admin/equipment` — the fixed-asset register for shop machinery. Nothing here is for sale and nothing carries stock.

Fields: name, brand, property number, date acquired, **cost** (required), **status** (Serviceable, Non-Serviceable, Functional, or Returned to supplier for repair), and notes.

This register is the source for the equipment report in [§14](#14-reports), so keep property numbers and statuses accurate.

---

## 11. Inventory watchlist

`/admin/inventory` shows only what has fallen to or below its low-stock threshold — products, raw materials, and textures together. Filter by search, item type, or stock status (low versus out).

Items are grouped by **default supplier**, and each group offers a straight jump into a pre-filled purchase order ([§12](#12-purchase-orders)).

**Assign default supplier** — the admin-only action here. Anything sitting in the "no supplier" group can't be reordered automatically; assign one and it joins that supplier's group. For a product you must also give the agreed **cost**; for raw materials and textures the supplier is simply set.

Staff see this same watchlist without the assign action ([Staff Guide §6](StaffUserGuide.md#6-inventory-watchlist)).

---

## 12. Purchase orders

`/admin/purchase` — identical in every respect to the staff procurement screens, so the full walkthrough lives in [Staff Guide §10](StaffUserGuide.md#10-purchase-orders). The essentials:

- Every PO is created as a **draft**, numbered `PO-20260804-A1B2`, against one supplier, with lines of products, raw materials, or textures.
- Starting from a supplier group on the watchlist pre-fills the lines from the shortfall, raised to the minimum order quantity.
- `draft` → `sent` → `confirmed` → `delivered`, with `cancelled` available throughout.
- **Marking `delivered` is what adds stock.** Moving a PO back out of `delivered` takes the same quantities off again.
- Every status change notifies all staff and admins; POs past their expected delivery date raise an overdue alert.

The overdue alert is a daily 07:00 check and needs Laravel's scheduler running on the server — locally it comes up with `composer dev`, in production it needs the cron entry in [README §12](../../README.md#12-production-deployment-notes). Without it, overdue POs pass silently.

---

## 13. Sales

`/admin/sales` reports **completed** orders. Pick a range — 7 days, 30 days, 90 days, 12 months, all time, or a custom from/to — and you get revenue, order count, average order value, items sold, all-time revenue, a revenue and order chart (daily, switching to monthly beyond about ten weeks), top-selling products, recent sales, and a status breakdown.

**Exporting.** The buttons beside the range picker preview the report, download it as **PDF**, or download it as **DOCX**. Exports carry whatever range is on screen, so the document always matches the figures you were looking at: summary, best sellers, and the period-by-period breakdown.

Staff see the same page, without the export buttons ([Staff Guide §5](StaffUserGuide.md#5-sales)).

---

## 14. Reports

`/admin/reports` — admin-only, and the reason the unit buckets and departments exist. Two reports, each available as an on-screen **PDF preview**, a **PDF download**, and a **DOCX download**.

### Materials report

`/admin/reports/materials` covers products, raw materials, and textures in one document, split into sections by department (Digital Customization Center, Book Production, Woodworks, then Uncategorized). Columns: type, name, unit, on display, sponsored, damaged, consumed, and available.

Filters: item group (all, products, raw materials, or textures), a **last-updated** date range, a name search, and a single department for a per-section export.

Downloads are named `inventory-materials[-department]-YYYY-MM-DD.pdf` / `.docx`.

### Equipment report

`/admin/reports/equipment` lists name, brand, property number, date acquired, cost, and status.

Filters: status, a **date-acquired** range, and a search across name, brand, and property number. Downloads are named `inventory-equipment-YYYY-MM-DD.pdf` / `.docx`.

Both reports render from the same layout in all three formats, so the PDF you file and the DOCX you edit match what you previewed.

---

## 15. User management

`/admin/users` lists every account — admin, staff, and customer — newest first. Search by name, email, or contact number, filter by **role** or **status**, and page through with a selectable page size.

**Disable** locks an account out: sign-in is refused by password and by Google alike, while all their orders and history stay intact. **Enable** restores access. You cannot disable your own account.

Roles are not editable here. Changing someone's role, or creating a new staff or admin account, is a database operation — the sign-up form always produces a customer.

Prefer disabling to deleting: it revokes access without touching order history.

---

## 16. Notifications

The bell refreshes every 30 seconds and holds the ten most recent items; **View all** pages through the rest, 20 at a time. Admins receive everything staff do:

| Notification | Raised when |
| :--- | :--- |
| **New order placed** | A customer checks out — your cue to review it |
| **New custom design** | A customer saves a brand-new design |
| **New customer registered** | Somebody signs up |
| **Low stock** / **Out of stock** | Any product, raw material, or texture crosses its threshold or hits zero |
| **Purchase order status changed** | Anyone moves a PO |
| **Purchase order overdue** | A sent or confirmed PO passes its expected delivery date |

Alerts fire on the crossing, not on every movement below the line. The [inventory watchlist](#11-inventory-watchlist) is the standing view of everything currently short.

---

## 17. Profile and settings

**Avatar → Profile**: full name, email, contact number, address, photo, and password (blank leaves it unchanged).

**Avatar → Settings**: the **Enable notifications** switch — a personal preference for your own account, not a system-wide setting. Turning it off silences your order and stock alerts, so leave it on.

---

## 18. Routine checklists

**Daily**

1. Clear the review queue — pending orders block staff entirely.
2. Skim the watchlist for anything newly out of stock.
3. Chase overdue purchase orders.

**Weekly**

1. Check that every regularly-reordered item has a default supplier, so POs can be pre-filled.
2. Review the low-stock thresholds on fast movers — a threshold that's too low means you find out too late.
3. Reconcile completed orders against sales.

**Monthly**

1. Export the materials and equipment reports and file the PDFs.
2. Audit physical stock and have staff correct the figures.
3. Review user accounts and disable anyone who has left.

**Habits worth keeping**

- Set a low-stock threshold on every stock item — the watchlist and every alert key off it.
- Give every product a default supplier with an agreed cost and minimum order quantity.
- Soft-delete products rather than deleting the category around them.
- Disable users; don't delete them.
