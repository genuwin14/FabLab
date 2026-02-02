# Product & Asset System Schema (Unified)

This document details the **Unified** `product` table and its **critical dependencies**. Since strictly relational databases (like MySQL/PostgreSQL) require parent tables to exist before they can be referenced by Foreign Keys, the **Dependencies** listed below must be created **BEFORE** the `product` table.

## 🔗 Dependencies (Create These First)

### 1. Table: `categories`
Used to categorize items into groups (e.g., "Raw Materials", "Sale Items", "Equipment").

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `name` | String | Not Null | Category Name (e.g., "Mugs"). |
| `description` | Text | Nullable | Optional details about the category. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record update. |

**Migration Order**: 1 (High Priority)

---

### 2. Table: `suppliers`
Details the vendors or sources for both Saleable Goods and Assets.

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `name` | String | Not Null | Company or Individual Name. |
| `contact_person` | String | Nullable | Key contact point. |
| `email` | String | Unique, Nullable | Contact email address. |
| `phone` | String | Nullable | Contact phone number. |
| `address` | String | Nullable | Physical address/Office location. |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record update. |

**Migration Order**: 1 (High Priority)

---

## 📦 Main Table: `product`
**Depends on**: `categories` (`category_id`), `suppliers` (`supplier_id`)

| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, AI | Unique identifier. |
| `sku` | String | Unique | **Dual Purpose**: Stock Keeping Unit (Goods) OR Property Number (Assets). |
| `name` | String | Not Null | Product or Machine Name. |
| `description` | Text | Nullable | Description or detailed specs. |
| `brand` | String | Nullable | **Start v2**: Brand/Make of item. |
| `price` | Decimal (12,2) | Default 0 | Selling price. Set to `0.00` for Internal/Fixed Assets. |
| `stock` | Integer | Default: 0 | Quantity available or '1' for unique assets. |
| `category_id` | ForeignID | Constrained | **FK**: Links to `categories`. |
| `supplier_id` | ForeignID | Nullable | **FK**: Links to `suppliers`. |
| `status` | Enum | Nullable | **Start v2**: `functional`, `defective` (Assets only). |
| `is_customizable` | Boolean | Default: 0 | Can this product be personalized? |
| `low_stock_threshold`| Integer | Nullable | Alert level for restocking. |
| `unit` | String | Default: 'pcs' | e.g., pcs, box, set. |
| `cost` | Decimal (12,2) | Nullable | Purchase cost. |
| `image` | LongText | Nullable | Product Photo (base64). |
| `created_at` | Timestamp | | Record creation. |
| `updated_at` | Timestamp | | Record update. |

---
