# Product Customization Implementation Plan (Customer Side)

This document outlines the phases and specific tasks required to implement the "Save" and "Add to Cart" functionality for customized products.

## Phase 1: Infrastructure & Data Persistence (Normalized Architecture)

### 1.1 Database Schema Update
- [x] **Custom Designs Table**: Create a dedicated `custom_designs` table to store the "Recipe" and "Snapshot".
- [x] **Order Items Table**: Modify the existing `order_items` migration to include a `custom_design_id` foreign key (nullable).
- [x] Perform `php artisan migrate:fresh` to apply the normalized structure.

### 1.2 Model Updates
- [x] Create `CustomDesign` model with relationships to `User` and `Product`.
- [x] Update `OrderItem` model to include `custom_design_id` in fillable and establish the `belongsTo` relationship with `CustomDesign`.

---

## Phase 2: Customizer Frontend (JavaScript)

### 2.1 Design Serializer ("The Recipe")
- [x] Create a JS function to gather current `textElements`, `shapeElements`, and `logoElements` into a clean JSON object.
- [x] Include base style (mug/t-shirt), color, and size in the recipe.

### 2.2 Snapshot Capture
- [x] Create a JS function to capture the Three.js canvas as a Base64 PNG image.
- [x] Ensure `preserveDrawingBuffer: true` is enabled in the renderer (Done).

### 2.3 AJAX Payload Update
- [x] Modify the "Add to Cart" button click event in `scripts.blade.php`.
- [x] Update payload to include:
    - `product_id`
    - `quantity`
    - `custom_recipe` (The serialized JSON)
    - `custom_snapshot` (The Base64 image)

---

## Phase 3: Backend Cart Logic (`CartController`)

### 3.1 Customization Handling in `add()`
- [x] Update `CartController@add` to receive customization data.
- [x] **Data Persistence**:
    1. Check if design already exists (exact recipe match for this user) or always create new.
    2. Create/Retrieve a `CustomDesign` record.
    3. Add the item to the session-based cart, including the `custom_design_id`.
- [x] Store only the `custom_design_id` in the cart session (normalized) instead of the full recipe.

### 3.2 Snapshot Storage (Optional)
- [x] Decided: Should snapshots be stored as files in `storage/app/public/custom_designs/` or as Base64 in the session? (Recommendation: Store as files to keep session size small). - *Note: Currently stored as LongText in DB for simplicity in this V2 prototype.*

---

## Phase 4: Cart & Checkout UI

### 4.1 Cart Page Updates
- [x] Modify `customer/cart/index.blade.php` to detect custom items.
- [x] Display the custom snapshot image instead of the default product image where applicable.
- [ ] Add a "View Details" or "Edit Design" link for custom items.

### 4.2 Checkout Realization
- [x] Update `CartController@checkout` to transfer the `custom_recipe` and `custom_image` from the session to the new `OrderItem` records in the database.

---

## Phase 5: Dynamic Pricing Engine

### 5.1 Real-time UI Calculation (JS)
- [x] Implement `calculateCustomPrice()` in `scripts.blade.php` to run on every state change.
- [x] Define pricing rules (Example):
    - Base Price: From Product DB
    - Text Element: +₱50 each
    - Shape Element: +₱30 each
    - Logo Element: +₱150 each
    - Premium Features (LED): +₱500
- [x] Update the price display in the Control Panel sidebar dynamically.

### 5.2 Server-side Price Validation (Laravel)
- [x] Implement a `calculateCustomPrice()` helper in the backend (Service or Controller).
- [x] Re-calculate price in `CartController@add` based on the received `custom_recipe`.
- [x] **Security**: Ensure the final total used for the order is calculated server-side, never trusting the JS-calculated price.

---

## Next Steps (Staff Side - Future Phase)
- Implement Admin Order View to display the custom snapshot.
- Implement "Open in 3D" button for staff to inspect the design.
- Create high-resolution export for manufacturing.
