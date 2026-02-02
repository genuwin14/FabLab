# 📊 Inventory Monitoring System v2 - Complete Database Schema

This document provides a unified view of the entire database schema for the v2 Inventory System. The tables are listed in their **Logical Migration Order** (from root dependencies to dependent transactions).

---

## 🟢 Phase 1: Foundations & Users
These tables must exist first as they are the parent references for almost everything else.

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
| `phone_verification_code`| String| Nullable | OTP for SMS. |
| `degree` | String | Nullable | User's academic program. |
| `year` | String | Nullable | Year level. |
| `section` | String | Nullable | Class section. |
| `gender` | String | Nullable | Gender identification. |
| `photo` | String | Nullable | Profile picture path. |
| `role` | Enum | Default: 'customer'| `admin`, `staff`, `customer`. |
| `remember_token` | String | Nullable | Session persistence. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

### 2. Table: `categories`
Used to differentiate between "Raw Materials", "Sale Items", and "Equipment".

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `category_id` | BigInt | PK, AI | Unique identifier. |
| `name` | String | Not Null | Category Name. |
| `description` | Text | Nullable | Optional details. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

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

### 4. Table: `products`
**Depends on**: `categories`, `suppliers`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `product_id` | BigInt | PK, AI | Unique identifier. |
| `sku` | String | Unique | SKU (Goods) or Property Number (Assets). |
| `name` | String | Not Null | Product or Machine Name. |
| `description` | Text | Nullable | Detailed information. |
| `brand` | String | Nullable | Product brand. |
| `price` | Decimal (12,2) | Default 0 | Selling price (0.00 for internal assets). |
| `stock` | Integer | Default: 0 | Current quantity. |
| `category_id` | ForeignID | Constrained | **FK**: Links to `categories(category_id)`. |
| `supplier_id` | ForeignID | Nullable | **FK**: Links to `suppliers(supplier_id)`. |
| `status` | String | Nullable | `functional`, `defective` (Assets only). |
| `is_customizable` | Boolean | Default: 0 | Can this be personalized? |
| `low_stock_threshold` | Integer | Nullable | Minimum stock level warning. |
| `unit` | String | Default: 'pcs' | Measurement unit. |
| `cost` | Decimal (12,2) | Nullable | Acquisition cost. |
| `image` | LongText | Nullable | Photo (base64). |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record last update. |

---

## 🛒 Phase 3: Sales & Transactions
Handling customer orders and point-of-sale events.

### 5. Table: `orders`
**Depends on**: `users`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `order_number` | String | Unique | e.g., ORDR-2024-001. |
| `user_id` | ForeignID | Constrained | **FK**: The buyer. |
| `status` | Enum | Default: 'pending' | `pending`, `processing`, `completed`, `cancelled`. |
| `total_amount` | Decimal (12,2)| Not Null | Final transaction price. |

### 6. Table: `order_items`
**Depends on**: `orders`, `product`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `order_id` | ForeignID | Constrained | **FK**: Parent order. |
| `product_id` | ForeignID | Constrained | **FK**: Item purchased. |
| `quantity` | Integer | Not Null | Amount purchased. |
| `price` | Decimal (12,2)| Not Null | Snapshot price. |

---

## 🎨 Phase 4: Fablab Customization
Specialized tables for personalized product workflows.

### 7. Table: `textures`
Available patterns for the customization tool.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `name` | String | Not Null | "Matte", "Glossy", etc. |
| `image` | LongText | Not Null | Base64 pattern. |

### 8. Table: `customized_products`
**Depends on**: `product`, `users`, `orders`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `product_id` | ForeignID | Constrained | Base product. |
| `user_id` | ForeignID | Constrained | Customer. |
| `front_image`| LongText | Nullable | Design preview. |
| `status` | Enum | Default: 'pending' | `pending`, `approved`, `produced`. |

---

## 📜 Phase 5: Procurement & Logs
Backend logistics and the system-wide audit trail.

### 9. Table: `purchases`
**Depends on**: `suppliers`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `supplier_id`| ForeignID | Constrained | Source vendor. |
| `total_cost` | Decimal (12,2)| Not Null | Total investment. |

### 10. Table: `inventory_logs`
**Depends on**: `product`, `users`.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `product_id` | ForeignID | Constrained | Item moved. |
| `user_id` | ForeignID | Constrained | Facilitator. |
| `type` | Enum | Not Null | `in`, `out`, `adjustment`. |
| `quantity` | Integer | Not Null | Units moved. |
| `reason` | String | Nullable | Context (e.g., "Order #123"). |
