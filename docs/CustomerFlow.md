# Customer User Flow (Current v1 & Planned v2)

## 1. Authentication & Onboarding
**Current Flow (v1):**
1.  **Landing**: Visitors arrive at `/` (Landing Page).
2.  **Register/Login**: Standard Laravel Auth forms.
3.  **Redirection**: Customers redirected to `customer.dashboard`.

**v2 Improvements:**
-   **Social Login**: Improve/Fix Google Login flow (`google.login`).
-   **Guest Checkout**: Allow purchasing without forcing account creation (optional, increases conversion).

## 2. Browsing & Shopping (`customer.dashboard`)
**Current Flow (v1):**
-   **Load**: Fetches *all* products.
-   **Display**: Card Grid layout with "Add to Cart" and "Buy Now" buttons.
-   **Filtering**: **Critical Bottleneck** - Uses client-side JS to hide/show products based on `data-category`. No server call is made when filtering.
-   **Sorting**: Client-side JS sorting.

**v2 Improvements:**
-   **Server-Side Search & Filter**: Implement AJAX filtering (or Laravel Livewire). The URL should update (e.g., `?category=electronics&sort=price_asc`) to allow link sharing.
-   **Pagination**: Essential for v2. The current "load all" approach will crash with 1000+ products.
-   **Refined UI**: Detailed "Quick View" modal to see product details without leaving the shop page.

## 3. Product Details (`customer.indexview`)
**Current Flow (v1):**
-   Shows Image, Price, Description.
-   **Reviews**: Lists reviews and ratings.
-   **Actions**: "Buy Now" (Modal) and "Add to Cart" (AJAX).

**v2 Improvements:**
-   **Related Products**: "You might also like" section based on category tags.
-   **Stock Real-time**: If 5 people are viewing the last item, show "Only 1 left!".

## 4. Customization Experience
**Current Flow (v1):**
-   **Customize Product**: `customershop.customize`.
-   **Controllers**: `CustomizationController`.
-   **Visuals**: Supports uploading distinct **Front Image** and **Back Image**.
-   **Pricing**: `calculatePrice` logic sums up product base price + complexity + multiple textures (counting duplicates).
-   **Payment**: Users can choose `Full` or `Partial` (50%) payment for custom orders.

**v2 Improvements:**
-   **Interactive 3D/Canvas Preview**: Instead of just uploading a file/texture, use a JS library (like Fabric.js or Three.js) to show the texture applied to the product model in real-time.
-   **Pricing Engine**: Dynamic price updates as the user adds complex customizations, visible immediately on the UI.
-   **Drafts**: Allow users to save a design as a "Draft" to finish later.

## 5. Cart & Checkout
**Current Flow (v1):**
-   **Cart**: `customer.cart.index`. Simple table with +/- quantity.
-   **Checkout**:
    -   Address Input.
    -   Payment Selection (Online/COD).
    -   `customercart.checkout` processes the order.

**v2 Improvements:**
-   **Multi-Step Checkout**: Break into "Shipping" -> "Payment" -> "Review" steps for better UX.
-   **Address Book**: Save used addresses to Profile so users don't type them every time.
-   **Payment Gateways**: Integrate robust providers (Stripe/GCash API) directly rather than generic "Online Pay" placeholders.

## 6. Order Tracking (`customer.orderlist.index`)
**Current Flow (v1):**
-   Table showing Order ID, Status, and Total.
-   **Cancel**: Can cancel if status is "Pending".

**v2 Improvements:**
-   **Visual Timeline**: A progress bar showing "Placed -> Confirmed -> Packing -> Shipped -> Delivered".
-   **Push Notifications**: Browser notifications when order status changes.

## 7. Profile & Settings
**Current Flow (v1):**
-   Manage basic account details (`customer.profile.viewprofile`).
-   Notification center (`customer.notifications.index`).

**v2 Improvements:**
-   **Order History Export**: Allow users to download invoices/receipts of past orders.

