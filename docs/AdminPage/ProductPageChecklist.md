# Admin Product Page Phase Checklist (v2)

Based on the analysis of `docs/AdminFlow.md` and the requirements for specific v2 improvements, the following checklist outlines the necessary features and implementation steps for the Admin Product Page.

## 1. Core Features & Functional Requirements

### 1.1. Product Listing (Index)
- [ ] **Product Table**: Implement a custom responsive table to list products (Avoid using DataTables library).
    - Columns: Image thumb, Product Name, SKU/Code, Category, Price, Stock Level, Status (Active/Inactive), Actions.
- [ ] **Search & Filtering**:
    - [ ] Real-time search by Product Name or SKU.
    - [ ] Filter by Category.
    - [ ] Filter by Stock Status (Low Stock, Out of Stock, In Stock).
- [ ] **Pagination**: Server-side pagination for performance with large datasets.

### 1.2. Product Management (CRUD)
- [ ] **Add Product**:
    - [ ] Trigger a **single dynamic modal** from a floating action button or primary header button.
    - [ ] Form Validation (Client-side & Server-side).
    - [ ] Image Upload preview.
- [ ] **Edit Product**:
    - [ ] **Refactor**: Remove unique modals for each row (v1 issue).
    - [ ] **AJAX Fetch**: Click "Edit" -> Fetch product details -> Populate the *shared* Add/Edit modal.
- [ ] **Delete Product**:
    - [ ] "Soft Delete" confirmation (SweetAlert2 or styled modal).

### 1.3. Advanced Features (v2 Specific)
- [ ] **Server-Side Export**:
    - [ ] Implement "Export to PDF" using a server-side queue/job (Laravel Excel or DomPDF) instead of inline JS.
    - [ ] Implement "Export to Excel".
- [ ] **Bulk Actions**:
    - [ ] Select multiple rows to Bulk Delete or Bulk Update Status.

## 2. UI/UX & Design (Aesthetics)
- [ ] **Glassmorphism Theme**: Apply the application's glassmorphism tokens to the table container and modals.
- [ ] **Micro-interactions**:
    - [ ] Hover effects on table rows.
    - [ ] Smooth transitions for modal open/close.
    - [ ] Loading skeletons/spinners while fetching data for Edit.
- [ ] **Badges & Indicators**:
    - [ ] Color-coded badges for Stock Level (Red = Low, Green = Good).
    - [ ] Status indicators.

## 3. Technical Implementation Steps

### Phase 1: Setup & Structure
- [ ] Create/Update `backend/resources/views/admin/product/index.blade.php`.
- [ ] Create `backend/resources/views/admin/product/components/modal-form.blade.php` (Reusable modal).
- [ ] Ensure `AdminProductController` handles JSON responses for AJAX requests (store, update, fetch).

### Phase 2: Dynamic Logic
- [ ] Write JavaScript (Vanilla or Alpine.js) to handle:
    - [ ] Modal toggling.
    - [ ] Form submission (AJAX).
    - [ ] Populating form data on logical "Edit".
    - [ ] Refreshing the table without page reload (optional but recommended for v2).

### Phase 3: Integration & Testing
- [ ] Verify image upload paths and storage.
- [ ] Test form validation errors display.
- [ ] Verify "Server-Side Export" generates correct files.
- [ ] Mobile responsiveness check.
