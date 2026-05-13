# Admin User Guide

This guide is for **Administrators** — the highest-privilege role with full control of users, catalog, inventory, procurement, orders, sales, and reports.

> **Account note**: Admin accounts use the `@admin.com` email domain. Logging in routes you to `/admin/dashboard`.

---

## 1. Logging In

1. Open **Login**, enter your `@admin.com` email + password.
2. The system redirects you to the Admin Dashboard.
3. The same 3-attempts-in-3-minutes rate limit applies.

---

## 2. Dashboard

`/admin/dashboard` is your KPI panel. Expect:

- **Sales metrics** (today / this week / this month) with line charts.
- **Order pipeline** by status.
- **Active customers / staff** counts.
- **Low-stock alerts** across products, raw materials, textures.
- **Recent purchase orders** and **recent orders**.

---

## 3. Catalog Management

### 3.1 Categories
`/admin/categories`

- Create, edit, delete categories.
- Categories are referenced by products — deleting one cascades (use carefully).

### 3.2 Suppliers
`/admin/suppliers`

- Create, edit, delete suppliers with name, contact person, email, phone, address.
- Suppliers can be linked to products with cost, MOQ, and lead time.

### 3.3 Products
`/admin/products`

- Create / edit / delete products.
- Each product has: SKU (unique), name, brand, description, base price, stock, category, department, status, customizable flag, low-stock threshold, unit, image (Base64), inventory counters.
- Use **soft delete** — products that have order history remain queryable even after removal.

#### Assign Suppliers
- Open a product → **Suppliers** tab.
- Add one or more suppliers with **cost**, **min order qty**, **lead time**, and a **default** flag (preferred for reordering).

#### Assign Textures
- Open a product → **Textures** tab.
- Tick the textures that customers may apply when customizing this product.

### 3.4 Raw Materials
`/admin/raw-materials`

- Manage materials used in production with cost per unit, stock quantity, threshold, and supplier.
- Link to products via the product's bill-of-materials.

### 3.5 Textures
`/admin/textures`

- Manage both the visual asset (image) **and** the inventory side (stock, supplier, cost, threshold).
- Set a **price modifier** — the amount added to a product's base price when a customer applies this texture.

### 3.6 Equipment
`/admin/equipment`

- Asset register for office / shop equipment (not for sale).
- Each row: name, brand, property number, date acquired, cost, status (`Serviceable` / `Unserviceable`), notes.

---

## 4. Inventory

`/admin/inventory` — consolidated, editable view of all stock-bearing entities.

- See on-hand vs. on-display / sponsored / damaged / consumed.
- Filter by department.
- **Assign Default Supplier** (`POST /admin/inventory/assign-supplier`) — bulk-set the preferred supplier for products you'll reorder.

---

## 5. Purchase Orders

Same workflow as Staff (`draft → sent → confirmed → delivered → cancelled`), accessible at `/admin/purchase`.

Admin can also override status changes if a PO gets stuck (e.g. mark `delivered` even if Staff marked it `sent`). Stock counters increment when marked `delivered`.

---

## 6. Orders & Review

`/admin/orders` lists every order in every status.

### 6.1 Reviewing Customer Orders
Click an order → **Review**.

- **Approve** — moves to `approved`. Customer is notified.
- **Reject** — requires a `reason` (text). Order status becomes `cancelled` and the reason is shown to the customer on their order detail.

Use Review for orders that:
- Have unusual quantities,
- Involve a customization that needs sign-off,
- Need a manual hold for any reason.

After review, Staff continue the operational workflow (`processing` → `ready_for_pickup` → `completed`).

---

## 7. Sales

`/admin/sales` aggregates completed orders into charts (daily / monthly), top-selling products, revenue, and customer counts.

---

## 8. Reports

`/admin/reports` is the export center. Two report types are available:

### 8.1 Materials Report
`/admin/reports/materials`

- **Preview** — HTML preview at `…/preview` (filters: department, date range).
- **PDF Export** — `…/pdf` (server-rendered via DomPDF).
- **DOCX Export** — `…/docx` (PHPWord).

The report lists raw materials with stock, cost, supplier, and consumption columns.

### 8.2 Equipment Report
`/admin/reports/equipment`

- Same Preview / PDF / DOCX flow.
- Lists equipment with property number, date acquired, cost, status.

> Reports are designed to look identical across HTML / PDF / DOCX so they're acceptable for printing and filing.

---

## 9. User Management

`/admin/users` lists all accounts.

- See each user's role, status (`active` / `disabled`), email-verified, phone-verified.
- Click **Disable** to lock a user out (`status = disabled`); the user will be denied login.
- Click **Enable** to restore access.

> You cannot change a user's role from the UI — that's intentional. Role is determined at registration by email domain.

---

## 10. Settings

`/admin/settings` exposes global system toggles. Update them and click **Save**.

---

## 11. Notifications

Same UI as Staff — bell in the navbar. Admins receive:

- **New Order Placed** notifications,
- **New Custom Design Submitted** notifications,
- Any other system events fanned to "staff + admins".

---

## 12. Profile

Top-right avatar → **Profile**. Update fields and photo. Changing contact number triggers SMS OTP re-verification.

---

## 13. Logging Out

Top-right avatar → **Logout**. Standard session invalidation; back-button access blocked.

---

## 14. Best Practices

1. **Use Categories deliberately** — they cascade-delete products, so create them carefully.
2. **Set Low-Stock Thresholds on every catalog item** — the dashboard relies on them to surface alerts.
3. **Soft-delete instead of hard-delete** — preserves order history. Hard deletes only when something was created in error and has no transactional references.
4. **Run the Materials and Equipment reports monthly** and archive the PDFs for audit.
5. **Disable, don't delete, users** — keeps order/PO history intact while revoking access.
6. **Mark a preferred supplier (`is_default`) on every product** you reorder regularly — speeds up PO creation.
