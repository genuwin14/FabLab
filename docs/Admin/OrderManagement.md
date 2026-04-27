# Order Management

This module handles the fulfillment and oversight of customer-placed orders.

## Page Sections

### 1. Filters & Search
- **Search**: Find orders by ID or Customer Name.
- **Export**: Generate reports of order history.
- **Status Filter**: Filter by Pending, Processing, Completed, or Cancelled.
- **Time Period Filter**: Quick filters for Today, This Week, or This Month.

### 2. Orders Table
- **Order ID**: Unique identifier for the transaction.
- **Ref No**: Payment reference number for verification.
- **Customer**: Name and avatar of the customer.
- **Total**: Final transaction amount.
- **Status Badge**: Visual status (e.g., Warning/Yellow for Pending, Success/Green for Completed).
- **Action (Review)**: Only available for "Pending" orders.

### 3. Order Review Modal
When an admin clicks "Review" on a pending order:
- **Order Info**: Displays Order Number and Customer Name.
- **Items Verification**:
    - Lists all products in the order.
    - **Stock Check**: Compares requested quantity against current system stock.
    - **Availability Badge**: Green "Available" or Red "Insufficient" badge based on stock levels.
- **Decision Actions**:
    - **Approve**: Move order to "Processing" status (deducts stock).
    - **Cancel**: Reject the order.

## System Flow Integration
- **Customer Interaction**: Orders originate from the Customer-facing portal.
- **Inventory Impact**: Upon approval in the Review Modal, the system automatically deducts the quantities from the Product stock levels.
- **Financial Flow**: Successful orders contribute to the "Total Revenue" metric on the Dashboard.
