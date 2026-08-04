# Customer User Guide

Everything a customer can do in the FABLAB Inventory Monitoring System: creating an account, browsing, designing your own version of a product, ordering, and following that order until you collect it.

**New here?** The [User Guides hub](README.md) explains how your orders travel through the shop. The short version: you place an order, an **admin reviews it**, then **staff produce it** and tell you when to collect. You'll see each step on your orders page.

| | |
| :--- | :--- |
| You sign in at | `/login`, and land on the Shop |
| Your pages | Shop, Customize, My Designs, Cart, My Orders |
| The other guides | [Staff Guide](StaffUserGuide.md) · [Admin Guide](AdminUserGuide.md) |

---

## 1. Creating your account

1. Click **Sign Up** (`/register`).
2. Fill in **Full Name**, **Email**, **Contact Number**, and a **Password** — at least 8 characters, typed twice.
3. Choose how you want your verification code delivered: **SMS** to your phone, or **Email**.
4. Submit. A 6-digit code is sent by the method you chose.
5. Type the code on the verification page. Your account activates and you land on the Shop.

Notes:

- Everyone who registers becomes a **customer**. Staff and admin accounts are created by the shop, not through this form.
- If you started registering before and never verified, using the same email again simply updates your details and sends a fresh code.
- Didn't get the code? Use **Resend Code** on the verification page. Check the number or address you typed — a typo sends the code somewhere else.

**Signing up with Google** creates a verified customer account instantly: no code, no phone step. Add your contact number later in [Profile](#13-profile-and-settings).

---

## 2. Signing in

1. Go to `/login`, enter your email and password, or use **Continue with Google**.
2. You land on the Shop.

If you registered but never verified, signing in shows the verification prompt again rather than the shop — finish the code step and you're in.

If your account has been **disabled**, sign-in is refused with a message asking you to contact support. Only an admin can restore it ([Admin Guide §15](AdminUserGuide.md#15-user-management)).

### Forgotten password

1. Click **Forgot Password** and enter your email.
2. The system shows your masked email and phone, and asks which one to send the code to.
3. Enter the 6-digit code. **It expires 10 minutes after it's sent** — request a new one if you're too late.
4. Set the new password (8 characters minimum, typed twice), then sign in with it.

---

## 3. Getting around

| Where | What's there |
| :--- | :--- |
| **Shop** | The catalog: search, filter, add to cart |
| **Customize** | The 3D studio for designing your own version of a product |
| **My Designs** | Designs you've saved |
| **Cart** (top bar) | What you've picked, with a live item count |
| **My Orders** | Every order you've placed, and its current status |
| **Bell** (top bar) | Notifications, newest first |
| **Avatar** (top right) | Profile, Settings, Logout |

---

## 4. Browsing the shop

The Shop lists 12 products per page.

- **Search** matches a product's name, SKU, or description.
- **Category chips** narrow the list to one category.
- Each card shows the image, name, price, stock state, and a **Customizable** badge where the product supports designing.
- Clicking a card opens a **quick view** with the full description and the add-to-cart controls.

Not every catalog item appears here. A product shows up only if the shop has marked it *active* or *functional* **and** given it a price above zero — so if something you expected is missing, it's being worked on rather than hidden from you ([Admin Guide §7](AdminUserGuide.md#7-products)).

### Adding to the cart

Choose a quantity and click **Add to Cart**. Stock is checked as you do: asking for more than exists returns *"Insufficient stock! Only N left"*, and the cart badge updates when it succeeds.

---

## 5. Customizing a product

Products marked customizable carry a **Customize Now** button on their shop card, which opens them in the 3D studio (`/customer/customize`). You can also open the studio from the sidebar and pick a product there. Inside you can:

- Add **text** elements — ₱50 each.
- Add **shapes** — ₱30 each.
- Add **logos** — ₱150 each.
- Switch on **LED lighting** — ₱500.
- Apply a **texture** from the swatches. Textures the shop has assigned to that product appear; where a texture carries a surcharge, the swatch shows it.

The running total updates as you work, and the badge shows how much customization has added on top of the base price.

When you're done:

- **Save Design** keeps it in [My Designs](#6-my-designs) without ordering anything.
- **Add to Cart** saves it *and* puts it in your cart.

The first time you save a brand-new design, staff and admins are notified so they can look at what you've asked for before it reaches production.

The figure the studio shows you is the figure you pay: element fees plus any texture surcharge, on top of the base price. It carries through to your cart, your order, and your receipt unchanged.

---

## 6. My Designs

`/customer/my-designs` lists every design you've saved, newest first, each with its preview image and the product it's based on.

From here you can:

- **Preview** it in 3D.
- **Reopen** it in the studio to keep editing — saving again updates the same design rather than creating a second copy.
- **Add to Cart**.
- **Delete** it. Deleting a design does not affect orders you've already placed with it.

---

## 7. Your cart

- Each line shows the image, name, unit price, quantity, and a remove button.
- Changing the quantity re-checks stock immediately; if the new number exceeds what's available you'll be told and the line stays as it was.
- **The same product with two different designs is two separate lines** — that's deliberate, since they're different things to make.
- Your cart lives in your browser session. Signing out or leaving it long enough for the session to expire clears it; saved **designs** are never lost this way.

---

## 8. Checking out

1. Tick the lines you want to buy. You can check out part of your cart and leave the rest.
2. Review the preview slip, then confirm.
3. The system re-verifies stock for every selected line, creates the order under a number beginning `ORDR-`, deducts the stock, and clears just those lines from your cart.
4. You land on **My Orders**, and the order starts at **Pending**.

If anything sold out between adding and checking out, the whole checkout stops with a message naming the product, and nothing is charged or reserved.

**Hand-off:** your new order notifies staff and admins straight away. An admin reviews it next — [Admin Guide §4](AdminUserGuide.md#4-reviewing-orders).

---

## 9. Tracking your orders

**My Orders** lists your orders newest first, with their lines and totals.

| Status | What it means for you | Who set it |
| :--- | :--- | :--- |
| **Pending** | Submitted, waiting for the shop to review it. You can still cancel | The system, at checkout |
| **Approved** | Accepted for production. A transaction slip has been emailed to you | An admin |
| **Processing** | Being made | Staff |
| **Ready for Pickup** | Waiting for you at the shop — go collect it | Staff |
| **Completed** | Handed over. Done | Staff |
| **Cancelled** | Not going ahead. If the shop cancelled it, the reason is shown on the order | You, an admin, or staff |

Each change also arrives as a notification, as long as notifications are on in your [Settings](#13-profile-and-settings).

**Hand-off:** the production statuses are driven by staff — [Staff Guide §4](StaffUserGuide.md#4-processing-orders). Approval and rejection are an admin decision — [Admin Guide §4](AdminUserGuide.md#4-reviewing-orders).

---

## 10. Cancelling an order

You can cancel **only while the order is still Pending**. Click **Cancel** on the order; the stock goes back to the shop immediately.

The shop can also stop a pending order — an admin who rejects it must give a reason, which then appears on your order.

Once an order has been **approved**, it can't be cancelled from either side: materials have been consumed and production may already have started. If something is wrong with an approved order, contact the shop directly and sort it out with them.

---

## 11. Your receipt

Every approved order has a **transaction slip**: a PDF carrying the order number as a barcode, your details, the lines, and the total.

- It's **emailed to you automatically** the moment an admin approves the order.
- You can also open it any time from the order itself, which streams the PDF in your browser — print it or save it from there.

The slip is available once the order reaches Approved, and stays available through Processing, Ready for Pickup, and Completed. Pending and cancelled orders have no slip.

---

## 12. Notifications

The **bell** in the top bar carries an unread count and refreshes every 30 seconds. Click it for your ten most recent notifications, or **View all** for the full list (20 per page).

You'll be notified when your order status changes. From the list you can click a notification to open the order it refers to, **mark all as read**, or delete individual items.

To stop in-app notifications entirely, switch them off in Settings. Transactional email — verification codes, password resets, and your transaction slip — is sent regardless.

---

## 13. Profile and settings

**Avatar → Profile** lets you update:

- Full name, email, contact number, address.
- Degree, year, and section (optional, for CSPC students).
- Gender and profile photo.
- Your password — fill the password fields to change it, leave them blank to keep the current one.

Changing your contact number or email takes effect immediately; there's no re-verification step.

**Avatar → Settings** holds one switch: **Enable notifications**. Off means no in-app notifications for your account.

**Avatar → Logout** ends the session. Your cart is cleared; your designs and orders are not.

---

## 14. Troubleshooting

**Why does the same product appear twice in my cart?**
Because the two lines carry different designs, or one is customized and the other isn't. Each is made separately.

**My verification code never arrived.**
Use **Resend Code**, and check the number or email address you registered with. SMS delivery depends on the shop's gateway being configured — if it never arrives, ask the shop to verify you by email instead.

**Why is my order still Pending?**
Every order waits for a human review. Nothing moves until an admin approves it.

**My order was cancelled — why?**
Open the order: a cancellation made by the shop always carries a reason.

**Can I change the quantity after checking out?**
No. Cancel while it's still Pending and order again, or ask the shop.

**A product I want isn't in the shop.**
It's either out of the active catalog or has no price set yet. Ask the shop — an admin controls both.
