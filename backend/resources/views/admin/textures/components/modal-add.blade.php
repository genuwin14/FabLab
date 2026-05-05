<!-- Add Texture Modal -->
<div class="modal fade" id="addTextureModal" tabindex="-1" aria-labelledby="addTextureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="addTextureModalLabel">Add New Texture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.textures.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="textureName" class="form-label small fw-bold">Texture Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="textureName" name="name" required placeholder="e.g. Matte Finish">
                        </div>
                        <div class="col-md-6">
                            <label for="textureSupplier" class="form-label small fw-bold">Supplier</label>
                            <select class="form-select rounded-3" id="textureSupplier" name="supplier_id">
                                <option value="">-- No Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="textureDescription" class="form-label small fw-bold">Description (Optional)</label>
                            <textarea class="form-control rounded-3" id="textureDescription" name="description" rows="2" placeholder="Describe the visual characteristics..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="textureCost" class="form-label small fw-bold">Cost per Unit (₱)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="textureCost" name="cost_per_unit" value="0">
                        </div>
                        <div class="col-md-6">
                            <label for="texturePriceModifier" class="form-label small fw-bold">Price Modifier (₱)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="texturePriceModifier" name="price_modifier" value="0" placeholder="Extra cost when applied to a design">
                        </div>

                        <div class="col-md-4">
                            <label for="textureStock" class="form-label small fw-bold">Stock Quantity</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="textureStock" name="stock_quantity" value="0">
                        </div>
                        <div class="col-md-4">
                            <label for="textureThreshold" class="form-label small fw-bold">Low Stock Alert</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="textureThreshold" name="low_stock_threshold" value="10">
                        </div>
                        <div class="col-md-4">
                            <label for="textureUnit" class="form-label small fw-bold">Unit</label>
                            <input type="text" class="form-control rounded-3" id="textureUnit" name="unit" value="pcs" placeholder="pcs / m / kg">
                        </div>

                        <div class="col-12">
                            <label for="textureImage" class="form-label small fw-bold">Texture Image (Preview)</label>
                            <input type="file" class="form-control rounded-3" id="textureImage" name="image_file" accept="image/*" onchange="previewImage(this, 'addTexturePreview')">
                            <div class="mt-2 text-center">
                                <img id="addTexturePreview" src="#" alt="Preview" class="rounded-3 d-none shadow-sm" style="max-height: 150px; max-width: 100%;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Texture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
