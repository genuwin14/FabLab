# Staff User Guide

This guide is for **Staff** users — the operational role that processes orders, manages day-to-day inventory, and runs procurement.

> **Account note**: Staff accounts use the `@staff.com` email domain. Logging in with such an account automatically routes you to the Staff Dashboard.

---

## 1. Logging In

1. Go to **Login**.
2. Enter your `@staff.com` email and password.
3. The system redirects you to `/staff/dashboard`.
4. After 3 failed attempts you'll be locked out for 3 minutes.

---

## 2. Dashboard

`/staff/dashboard` is your control center. It surfaces:

- **Today's Orders** — by status.
- **Low-Stock Alerts** — products/raw materials/textures below their `low_stock_threshold`.
- **Recent Purchase Orders** — your most recent procurement activity.
- **Quick Links** to Orders, Inventory, and Purchase Orders.

---

## 3. Managing Orders

Navigate via the sidebar → **Orders**.

### 3.1 List View
- Filter by status, date, or customer.
- Search by order number (e.g. `ORDR-XXXX`).
- Each row shows: number, customer, total, status, placed-on.

### 3.2 Status Workflow
Click an order → choose the next status:

```
pending  →  approved  →  processing  →  ready_for_pickup  →  completed
                                                          ↘
                                                            cancelled (with reason)
```

- **Approve** a `pending` order once you've verified availability.
- Move to **Processing** when you start preparing it.
- Mark **Ready for Pickup** when the customer can collect.
- Mark **Completed** after handoff.
- To **Cancel**, you must enter a reason (visible to the customer on their order detail page).

> The customer is notified automatically whenever a status changes.

---

## 4. Sales (Read-only)

`/staff/sales` shows a read-only summary of completed orders. Useful for end-of-day reconciliation.

---

## 5. Products

`/staff/products` — read + edit (you **cannot** create or delete).

- View the catalog.
- Click **Edit** to update fields like price, description, low-stock threshold, unit, and image.
- Stock counter columns (`stock`, `units_on_display`, `units_sponsored`, `units_damaged`, `units_consumed`) are editable so you can correct counts after a physical audit.

---

## 6. Raw Materials

`/staff/raw-materials` — read + edit.

- Each material shows: name, supplier, cost-per-unit, stock, low-stock threshold, unit.
- **Edit** lets you update the same fields you can edit on products, plus material-specific ones like `quantity_required` linkages (visible via product detail).

---

## 7. Textures

`/staff/textures` — read + edit.

- Textures are both **customization options** and **inventory items** — they have stock counters like raw materials, plus a `price_modifier` that gets added to a product's price when applied to a custom design.
- Update stock counters here when textures are physically received or consumed.

---

## 8. Inventory (Read-only)

`/staff/inventory` is a consolidated read-only view that joins **products + raw materials + textures**, showing:

- Total on-hand vs. each "units_*" bucket.
- Items below threshold (highlighted).
- Filter by department.

Use this view before creating a Purchase Order.

---

## 9. Purchase Orders (Procurement)

This is the full procurement workflow — Staff can create, send, confirm, and receive POs.

### 9.1 List View
`/staff/purchase` — every PO with status, supplier, total, ETA.

### 9.2 Create a PO
1. Click **Create PO**.
2. Pick a **Supplier**.
3. Add **line items** — each line is one of:
   - A finished **Product** (for resale stock),
   - A **Raw Material**,
   - A **Texture**.
4. Enter quantity and cost per unit for each line.
5. Set **Expected Delivery Date** (optional).
6. Save as `draft` or **Send** immediately.

### 9.3 Status Workflow
```
draft  →  sent  →  confirmed  →  delivered  →  cancelled (any stage)
```

- **Sent** — PO has been transmitted to the supplier (manually, outside the system).
- **Confirmed** — supplier has confirmed the order.
- **Delivered** — when you mark this, **stock counters are incremented** on every referenced item.
- **Cancelled** — available at any stage; does not affect stock unless already delivered.

> Click into any PO to view line items, supplier contact info, and a status history.

---

## 10. Notifications

The bell icon in the navbar shows unread items. You'll get notifications for:

- **New Order Placed** — when a customer checks out.
- **New Custom Design Submitted** — when a customer saves a brand-new design.

Click a notification to jump to the relevant page. Mark all as read with **Mark all as read**, or delete individually.

---

## 11. Profile

Top-right avatar → **Profile**.

Update your name, contact number, photo. Changing your contact number triggers SMS OTP re-verification.

---

## 12. Settings

Top-right avatar → **Settings**.

- **Enable Notifications** — toggle to stop receiving in-app notifications. (Operational emails still apply.)

---

## 13. Logging Out

Top-right avatar → **Logout**. Session ends and back-button access to protected pages is blocked.

---

## 14. What You Cannot Do (Admin-only)

These are intentionally locked off for the Staff role:

- Create or delete products, categories, raw materials, textures, suppliers, equipment.
- Manage user accounts (enable/disable).
- Approve / reject orders at the **review** stage (admin review).
- Export Reports (Materials, Equipment) — admin only.
- Edit global System Settings.

If you need any of these, raise the request with an Admin.
