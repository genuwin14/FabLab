# Staff User Flow (Current v1 & Planned v2)

## 1. Authentication & Access Scope
**Current Flow (v1):**
1.  **Login**: Staff logs in via standard `/login`.
2.  **Role Check**: Middleware ensures user is `staff`.
3.  **Dashboard**: Redirects to `staff.dashboard`.

**v2 Improvements:**
-   **Access Logs**: Monitor when staff log in/out for shift tracking.
-   **Restricted Views**: Ensure Staff *cannot* access `/admin/*` routes even if they guess the URL (already handled by middleware, but UI should also hide admin links).

## 2. Dashboard (`staff.dashboard`)
**Current Flow (v1):**
-   Similar to Admin but scoped.
-   Shows "Orders Today", "Pending Orders", and "Low Stock" alerts.
-   **Income visibility**: Shows "Income Today" and "Total Cost".

**v2 Improvements:**
-   **Task List**: A "To-Do" widget (e.g., "Pack Order #104", "Restock Item B").
-   **Permissions**: Option to hide sensitive financial data (Profit/Cost) if the Staff member is just a packer/inventory handler.

## 3. Order Processing (Operational)
**Current Flow (v1):**
-   **Order List** (`staff.orders.index`): View incoming orders.
-   **Actions**: Change Status (Pending -> Processing -> Completed).
-   **Bulk Actions**: Bulk update statuses.

**v2 Improvements:**
-   **Barcode Picking**: Use scanner to "beep" items into a "Packed" state before marking the order as ready.
-   **Packing Slips**: Generate printable packing slips directly from the Order List.

## 4. Inventory & Supply Interaction
**Current Flow (v1):**
-   **Stock Management**: `staff.inventories.index`, `staff.materials`.
-   **Supply/Expenses**: Staff accesses `/paysupply` (mapped to `StaffSupplierController`) to record purchasing stock from suppliers.
-   **Machine Maintenance**: Staff accesses `/machine` (`StaffMachineController`) to update machine status (e.g., mark as "Under Maintenance").

**v2 Improvements:**
-   **Stock Request Workflow**: Instead of allowing Staff to *Create* purchases directly, implement a "Request Restock" flow that Admin approves.
-   **Audit Mode**: A simplified view for doing physical inventory counts (e.g., "System says 10, I count 8").
-   **Maintenance Logs**: A dedicated log for machine repairs rather than just changing a status dropdown.

## 5. POS (staff.sale.index)
**Current Flow (v1):**
-   Staff operates the Point of Sale for physical transactions.
-   Select products -> Calculate Total -> Pay.

**v2 Improvements:**
-   **Shift Management**: "Open Register" and "Close Register" features to track cash drawer discrepancies at the start/end of a shift.
-   **Offline Mode**: Enable basic POS functionality (via Service Workers/PWA) even if internet drops.

## 6. Customization Handling
**Current Flow (v1):**
-   `staff.customize.index`: View requests.
-   `personal_design.index`: View designs.

**v2 Improvements:**
-   **Production Pipeline**: A Trello-like board for custom orders: "Design Recieved" -> "Printing" -> "Quality Check" -> "Ready".

