# Purchase Orders (Restocking)

Purchase Orders (POs) manage the procurement of stock from external suppliers.

## 1. PO List Page
**Location**: `admin/purchase`

### Page Sections
- **PO Number**: Unique tracking number for the supplier order.
- **Supplier**: Company name and item count.
- **Status**: Track progress (Draft -> Sent -> Confirmed -> Delivered -> Cancelled).
- **Total Cost**: The total amount to be paid to the supplier.
- **Action**: View details.

## 2. Create Purchase Order
**Location**: `admin/purchase/create`

### Features
- **Supplier Selection**: Choosing a supplier filters available products to only those assigned to them.
- **Dynamic Item Entry**: Add multiple products, specify quantities, and see real-time cost calculation.
- **Validation**: Ensures quantities are valid before submission.

## 3. PO Details View
**Location**: `admin/purchase/{id}`

### Page Sections
- **Status Timeline**: Shows the historical progress of the order.
- **Supplier Information**: Contact details and address for shipping/billing.
- **Items List**: Detailed breakdown of products ordered.
- **Finalization**: "Mark as Delivered" action, which triggers the automated stock increase.

## System Flow Integration
- **Replenishment**: The "Deliver" action on a Purchase Order is the primary mechanism for increasing system stock levels.
- **Automation**: Often triggered by the "Create Validated PO" button in the **Inventory Monitoring** module.
