# 📊 Inventory Monitoring System v2 – Complete Database Schema

This document provides a unified view of the entire database schema for the v2 Inventory System.  
The tables are listed in their **Logical Migration Order** (from root dependencies to dependent transactions).

---

## 🟢 Phase 1: Foundations & Users
These tables must exist first as they are the parent references for almost everything else.

---

### 1. Table: `users`
Core authentication and role management.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `email` | String | Unique | Login credential. |
| `email_verified_at` | Timestamp | Nullable | Email verification date. |
| `password` | String | Not Null | Hashed password. |
| `fullname` | String | Nullable | User's full name. |
| `address` | String | Nullable | Physical address. |
| `contact_number` | String | Nullable | Primary contact for alerts. |
| `phone_verified` | Boolean | Default: 0 | SMS verification status. |
| `phone_verification_code` | String | Nullable | OTP for SMS. |
| `degree` | String | Nullable | User's academic program. |
| `year` | String | Nullable | Year level. |
| `section` | String | Nullable | Class section. |
| `gender` | String | Nullable | Gender identification. |
| `photo` | String | Nullable | Profile picture path. |
| `role` | Enum | Default: 'customer' | `admin`, `staff`, `customer`. |
| `remember_token` | String | Nullable | Session persistence. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

---

### 2. Table: `categories`
Used to differentiate between "Raw Materials", "Sale Items", and "Equipment".

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `category_id` | BigInt | PK, AI | Unique identifier. |
| `name` | String | Not Null | Category Name. |
| `description` | Text | Nullable | Optional details. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

---

### 3. Table: `suppliers`
Details the vendors for both Saleable Goods and Assets.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `supplier_id` | BigInt | PK, AI | Unique identifier. |
| `name` | String | Not Null | Company Name. |
| `contact_person` | String | Nullable | Key contact point. |
| `email` | String | Unique, Nullable | Contact email. |
| `phone` | String | Nullable | Contact phone number. |
| `address` | String | Nullable | Supplier address. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

---

## 📦 Phase 2: Core Product System
The unified storage for all physical items in the system.

---

### 4. Table: `products`
**Depends on**: `categories`.

> Products are **supplier-agnostic**.  
> Suppliers are linked separately to allow multiple vendors per product.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `product_id` | BigInt | PK, AI | Unique identifier. |
| `sku` | String | Unique | SKU (Goods) or Property Number (Assets). |
| `name` | String | Not Null | Product or Machine Name. |
| `description` | Text | Nullable | Detailed information. |
| `brand` | String | Nullable | Product brand. |
| `price` | Decimal (12,2) | Default: 0 | Selling price (0.00 for internal assets). |
| `stock` | Integer | Default: 0 | Current quantity on hand. |
| `category_id` | ForeignID | Constrained | **FK**: Links to `categories(category_id)`. |
| `status` | String | Nullable | `functional`, `defective` (Assets only). |
| `is_customizable` | Boolean | Default: 0 | Can this be personalized? |
| `low_stock_threshold` | Integer | Nullable | Minimum stock level warning. |
| `unit` | String | Default: 'pcs' | Measurement unit. |
| `image` | LongText | Nullable | Photo (base64). |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

---

### 5. Table: `product_suppliers`
**Depends on**: `products`, `suppliers`.

> Defines supplier-specific pricing and fulfillment rules for products.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `product_id` | ForeignID | Constrained | **FK**: Linked product. |
| `supplier_id` | ForeignID | Constrained | **FK**: Linked supplier. |
| `cost` | Decimal (12,2) | Not Null | Supplier acquisition cost. |
| `is_default` | Boolean | Default: 0 | Preferred supplier for reordering. |
| `min_order_qty` | Integer | Nullable | Minimum order quantity (MOQ). |
| `lead_time_days` | Integer | Nullable | Expected delivery time. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

---

## 🧾 Phase 3: Purchasing & Restocking
Formal admin ↔ supplier interaction.

---

### 6. Table: `purchase_orders`
**Depends on**: `suppliers`, `users`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `po_number` | String | Unique | e.g., PO-2024-001 |
| `supplier_id` | ForeignID | Constrained | **FK**: Supplier fulfilling the order. |
| `status` | Enum | Default: 'draft' | `draft`, `sent`, `confirmed`, `delivered`, `cancelled`. |
| `expected_delivery_date` | Date | Nullable | Supplier ETA. |
| `total_cost` | Decimal (12,2) | Nullable | Total purchase value. |
| `created_by` | ForeignID | Constrained | **FK**: Admin who created the PO. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

---

### 7. Table: `purchase_order_items`
**Depends on**: `purchase_orders`, `products`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `purchase_order_id` | ForeignID | Constrained | **FK**: Parent purchase order. |
| `product_id` | ForeignID | Constrained | **FK**: Product ordered. |
| `quantity` | Integer | Not Null | Quantity ordered. |
| `cost` | Decimal (12,2) | Not Null | Cost per unit at time of order. |

---

## 🛒 Phase 4: Sales & Transactions
Handling customer orders and point-of-sale events.

---

### 8. Table: `orders`
**Depends on**: `users`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `order_number` | String | Unique | e.g., ORDR-2024-001. |
| `user_id` | ForeignID | Constrained | **FK**: The buyer. |
| `status` | Enum | Default: 'pending' | `pending`, `processing`, `completed`, `cancelled`. |
| `total_amount` | Decimal (12,2) | Not Null | Final transaction price. |

---

### 9. Table: `order_items`
**Depends on**: `orders`, `products`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `order_id` | ForeignID | Constrained | **FK**: Parent order. |
| `product_id` | ForeignID | Constrained | **FK**: Item purchased. |
| `quantity` | Integer | Not Null | Amount purchased. |
| `price` | Decimal (12,2) | Not Null | Snapshot price. |

---
