<!-- Edit Texture Modal -->
<div class="modal fade" id="editTextureModal" tabindex="-1" aria-labelledby="editTextureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="editTextureModalLabel">Edit Texture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTextureForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editTextureName" class="form-label small fw-bold">Texture Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="editTextureName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editTextureSupplier" class="form-label small fw-bold">Supplier</label>
                            <select class="form-select rounded-3" id="editTextureSupplier" name="supplier_id">
                                <option value="">-- No Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="editTextureDescription" class="form-label small fw-bold">Description (Optional)</label>
                            <textarea class="form-control rounded-3" id="editTextureDescription" name="description" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="editTextureCost" class="form-label small fw-bold">Cost per Unit (₱)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="editTextureCost" name="cost_per_unit">
                        </div>
                        <div class="col-md-6">
                            <label for="editTexturePriceModifier" class="form-label small fw-bold">Price Modifier (₱)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="editTexturePriceModifier" name="price_modifier" placeholder="Extra cost when applied to a design">
                        </div>

                        <div class="col-md-4">
                            <label for="editTextureStock" class="form-label small fw-bold">Stock Quantity</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="editTextureStock" name="stock_quantity">
                        </div>
                        <div class="col-md-4">
                            <label for="editTextureThreshold" class="form-label small fw-bold">Low Stock Alert</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="editTextureThreshold" name="low_stock_threshold">
                        </div>
                        <div class="col-md-4">
                            <label for="editTextureUnit" class="form-label small fw-bold">Unit</label>
                            <input type="text" class="form-control rounded-3" id="editTextureUnit" name="unit">
                        </div>

                        <div class="col-12">
                            <label for="editTextureImage" class="form-label small fw-bold">Update Image (Optional)</label>
                            <input type="file" class="form-control rounded-3" id="editTextureImage" name="image_file" accept="image/*">
                            <div class="mt-2 text-center">
                                <img id="editTexturePreview" src="#" alt="Preview" class="rounded-3 shadow-sm" style="max-height: 150px; max-width: 100%;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Texture</button>
                </div>
            </form>
        </div>
    </div>
</div>
