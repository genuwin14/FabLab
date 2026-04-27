# Inventory Monitoring

The Inventory Monitoring module is the "brain" of the system, providing automated insights into stock levels and restocking needs.

## Page Sections

### 1. Alert Banner
- **Function**: Displays an immediate warning if any products have fallen below their `low_stock_threshold`.
- **Content**: Summary count of products needing attention.

### 2. Reorder Suggestions (Grouped by Supplier)
The system automatically groups low-stock items based on their assigned suppliers.
- **Supplier Card**:
    - **Header**: Supplier Name and count of items needing reorder.
    - **Action**: "Create Validated PO" - A direct link that pre-fills a Purchase Order with all low-stock items from this specific supplier.
- **Items Table**:
    - **Product**: Name and SKU.
    - **Current**: Current stock level (highlighted in red if below threshold).
    - **Threshold**: The minimum stock level defined in the product settings.
    - **Status**: "Low Stock" or "Out of Stock" badges.

### 3. "No Default Supplier" Section
- **Purpose**: Identifies products that are low on stock but have no assigned supplier.
- **Action**: Directs the administrator back to the Product Management module to fix the assignment.

## System Flow Integration
- **Monitoring**: This page is the primary source of truth for the health of the inventory.
- **Procurement Flow**: Clicking "Create Validated PO" triggers the transition from Monitoring to the **Purchase Order** creation flow, ensuring that the restocking process is data-driven and efficient.
