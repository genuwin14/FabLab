# P2 — Database Schema (Normalized)

This document describes the **normalized** database layout for the Inventory Monitoring System v2. The schema follows 3NF principles: facts live in exactly one table, all many-to-many relationships use pivot tables, and stock-bearing entities (products, raw materials, textures) each own their own inventory counters rather than duplicating them.

> **Convention**: Each domain table uses a meaningful primary key (`product_id`, `supplier_id`, etc.) instead of the generic `id`. Pivot tables use the default `id`. All FK columns cascade on delete unless noted.

---

## 1. Entity Relationship Overview

```
                                                  ┌──────────────┐
                                                  │   users      │
                                                  └──────┬───────┘
                                                         │ 1
                                                         │ creates / owns
                            ┌────────────────────────────┼────────────────────────────┐
                            │                            │                            │
                            ▼ M                          ▼ M                          ▼ M
                     ┌──────────────┐            ┌──────────────┐            ┌──────────────┐
                     │ purchase_    │            │   orders     │            │ custom_      │
                     │  orders      │            │              │            │  designs     │
                     └──────┬───────┘            └──────┬───────┘            └──────┬───────┘
                            │ 1                         │ 1                         │ 1
                            │                           │                           │
                            ▼ M                         ▼ M                         │
                     ┌──────────────┐            ┌──────────────┐                   │
                     │ purchase_    │            │ order_items  │◀──────────────────┘
                     │ order_items  │            └──────┬───────┘
                     └──────┬───────┘                   │ M
                            │ M                         │
        ┌───────────────────┼───────────────────┐       │
        │                   │                   │       │
        ▼                   ▼                   ▼       ▼
 ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
 │  products    │   │ raw_materials│   │   textures   │
 └──────┬───────┘   └──────┬───────┘   └──────┬───────┘
        │ M                 │ M                │ M
        │                   │                  │
        ▼                   │                  │
 ┌──────────────┐           │                  │
 │ categories   │           │                  │
 └──────────────┘           │                  │
        ▲ M                 │                  │
        │                   │                  │
        │  pivots: product_suppliers, product_raw_materials, product_textures
        │
 ┌──────────────┐
 │  suppliers   │◀── raw_materials.supplier_id, textures.supplier_id
 └──────────────┘
```

Separately tracked: `equipment`, `notifications`, `personal_access_tokens` (Sanctum), `sessions`, `password_reset_tokens`.

---

## 2. Core Tables

### 2.1 `users`
Stores **authentication** and **profile** fields in a single normalized record.

| Column | Type | Constraints / Notes |
| :--- | :--- | :--- |
| `id` | bigint, PK | auto-increment |
| `email` | string | UNIQUE, NOT NULL |
| `email_verified_at` | timestamp | nullable |
| `password` | string | nullable (Google-only users have no password) |
| `fullname` | string | nullable |
| `address` | string | nullable |
| `contact_number` | string | nullable, PH-format `+63…` |
| `phone_verified` | boolean | default `false` |
| `phone_verification_code` | string | nullable, 6-digit OTP |
| `degree`, `year`, `section`, `gender`, `photo` | string | nullable (student/profile metadata) |
| `status` | enum(`active`, `disabled`) | default `active` |
| `role` | enum(`customer`, `staff`, `admin`) | default `customer` |
| `notifications_enabled` | boolean | default `true` |
| `remember_token`, `timestamps` | — | — |

### 2.2 `categories`
| Column | Type | Notes |
| :--- | :--- | :--- |
| `category_id` | bigint, PK | |
| `name` | string | |
| `description` | text, nullable | |
| `timestamps` | — | |

### 2.3 `suppliers`
| Column | Type | Notes |
| :--- | :--- | :--- |
| `supplier_id` | bigint, PK | |
| `name` | string | |
| `contact_person`, `email`, `phone`, `address` | string, nullable | `email` UNIQUE |
| `timestamps` | — | |

### 2.4 `products`
The catalog item. Inventory counters live here, **not** in a separate stock table — keeping current state on the product avoids redundant joins for every read while still being 3NF (each counter is a single dependent fact of `product_id`).

| Column | Type | Notes |
| :--- | :--- | :--- |
| `product_id` | bigint, PK | |
| `sku` | string | UNIQUE |
| `name`, `description`, `brand` | text/string | |
| `price` | decimal(12, 2) | default 0 |
| `stock` | integer | default 0 — primary on-hand qty |
| `units_on_display` | integer | default 0 |
| `units_sponsored` | integer | default 0 |
| `units_damaged` | integer | default 0 |
| `units_consumed` | integer | default 0 |
| `category_id` | FK → `categories.category_id` | cascade |
| `department` | string, nullable | |
| `status` | string, nullable | e.g. `functional`, `defective` |
| `is_customizable` | boolean | default `false` |
| `low_stock_threshold` | integer, nullable | |
| `unit` | string | default `pcs` |
| `image` | longText, nullable | Base64 |
| `deleted_at`, `timestamps` | — | soft-deletes enabled |

### 2.5 `raw_materials`
| Column | Type | Notes |
| :--- | :--- | :--- |
| `raw_material_id` | bigint, PK | |
| `name` | string | |
| `image_path` | longText, nullable | |
| `supplier_id` | FK → `suppliers.supplier_id` | cascade |
| `cost_per_unit`, `stock_quantity` | decimal(10, 2) | |
| `low_stock_threshold` | decimal(10, 2) | default 10 |
| `units_on_display`, `units_sponsored`, `units_damaged`, `units_consumed` | decimal(10, 2) | default 0 |
| `unit` | string | default `pcs` |
| `description`, `department` | text/string, nullable | |
| `deleted_at`, `timestamps` | — | soft-deletes enabled |

### 2.6 `textures`
Textures are both **catalog modifiers** (a customizable surface for a product) and **inventory items** (consumed when used on a custom design).

| Column | Type | Notes |
| :--- | :--- | :--- |
| `texture_id` | bigint, PK | |
| `name` | string | |
| `image_path` | string, nullable | |
| `description`, `department` | text/string, nullable | |
| `supplier_id` | FK → `suppliers.supplier_id` | `nullOnDelete` |
| `cost_per_unit`, `stock_quantity`, `low_stock_threshold` | decimal(10, 2) | |
| `units_on_display`, `units_sponsored`, `units_damaged`, `units_consumed` | decimal(10, 2) | default 0 |
| `unit` | string | default `pcs` |
| `price_modifier` | decimal(10, 2) | default 0 — added to product price when applied |
| `deleted_at`, `timestamps` | — | soft-deletes enabled |

### 2.7 `equipment`
Stand-alone asset register (not part of the saleable catalog).

| Column | Type | Notes |
| :--- | :--- | :--- |
| `equipment_id` | bigint, PK | |
| `name`, `brand`, `property_no`, `notes` | string/text, nullable | |
| `date_acquired` | date, nullable | |
| `cost` | decimal(12, 2) | default 0 |
| `status` | string | default `Serviceable` |
| `deleted_at`, `timestamps` | — | soft-deletes |

---

## 3. Pivot / Relationship Tables

### 3.1 `product_suppliers`  *(M:N — product ↔ supplier with cost metadata)*
| Column | Type | Notes |
| :--- | :--- | :--- |
| `product_supplier_id` | bigint, PK | |
| `product_id` | FK → `products` | cascade |
| `supplier_id` | FK → `suppliers` | cascade |
| `cost` | decimal(12, 2) | acquisition cost from this supplier |
| `is_default` | boolean | preferred supplier for reordering |
| `min_order_qty` | integer, nullable | MOQ |
| `lead_time_days` | integer, nullable | |
| `timestamps` | — | |

### 3.2 `product_raw_materials`  *(M:N — BOM: which raw materials produce a product)*
| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint, PK | |
| `product_id` | FK → `products` | cascade |
| `raw_material_id` | FK → `raw_materials` | cascade |
| `quantity_required` | decimal(15, 2) | per finished product |
| `timestamps` | — | |

### 3.3 `product_textures`  *(M:N — which textures a customizable product accepts)*
| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint, PK | |
| `product_id` | FK → `products` | cascade |
| `texture_id` | FK → `textures` | cascade |
| `timestamps` | — | UNIQUE(`product_id`, `texture_id`) |

---

## 4. Transactional Tables

### 4.1 `orders` *(customer purchases)*
| Column | Type | Notes |
| :--- | :--- | :--- |
| `order_id` | bigint, PK | |
| `order_number` | string | UNIQUE, e.g. `ORDR-…` |
| `user_id` | FK → `users` | cascade — the buyer |
| `status` | enum(`pending`, `approved`, `processing`, `ready_for_pickup`, `completed`, `cancelled`) | default `pending` |
| `payment_reference` | string, nullable | |
| `reason` | text, nullable | rejection / cancellation reason |
| `total_amount` | decimal(12, 2) | |
| `timestamps` | — | |

### 4.2 `order_items`
| Column | Type | Notes |
| :--- | :--- | :--- |
| `order_item_id` | bigint, PK | |
| `order_id` | FK → `orders.order_id` | cascade |
| `product_id` | FK → `products` | cascade |
| `custom_design_id` | bigint, nullable | links to `custom_designs` if customized |
| `quantity` | integer | |
| `price` | decimal(12, 2) | snapshot at purchase time |

### 4.3 `purchase_orders` *(procurement from suppliers)*
| Column | Type | Notes |
| :--- | :--- | :--- |
| `purchase_order_id` | bigint, PK | |
| `po_number` | string | UNIQUE, e.g. `PO-2024-001` |
| `supplier_id` | FK → `suppliers` | cascade |
| `status` | enum(`draft`, `sent`, `confirmed`, `delivered`, `cancelled`) | default `draft` |
| `expected_delivery_date` | date, nullable | |
| `total_cost` | decimal(12, 2), nullable | |
| `created_by` | FK → `users` | cascade — Admin/Staff |
| `timestamps` | — | |

### 4.4 `purchase_order_items`
A single line can refer to **one of**: a finished product, a raw material, or a texture — modeled by nullable FKs (exactly one is set per row).

| Column | Type | Notes |
| :--- | :--- | :--- |
| `purchase_order_item_id` | bigint, PK | |
| `purchase_order_id` | FK → `purchase_orders` | cascade |
| `product_id` | FK → `products`, nullable | cascade |
| `raw_material_id` | FK → `raw_materials`, nullable | cascade |
| `texture_id` | FK → `textures`, nullable | cascade |
| `quantity` | integer | |
| `cost` | decimal(12, 2) | per-unit cost at order time |

### 4.5 `custom_designs`
| Column | Type | Notes |
| :--- | :--- | :--- |
| `custom_design_id` | bigint, PK | |
| `user_id` | FK → `users` | cascade |
| `product_id` | FK → `products`, nullable | cascade |
| `recipe` | JSON | structured customization (text, shapes, logos, features…) |
| `snapshot` | longText, nullable | rendered preview (Base64) |
| `timestamps` | — | |

---

## 5. System / Framework Tables

### 5.1 `notifications` *(Laravel default)*
| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | UUID, PK | |
| `type` | string | notification class name |
| `notifiable_type`, `notifiable_id` | morph | recipient (typically a User) |
| `data` | text | JSON payload |
| `read_at` | timestamp, nullable | |
| `timestamps` | — | |

### 5.2 `personal_access_tokens` *(Sanctum default)*
Standard Sanctum table — issues API tokens for the User model (`HasApiTokens`).

### 5.3 `sessions`
Standard Laravel session table (cookie-based session storage).

### 5.4 `password_reset_tokens`
Standard Laravel password-reset table (`email` PK, token, created_at). The custom 6-digit reset flow stores codes in the **session** rather than this table, but the table is kept for framework compatibility.

---

## 6. Normalization Notes

1. **No duplicated facts** — product price lives on `products`, not duplicated on every order line; `order_items.price` is intentionally a *snapshot* at sale time (historical record, not duplication).
2. **All M:N relationships use pivot tables** — `product_suppliers`, `product_raw_materials`, `product_textures` are textbook 3NF junctions.
3. **Inventory counters live on their owning entity** — products, raw materials, and textures each have their own `stock_*` and `units_*` columns. There is no separate "inventory" table mixing concerns.
4. **Polymorphic transactional refs** — `purchase_order_items` uses three nullable FKs (product / raw_material / texture) with a "exactly one is set" semantics. This avoids both denormalized JSON and a 3-way exclusive supertype/subtype that would explode joins.
5. **Soft deletes** — `products`, `raw_materials`, `textures`, `equipment` use `deleted_at` so historical orders/PO lines stay queryable even after a catalog item is retired.
