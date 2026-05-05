# Texture Integration Plan

Wire admin-managed textures into the customer customizer, with full inventory tracking and product-texture linkage.

## Decisions
- **Texture stock model:** Fixed 1 unit consumed per saved design (simplest, upgrade later if needed)
- **Scope:** M1 through M6. M7 (staff parity) is optional follow-up.
- **Folder pattern:** Continue clone-per-role — no shared components.

---

## M1 — Schema foundation ✅
- [x] Migration: extend `textures` table — add `cost_per_unit`, `stock_quantity`, `low_stock_threshold`, `unit`, `supplier_id`, `price_modifier`
- [x] Migration: create `product_textures` pivot table (`product_id`, `texture_id`)
- [x] Update `Texture` model — add fillable fields, `supplier()` belongsTo, `products()` belongsToMany
- [x] Update `Product` model — add `textures()` belongsToMany
- [x] Run migrations and verify schema

## M2 — Admin texture page becomes inventory-aware ✅
- [x] Update `Admin/TextureController::store` and `update` validation for new fields
- [x] Update `admin/textures/index.blade.php` grid to show stock and supplier
- [x] Extend `modal-add.blade.php` with cost / stock / threshold / unit / supplier / price_modifier inputs
- [x] Extend `modal-edit.blade.php` with the same fields
- [ ] Test add/edit flow end-to-end _(manual browser test pending)_

## M3 — Plug textures into the existing inventory system ✅
- [x] Update `Admin/InventoryController` to include low-stock textures alongside products and raw materials
- [x] Update `admin/inventory/index.blade.php` to render texture rows in the supplier-grouped suggestions
- [x] Migration: add `texture_id` column to `purchase_order_items` table
- [x] Update `PurchaseOrderItem` model — add `texture()` relation, fillable
- [x] Update `Admin/PurchaseOrderController` `index` (supplierItems builder), `store`, `updateStatus` to handle texture line items
- [x] Update PO create-modal item picker (`admin/purchase/index.blade.php` JS) to include textures in optgroups
- [x] Update `admin/purchase/show.blade.php` to render texture line items
- [x] **Mirror all of the above on Staff side** (clone-per-role parity)
- [ ] Test PO creation with textures + delivery flow incrementing texture stock _(manual browser test pending)_

## M4 — Product↔Texture assignment ✅
- [x] New view `admin/product/assign-textures.blade.php` (clone the assign-suppliers pattern)
- [x] New methods `Admin/ProductController::assignTextures` and `storeTextures`
- [x] Routes: `GET /admin/products/{id}/textures`, `POST /admin/products/{id}/textures`
- [x] Add "Manage Textures" action button in `admin/product/products.blade.php` table
- [ ] Test assigning textures to a product _(manual browser test pending)_

## M5 — Customer customizer wiring (the missing v1 link) ✅
- [x] `Customer/CustomizeController::index` — load `$product->textures` (or all textures if no product) and pass to view
- [x] Replace hardcoded squares in `customer/prod-customize/components/control-panel.blade.php:88-110` with `@foreach($textures as $texture)`
- [x] Extend `window.CustomizerConfig` in `scripts.blade.php` to include `textures` array (id + image_path)
- [x] Update `public/js/customizer/state.js` to track selected `texture_id`
- [x] Update `public/js/customizer/rendering.js` to apply `image_path` as a Three.js `MeshStandardMaterial.map`
- [x] Update `public/js/customizer/handlers.js` for new dynamic texture-click events
- [x] Update recipe save format to store `texture_id` instead of hardcoded name
- [x] Update model loaders (mug/t-shirt/shorts/umbrella) — replaced legacy `data-texture` lookup with `currentTextureId`
- [x] `calculateCustomPrice` now factors in texture's `price_modifier`
- [ ] Test selecting texture in customizer — confirm 3D model updates and design saves correctly _(manual browser test pending)_

## M6 — Pricing & stock decrement ✅
- [x] Update `CustomDesign::getCalculatedPriceAttribute` — add texture's `price_modifier` (lookup by `texture_id` from recipe)
- [x] Added `CustomDesign::texture()` helper that resolves the recipe's `texture_id` to a Texture record
- [x] Identify the order-fulfillment hook — `Admin/OrderController::review` (where status flips pending → approved/cancelled)
- [x] On approval: decrement texture `stock_quantity` by `item->quantity` (parallel to raw material decrement)
- [x] On cancel-from-approved: re-increment texture stock to revert
- [ ] Test end-to-end: design → cart → order → fulfillment → texture stock decremented _(manual browser test pending)_

## M7 — Staff parity ✅
- [x] Clone `Admin/TextureController` to `Staff/TextureController` (index + update only — no add/delete)
- [x] Clone `admin/textures/` views into `staff/textures/`, drop add/delete UI
- [x] Add staff routes for textures
- [x] Wire the (currently dead) Textures link in `staff/partials/sidebar.blade.php`

---

## Progress notes
_Add notes here as milestones complete — gotchas, schema decisions, things future-you should know._
