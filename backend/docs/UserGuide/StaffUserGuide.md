# Staff User Guide

Staff run the shop day to day: producing the orders admins have approved, keeping stock figures honest, and buying in what runs low.

**Read this first:** the [User Guides hub](README.md) shows the whole order journey. Your part starts *after* an admin approves an order and ends when the customer walks out with it.

| | |
| :--- | :--- |
| You sign in at | `/login`, and land on `/staff/dashboard` |
| Your pages | Dashboard, Orders, Sales, Products, Raw Materials, Textures, Inventory, Purchase Orders |
| The other guides | [Customer Guide](CustomerUserGuide.md) · [Admin Guide](AdminUserGuide.md) |

---

## 1. Signing in

Sign in at `/login` with the email and password the shop issued you. Staff accounts are created directly by the shop — there is no staff sign-up form, and registering yourself would only produce a customer account.

You land on the Staff Dashboard. If sign-in is refused with an "account has been disabled" message, an admin has to re-enable you ([Admin Guide §15](AdminUserGuide.md#15-user-management)).

---

## 2. Where you fit in the workflow

```
Customer checks out          Admin reviews             YOU                        Customer
  status: pending      →     approve / reject     →    processing            →    collects
  stock deducted             materials deducted        ready_for_pickup           completed
                             slip emailed
```

Two things to take from this:

- **You never approve orders.** A pending order shows *Awaiting admin* in your list and nothing else — see [Admin Guide §4](AdminUserGuide.md#4-reviewing-orders).
- **Materials are already deducted before you start.** Approval consumed the bill of materials and the design's texture. Your job is to make what was approved and to correct stock figures if the real consumption differed.

---

## 3. Dashboard

`/staff/dashboard` is built around what needs doing today.

| Panel | What it shows |
| :--- | :--- |
| **Pipeline counters** | Pending, processing, ready for pickup, and completed-today counts |
| **Stock alerts** | How many products, raw materials, and textures sit at or below their threshold |
| **Incoming deliveries** | Purchase orders currently `sent` or `confirmed` |
| **Order status breakdown** | Live chart of pending / approved / processing / ready-for-pickup |
| **Daily orders** | Order volume over the last 7 days |
| **Order queue** | The six oldest orders still pending, approved, or processing — work down from the top |
| **Critical stock** | The five products closest to (or furthest below) their threshold |

---

## 4. Processing orders

`/staff/orders` lists every order in the system, newest first, 10 per page.

### Finding an order

- **Search** matches the order number, the payment reference, or the customer's name.
- **Filter** by status, or by date (today / this week / this month).
- Status tabs across the top carry live counts.

### Moving an order forward

Each row offers exactly one forward step, so the pipeline can't be skipped:

| Current status | Button | Becomes |
| :--- | :--- | :--- |
| `pending` | *(none — "Awaiting admin")* | — |
| `approved` | **Process** | `processing` |
| `processing` | **Ready** | `ready_for_pickup` |
| `ready_for_pickup` | **Complete** | `completed` |
| `completed` / `cancelled` | *(none)* | — |

**Moving to Processing requires a payment reference.** The confirmation dialog asks for it — enter the receipt or transaction number for the payment you took. It's stored on the order and is searchable afterwards, which is how you reconcile at end of day.

**View** opens the full order: customer details, every line, any customization (with its design preview), the total, and the payment reference.

Each change notifies the customer automatically, provided they haven't switched notifications off.

The pipeline is enforced, not just suggested: an order can only take its next step, so nothing skips a stage or moves backwards.

> Cancelling isn't part of the staff screens. If an order has to be stopped, ask an admin — they can reject it at review, or cancel it any time before hand-over, and the stock and materials come back automatically ([Admin Guide §4](AdminUserGuide.md#4-reviewing-orders)).

### Purchase Request orders

Some orders are bought through CSPC procurement instead of the cashier. These run on paperwork, and an admin drives the middle of them — so where you'd normally see a button, you'll see what the order is waiting on:

| Shows | Meaning |
| :--- | :--- |
| **Awaiting PR number** | The customer hasn't returned their PR number yet. Nothing to do. |
| **Awaiting NOA** | An admin needs to upload the Notice of Award to start production. |
| **Awaiting PO** | An admin needs to upload the Purchase Order to release delivery. |

Your one step is at the end: an order at **For Delivery** carries the usual **Complete** button. `for_delivery` is the PR path's version of `ready_for_pickup` — these orders are delivered rather than collected, so they never show as ready for pickup.

**Hand-off:** the customer sees each of these statuses on their orders page and is told when to collect — [Customer Guide §9](CustomerUserGuide.md#9-tracking-your-orders).

---

## 5. Sales

`/staff/sales` reports on **completed** orders only — nothing counts until you've handed it over.

Choose a range (7 days, 30 days, 90 days, 12 months, all time, or a custom from/to) and the page shows revenue, order count, average order value, items sold, all-time revenue, a revenue/orders chart (daily, or monthly for ranges over ~10 weeks), top-selling products, recent sales, and a status breakdown.

It's the same page and the same numbers admins see in [Admin Guide §13](AdminUserGuide.md#13-sales) — they can also export it to PDF or Word from there.

---

## 6. Inventory watchlist

`/staff/inventory` is **not** a full stock list — it shows only what has fallen to or below its low-stock threshold, across all three item types:

- Products (`stock` vs threshold)
- Raw materials (`stock_quantity` vs threshold)
- Textures (`stock_quantity` vs threshold)

Filter by search term, item type, or stock status (low versus completely out). Items are grouped by their **default supplier**, which is what makes the next step quick: pick a supplier group and raise a purchase order for everything in it at once.

Items with no default supplier are grouped separately and can't be pre-filled into a PO. Only an admin can attach one ([Admin Guide §11](AdminUserGuide.md#11-inventory-watchlist)).

---

## 7. Products

`/staff/products` — you can **view and edit**, but not create or delete.

Search by name, SKU, or brand; filter by category or stock status (in stock, low stock, out of stock); page size 10 to 100.

Editing lets you change: name, SKU, category, price, stock, unit, brand, low-stock threshold, description, status, the customizable flag, and the image (max 2 MB).

Not available to you — an admin's job:

- The **units on display / sponsored / damaged / consumed** buckets and the **department**.
- **Suppliers**, **textures**, and the **bill of materials** attached to a product.
- Creating and deleting products.

Use the plain **stock** field to correct counts after a physical audit. Dropping a product's stock to or below its threshold raises a Low stock notification for every staff member and admin; hitting zero raises an Out of stock one.

---

## 8. Raw materials

`/staff/raw-materials` — view and edit. Search matches name, description, unit, or supplier name.

Editable: name, supplier, cost per unit, stock quantity, low-stock threshold, unit, description.

Raw materials are consumed automatically when an admin approves an order that contains a product with a bill of materials — the quantity per unit times the quantity ordered. If the shop floor used more or less than the recipe says, correct the stock figure here.

---

## 9. Textures

`/staff/textures` — view and edit. Textures are two things at once: a finish a customer can apply in the design studio, and a stock item you buy and consume.

Editable: name, description, image, supplier, cost per unit, stock quantity, low-stock threshold, unit, and **price modifier** (the surcharge added when a customer picks this texture).

One unit of texture stock is deducted per ordered unit when an admin approves an order whose design uses it.

---

## 10. Purchase orders

`/staff/purchase` — the full procurement workflow. Staff and admins have identical powers here.

### The list

Every PO with its number, supplier, total cost, status, and expected delivery date. Search by PO number or supplier name; filter by status or date; status tabs carry counts.

### Creating one

1. Click **Create PO**.
2. Pick the **supplier**.
3. Add **lines**. Each line is one product, raw material, or texture, with a quantity and a cost per unit. The item picker is restricted to things that supplier actually provides.
4. Optionally set an **expected delivery date** — this is what the overdue alert watches.
5. Save. The PO is created as a **draft**, numbered `PO-20260804-A1B2` (date plus a random suffix), with the total cost calculated for you.

**The shortcut worth knowing:** starting from the [inventory watchlist](#6-inventory-watchlist) and choosing a supplier pre-fills the lines with everything of theirs that's short — quantity set to the shortfall, raised to the supplier's minimum order quantity where one is set, priced at the agreed cost. Check the numbers, then save.

### Moving it along

| Set to | When | Effect |
| :--- | :--- | :--- |
| `sent` | You've emailed or phoned the order through | None |
| `confirmed` | The supplier acknowledged it | None |
| `delivered` | The goods physically arrived | **Every line is added to stock** |
| `cancelled` | It's not happening | None |

Mark `delivered` only when the delivery is in front of you — that click is what puts the stock in. If you marked it in error, moving it back off `delivered` removes the same quantities again.

Every status change notifies all staff and admins, so the whole team sees a delivery land.

**Overdue POs:** a daily check flags anything still `sent` or `confirmed` past its expected delivery date and notifies the team once per PO — your cue to chase the supplier.

---

## 11. Notifications

The bell refreshes every 30 seconds and holds your ten most recent items; **View all** opens the full list, 20 per page. As staff you receive:

| Notification | Raised when |
| :--- | :--- |
| **New order placed** | A customer checks out |
| **New custom design** | A customer saves a brand-new design |
| **New customer registered** | Somebody signs up |
| **Low stock** / **Out of stock** | Any product, raw material, or texture crosses its threshold or hits zero |
| **Purchase order status changed** | Anyone moves a PO |
| **Purchase order overdue** | A sent or confirmed PO passes its expected delivery date |

Click any notification to jump to the page it refers to. Mark all as read, or delete items individually.

Alerts fire on the crossing, not on every movement below the line, so a shortage tells you once rather than on repeat. The [inventory watchlist](#6-inventory-watchlist) is the standing view of everything currently short.

---

## 12. Profile and settings

**Avatar → Profile**: full name, email, contact number, address, photo, and password (leave the password fields blank to keep the current one). Changes apply immediately.

**Avatar → Settings**: the **Enable notifications** switch. Turning it off silences in-app notifications for your account — including stock alerts, so leave it on unless you have a reason.

**Avatar → Logout** ends your session.

---

## 13. What only an admin can do

| Task | Where it lives |
| :--- | :--- |
| Approve or reject orders | [Admin §4](AdminUserGuide.md#4-reviewing-orders) |
| Create or delete products, raw materials, textures | [Admin §7](AdminUserGuide.md#7-products) |
| Categories, suppliers, equipment | [Admin §5](AdminUserGuide.md#5-categories), [§6](AdminUserGuide.md#6-suppliers), [§10](AdminUserGuide.md#10-equipment) |
| Attach suppliers, textures, or a bill of materials to a product | [Admin §7](AdminUserGuide.md#7-products) |
| Edit product unit buckets and department | [Admin §7](AdminUserGuide.md#7-products) |
| Assign a default supplier from the watchlist | [Admin §11](AdminUserGuide.md#11-inventory-watchlist) |
| Export materials and equipment reports | [Admin §14](AdminUserGuide.md#14-reports) |
| Enable or disable user accounts | [Admin §15](AdminUserGuide.md#15-user-management) |

If you need any of these, ask an admin.
