<!-- Edit Raw Material Modal -->
<div class="modal fade" id="editRawMaterialModal" tabindex="-1" aria-labelledby="editRawMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="editRawMaterialModalLabel">Edit Raw Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRawMaterialForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editMaterialName" class="form-label small fw-bold">Material Name</label>
                        <input type="text" class="form-control rounded-3" id="editMaterialName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editMaterialSupplier" class="form-label small fw-bold">Supplier</label>
                        <select class="form-select rounded-3" id="editMaterialSupplier" name="supplier_id" required>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editMaterialCost" class="form-label small fw-bold">Cost per Unit (₱)</label>
                            <input type="number" step="0.01" class="form-control rounded-3" id="editMaterialCost" name="cost_per_unit" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editMaterialUnit" class="form-label small fw-bold">Unit</label>
                            <input type="text" class="form-control rounded-3" id="editMaterialUnit" name="unit" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editMaterialStock" class="form-label small fw-bold">Stock Quantity</label>
                            <input type="number" step="0.01" class="form-control rounded-3" id="editMaterialStock" name="stock_quantity" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editMaterialThreshold" class="form-label small fw-bold">Low Stock Threshold</label>
                            <input type="number" step="0.01" class="form-control rounded-3" id="editMaterialThreshold" name="low_stock_threshold" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editMaterialDescription" class="form-label small fw-bold">Description (Optional)</label>
                        <textarea class="form-control rounded-3" id="editMaterialDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Material</button>
                </div>
            </form>
        </div>
    </div>
</div>
