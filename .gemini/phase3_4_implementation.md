# Phase 3 & 4 Implementation: Inventory Monitoring & Purchasing

## Overview
Successfully implemented the Inventory Monitoring and Purchase Order system, allowing admins to track stock levels and seamlessly reorder from suppliers.

## Phase 3: Inventory Monitoring

### 1. Stock Monitoring Page
- **Route:** `/admin/inventory`
- **Controller:** `InventoryController`
- **Features:**
  - Displays products where `stock <= low_stock_threshold`.
  - **Smart Grouping:** Automatically groups low-stock items by their **Default Supplier**.
  - **Items without Supplier:** Grouped separately with a prompt to assign suppliers.
  - **Action:** "Create Validated PO" button for each supplier group.

## Phase 4: Purchase Orders

### 1. Purchase Order Management
- **Route:** `/admin/purchase`
- **Controller:** `PurchaseOrderController`
- **Components:**
  - **List View:** Status badges (Draft, Sent, Confirmed, Delivered), filtering by status/supplier (UI ready).
  - **Create PO Form:**
    - **Pre-filling:** When clicked from Inventory Monitoring, auto-fills products and quantities based on reorder needs.
    - **Locking:** Fills and locks the Supplier selection if creating from a suggestion.
    - **Dynamic Items:** Add/Remove rows, auto-calculate totals.
  - **Detail View:**
    - Full order details.
    - **Workflow Actions:** Buttons to transition status:
      - Draft -> Sent
      - Sent -> Confirmed
      - Confirmed -> Delivered
      - Cancel (if not delivered)
    - **Auto-Stock Update:** Marking as **Delivered** automatically increments product stock.

### 2. Database Models
- **PurchaseOrder:** `po_number`, `supplier_id`, `status`, `total_cost`, `created_by`.
- **PurchaseOrderItem:** `purchase_order_id`, `product_id`, `quantity`, `cost`.

## Workflow
1.  **Monitor:** Admin visits "Stock Monitoring".
2.  **Identify:** Sees 5 items low on stock from "Epson Supplier".
3.  **Action:** Clicks "Create Validated PO".
4.  **Create:** Redirects to PO Create form, pre-filled with those 5 items. Admin adjusts quantities if needed.
5.  **Save:** PO saved as "Draft".
6.  **Send:** Admin marks as "Sent".
7.  **Receive:** When items arrive, Admin marks as "Delivered".
8.  **Update:** System automatically adds quantities to Product Stock.

## Next Steps / Recommendations
- **Email Integration:** Send email to supplier when status is "Sent".
- **PDF Generation:** Generate PDF Purchase Order to attach to email.
- **Stock Movement Log:** Log these automatic increments to an `inventory_logs` table (Phase 5 extension).
