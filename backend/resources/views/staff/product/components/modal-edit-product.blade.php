<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="p-4">
                        <div class="row g-4">
                            <!-- Left Column: Image & Settings -->
                            <div class="col-lg-4 border-end">
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block w-100">
                                        <div class="ratio ratio-1x1 bg-light rounded-4 shadow-sm border overflow-hidden"
                                            id="editImagePreview" style="background-size: cover; background-position: center;">
                                            <div class="d-flex align-items-center justify-content-center h-100" id="editImagePlaceholder">
                                                <i class="bi bi-camera text-muted fs-1 opacity-50"></i>
                                            </div>
                                        </div>
                                        <label class="btn btn-primary rounded-circle position-absolute bottom-0 end-0 m-3 shadow" style="cursor: pointer;">
                                            <i class="bi bi-pencil-fill"></i>
                                            <input type="file" name="image_file" class="d-none" onchange="previewImage(this, 'editImagePreview', 'editImagePlaceholder')">
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-check form-switch mb-3 p-3 border rounded-3 bg-light bg-opacity-50">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="editIsCustomizable" name="is_customizable">
                                        <label class="form-check-label small fw-bold" for="editIsCustomizable">Customizable Product</label>
                                    </div>
                                    <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                    <textarea name="description" id="editDescription" class="form-control bg-light border-0 rounded-3 px-3 py-2" rows="4"></textarea>
                                </div>
                            </div>

                            <!-- Right Column: Core Data -->
                            <div class="col-lg-8">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="editName" class="form-control rounded-3 bg-light border-0 px-3 py-2 fw-bold text-dark fs-5" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">SKU / Code <span class="text-danger">*</span></label>
                                        <input type="text" name="sku" id="editSku" class="form-control rounded-3 bg-light border-0 px-3 font-monospace" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" id="editCategoryId" class="form-select rounded-3 bg-light border-0 px-3" required>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                                        <input type="text" name="brand" id="editBrand" class="form-control rounded-3 bg-light border-0 px-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Price</label>
                                        <input type="number" step="0.01" name="price" id="editPrice" class="form-control rounded-3 bg-light border-0 px-3 fw-bold text-success" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                        <select name="status" id="editStatus" class="form-select rounded-3 bg-light border-0 px-3">
                                            <option value="active">Active</option>
                                            <option value="functional">Functional</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="broken">Broken / Defective</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Unit</label>
                                        <input type="text" name="unit" id="editUnit" class="form-control rounded-3 bg-light border-0 px-3" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Stock</label>
                                        <input type="number" name="stock" id="editStock" class="form-control rounded-3 bg-light border-0 px-3" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Low Stock Alert</label>
                                        <input type="number" name="low_stock_threshold" id="editLowStock" class="form-control rounded-3 bg-light border-0 px-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
