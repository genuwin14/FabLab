<!-- Add Raw Material Modal -->
<div class="modal fade" id="addRawMaterialModal" tabindex="-1" aria-labelledby="addRawMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="addRawMaterialModalLabel">Add New Raw Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.raw-materials.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="materialName" class="form-label small fw-bold">Material Name</label>
                        <input type="text" class="form-control rounded-3" id="materialName" name="name" required placeholder="e.g. Leather Fabric">
                    </div>
                    <div class="mb-3">
                        <label for="materialSupplier" class="form-label small fw-bold">Supplier</label>
                        <select class="form-select rounded-3" id="materialSupplier" name="supplier_id" required>
                            <option value="" disabled selected>Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="materialCost" class="form-label small fw-bold">Cost per Unit (₱)</label>
                            <input type="number" step="0.01" class="form-control rounded-3" id="materialCost" name="cost_per_unit" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="materialUnit" class="form-label small fw-bold">Unit</label>
                            <input type="text" class="form-control rounded-3" id="materialUnit" name="unit" required placeholder="e.g. meters">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="materialStock" class="form-label small fw-bold">Initial Stock</label>
                            <input type="number" step="0.01" class="form-control rounded-3" id="materialStock" name="stock_quantity" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="materialThreshold" class="form-label small fw-bold">Low Stock Threshold</label>
                            <input type="number" step="0.01" class="form-control rounded-3" id="materialThreshold" name="low_stock_threshold" value="10" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="materialDescription" class="form-label small fw-bold">Description (Optional)</label>
                        <textarea class="form-control rounded-3" id="materialDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Material</button>
                </div>
            </form>
        </div>
    </div>
</div>
