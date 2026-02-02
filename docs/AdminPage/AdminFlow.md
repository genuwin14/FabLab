# Admin User Flow (Current v1 & Planned v2)

## 1. Authentication & Entry
**Current Flow (v1):**
1.  **Login**: Admin visits `/login`.
2.  **Validation**: `AuthController` validates credentials.
3.  **Role Check**: Middleware redirects to `admin.dashboard` if role is 'admin'.
4.  **Landing**: Admin lands on the Dashboard (`resources/views/admin/dashboard.blade.php`).

**v2 Improvements:**
-   **Multi-Factor Authentication (MFA)**: Add optional 2FA for admin accounts for security.
-   **Session handling**: Ensure explicit session timeouts and "Remember Me" functionality are robust.

## 2. Dashboard (`admin.dashboard`)
**Current Flow (v1):**
-   **Data Loading**: `AdminDashboardController` fetches:
    -   Total Inventory, Users, Active Sales (Today, Week, Month, Year).
    -   Recent Orders data.
    -   Chart data (Revenue Trend, Pipeline) passed as JSON to inline scripts.
-   **Visuals**: "Glassmorphism" cards display KPIs.
-   **Interactions**: View logic calculates month-over-month % change client-side.

**v2 Improvements:**
-   **API-Driven Dashboard**: Fetch chart data via asynchronous API calls (`/api/v2/admin/stats`) to improve initial page load speed.
-   **Real-time Updates**: Use WebSockets (Laravel Reverb or Pusher) to update "Recent Orders" and "Sales Today" without page refresh.

## 3. Product & Inventory Management
### 3.1. Products (`admin.products.index`)
**Current Flow (v1):**
-   **List View**: Displays Table of products (datatables).
-   **Add Product**: Opens a modal (`#addProductModal`) included in the index file.
-   **Edit Product**: **Critical Issue** - Renders a unique modal for *every* product row inside the `@foreach` loop.
-   **PDF Download**: Inline JS generates a PDF using `jspdf`.

**v2 Improvements:**
-   **Dynamic Modals**: Use a single "Add/Edit" modal component. When "Edit" is clicked, fetch product data via AJAX and populate the single modal.
-   **Server-Side Export**: Move PDF/Excel generation to the server/queue (e.g., `Laravel Excel`) to handle large catalogs without freezing the browser.

### 3.2. Inventory & Stock (`admin.inventory`)
**Current Flow (v1):**
-   View-only dashboard separating "Raw Materials", "Wholesale", and "Finished Goods".
-   Alerts for "Low Stock" calculated in Controller/View.

**v2 Improvements:**
-   **Stock Movement Logs**: Click on a distinct stock number to see a history of *why* it changed (e.g., "Order #123", "Restock #55", "Adjustment").
-   **Prediction**: Integrate the "Days Until Out" estimation logic directly into the main table with sorting capabilities.

### 3.3. Expense Management (Purchasing)
**Current Flow (v1):**
-   **Controller**: `AdminPurchaseController`.
-   **Workflow**: Admin creates a Purchase Order for a `Supplier`.
-   **Impact**: Automatically updates `Product` stock levels and recalculates unit costs.
-   **Payment Status**: Tracks `unpaid`, `partial`, and `paid` status for supplier payments.

**v2 Improvements:**
-   **Accounts Payable**: A dedicated view to see all "Unpaid" or "Partial" debts to suppliers.
-   **Restock Triggers**: A "Reorder" button on the Inventory Low Stock alert that pre-fills a Purchase Order for that supplier.

### 3.4. Asset & Machine Management
**Current Flow (v1):**
-   **Controller**: `AdminMachineController`.
-   **Data**: Tracks `brand`, `property_no`, cost, and status (`serviceable`, `non serviceable`, `return to supplier`).
-   **Purpose**: Asset tracking for FabLab equipment.

**v2 Improvements:**
-   **Maintenance Scheduler**: Set alerts for when machines need servicing based on `updated_at` or usage logs.
-   **Usage Tracking**: Link Machine usage to specific Custom Orders to calculate depreciation costs per job.

## 4. Order Management (`admin.orders.index`)
**Current Flow (v1):**
-   **List View**: Tabular list of orders with Status toggles (Pending, Completed, Cancelled).
-   **Actions**: Bulk Delete, Bulk Update, Bulk Approve.
-   **Notifications**: Button to manually sends SMS/Email updates.

**v2 Improvements:**
-   **Order Detail View**: Create a dedicated `/admin/orders/{id}` page instead of relying solely on modals or table rows.
-   **Workflow Automation**: "One-click Ship" that automatically updates stock, sends tracking SMS, and marks status as "Shipped".
-   **Status State Machine**: Enforce strict status transitions (cannot go from 'Pending' to 'Shipped' without 'Processing') in the backend.

## 5. Sales & POS (`admin.sale.pos`)
**Current Flow (v1):**
-   Simple interface for processing walk-in customers.
-   Interacts directly with `AdminPosController`.

**v2 Improvements:**
-   **Barcode Scanner Support**: optimize the UI input to always focus on the product search field for barcode readers.
-   **Receipt Printing**: Direct thermal printer integration or optimized Print CSS.

## 6. Customization & Design Approval
**Current Flow (v1):**
-   `admin.customized.index`: Lists products users have customized.
-   `personal_designs.index`: Lists user-uploaded designs for approval.

**v2 Improvements:**
-   **Visual Diffing**: Show the original product vs. the user's custom design side-by-side.
-   **Asset Management**: Integration with S3/Cloud storage for large design files instead of local storage.

## 7. Reports & Analytics
**Current Flow (v1):**
-   **Controller**: `ReportController`.
-   **Metrics**:
    -   **Financial**: Revenue, Expenses, Profit, Profit Margin %, Revenue Growth (MoM).
    -   **Inventory**: Inventory Turnover Ratio, Low Stock.
    -   **Partners**: Top Customers by spend, Supplier Purchase Volume.
-   **Filters**: Date Range, Status, Delivery Type.

**v2 Improvements:**
-   **Visual Dashboards**: Replace the current tabular reports with graphic visualizations (Bar/Line charts) for "Revenue vs Expenses".
-   **Exportability**: Add "Export to CSV/PDF" for all report generated tables.
-   **Audit Trail**: Track *who* changed stock levels or approved orders (currently missing in Reports).
-   **Role Based Access Control (RBAC)**: Define granular permissions (e.g., "Can Edit Product" vs "Can Only View Reports").

