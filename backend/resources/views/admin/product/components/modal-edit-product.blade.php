<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <!-- Left Column: Image & Settings -->
                        <div class="col-lg-4 border-end">
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block w-100">
                                    <div class="ratio ratio-1x1 bg-light rounded-4 shadow-sm border overflow-hidden"
                                        style="background-image: url('{{ asset('img/FABLAB-LOGO.png') }}'); background-size: cover; background-position: center;"
                                        id="editImagePreview">
                                        <div class="d-flex align-items-center justify-content-center h-100"
                                            id="editImagePlaceholder">
                                            <i class="bi bi-camera text-muted fs-1 opacity-50"></i>
                                        </div>
                                    </div>
                                    <label
                                        class="btn btn-primary rounded-circle position-absolute bottom-0 end-0 m-3 shadow"
                                        style="cursor: pointer;">
                                        <i class="bi bi-pencil-fill"></i>
                                        <input type="file" name="image_file" class="d-none"
                                            onchange="previewImage(this, 'editImagePreview', 'editImagePlaceholder')">
                                    </label>
                                </div>
                                <div class="small text-muted mt-2">Product Image</div>
                            </div>

                            <!-- Additional Info & Settings -->
                            <div class="d-flex flex-column gap-3">
                                <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="editIsCustomizable"
                                            name="is_customizable">
                                        <label class="form-check-label small fw-bold"
                                            for="editIsCustomizable">Customizable
                                            Product</label>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                    <textarea name="description" id="editDescription"
                                        class="form-control bg-light border-0 rounded-3 px-3 py-2" rows="5"
                                        placeholder="Enter detailed product description..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Core Data & Pricing -->
                        <div class="col-lg-8">
                            <div class="row g-3">
                                <!-- Core Identity -->
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Product Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="editName"
                                        class="form-control rounded-3 bg-light border-0 px-3 py-2 fw-bold text-dark fs-5"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">SKU / Code <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="sku" id="editSku"
                                        class="form-control rounded-3 bg-light border-0 px-3 font-monospace" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Category <span
                                            class="text-danger">*</span></label>
                                    <select name="category_id" id="editCategoryId"
                                        class="form-select rounded-3 bg-light border-0 px-3" required>
                                        <option disabled value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <hr class="text-muted opacity-25 my-1">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                                    <input type="text" name="brand" id="editBrand"
                                        class="form-control rounded-3 bg-light border-0 px-3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                    <select name="status" id="editStatus"
                                        class="form-select rounded-3 bg-light border-0 px-3">
                                        <option value="active">Active</option>
                                        <option value="functional">Functional</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="broken">Broken / Defective</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Supplier</label>
                                    <select name="supplier_id" id="editSupplierId"
                                        class="form-select rounded-3 bg-light border-0 px-3">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mt-4 mb-2">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Pricing & Inventory</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Selling
                                        Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0 text-muted">₱</span>
                                        <input type="number" step="0.01" name="price" id="editPrice"
                                            class="form-control bg-light border-0 fw-bold text-success" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Cost Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0 text-muted">₱</span>
                                        <input type="number" step="0.01" name="cost" id="editCost"
                                            class="form-control bg-light border-0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                                    <select name="unit" id="editUnit"
                                        class="form-select rounded-3 bg-light border-0 px-3" required>
                                        <option value="pcs">Pieces (pcs)</option>
                                        <option value="set">Set</option>
                                        <option value="box">Box</option>
                                        <option value="roll">Roll</option>
                                        <option value="ream">Ream</option>
                                        <option value="kg">Kg</option>
                                        <option value="meter">Meter</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Current
                                        Stock</label>
                                    <input type="number" name="stock" id="editStock"
                                        class="form-control rounded-3 bg-light border-0 px-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Low Stock
                                        Alert</label>
                                    <input type="number" name="low_stock_threshold" id="editLowStock"
                                        class="form-control rounded-3 bg-light border-0 px-3">
                                </div>

                                <div class="col-12 mt-4 text-end">
                                    <button type="button" class="btn btn-light rounded-pill px-4 me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update
                                        Product</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>