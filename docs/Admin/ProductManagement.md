# Product Management

This module allows administrators to manage the core catalog of the system, including categories and specific products.

## 1. Categories Page
**Location**: `admin/categories`

### Page Sections
- **Header**: Title "Categories" and "Add Category" button.
- **Categories Table**: Lists all groupings (e.g., Electronics, Raw Materials).
    - **Actions**: Edit (yellow pencil) and Delete (red trash).
- **Modals**:
    - **Add Category**: Form for Name and Description.
    - **Edit Category**: Pre-populated form to update existing category details.
    - **Delete Category**: Confirmation modal to prevent accidental removal.

## 2. Products Page
**Location**: `admin/products`

### Page Sections
- **Header**: Title "Products", "Export" (CSV/Excel), and "Add Product" buttons.
- **Filters & Search**:
    - **Search**: Text search by name or SKU.
    - **Category Filter**: Dropdown to filter by category.
    - **Stock Status Filter**: Filter by In Stock, Low Stock, or Out of Stock.
- **Products Table**:
    - **Product Info**: Image preview, Name, and Description.
    - **Stock Progress Bar**: Visual indicator of stock levels (Green for healthy, Red for low stock).
    - **Status Badges**: Indicators like "Active", "Maintenance", "Low Stock", or "Out of Stock".
    - **Actions**:
        - **Truck Icon**: Link to "Assign Suppliers" for that specific product.
        - **Pencil Icon**: Open Edit Product Modal.
        - **Trash Icon**: Open Delete Product Modal.

## 3. Assign Suppliers Page
**Location**: `admin/products/{id}/suppliers`

### Purpose
Links products to specific vendors. This is crucial for the **Inventory Monitoring** system to suggest automated Purchase Orders.

### Page Sections
- **Product Overview**: Displays the product being managed.
- **Supplier Assignment List**: Checkbox list of available suppliers.
- **Default Supplier Selection**: Designates the primary source for restocking.

## System Flow Integration
- **Catalog Setup**: Categories must be created before products can be categorized.
- **Supply Chain Link**: Products must be assigned to suppliers to enable the "Validated Purchase Order" flow in the Inventory Monitoring module.
