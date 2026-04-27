# Admin System Flow

This document outlines the operational flow of the Inventory Monitoring System from an administrator's perspective.

## 1. Initial Setup Flow
1. **User Management**: Admins and Staff accounts are created or enabled.
2. **Supplier Management**: Vendors are registered.
3. **Product Management**: 
    - Categories are defined.
    - Products are added with SKU, price, and `low_stock_threshold`.
    - Products are **assigned to suppliers** (crucial for automation).

## 2. The Procurement Cycle (Restocking)
1. **Dashboard Alert**: Admin sees "Low Stock Items" count increase.
2. **Inventory Monitoring**: Admin reviews specific items falling below threshold.
3. **Validated PO**: Admin clicks "Create Validated PO" for a specific supplier.
4. **Purchase Order**:
    - The system pre-fills the order with needed items.
    - Admin reviews and sends the PO.
5. **Stock Update**: When the shipment arrives, Admin marks the PO as **Delivered**, automatically increasing system stock.

## 3. The Sales & Fulfillment Cycle
1. **Order Management**: Admin receives notification of a "Pending" order.
2. **Review Modal**: Admin verifies if stock is sufficient for the request.
3. **Approval**: 
    - If approved, status moves to "Processing".
    - System **automatically deducts** stock from inventory.
4. **Fulfillment**: Once shipped/completed, status is updated to "Completed".
5. **Revenue**: Total transaction value is reflected on the Dashboard metrics.

## 4. Continuous Monitoring
- **Dashboard**: Daily check of high-level stats.
- **System Alerts**: Real-time notifications of low stock or new users.
- **Profile**: Periodic updates to contact info or security settings.
