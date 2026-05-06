<!-- Delete Supplier Modal -->
<div class="modal fade supplier-delete-modal" id="deleteSupplierModal" tabindex="-1"
    aria-labelledby="deleteSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered supplier-delete-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="supplier-delete-modal-header">
                <div class="supplier-delete-modal-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="deleteSupplierModalLabel">Delete Supplier</h5>
                <p class="text-white-50 small mb-0">This action cannot be undone</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 supplier-delete-modal-body">
                <p class="text-dark mb-0 text-center">
                    Are you sure you want to delete
                    <span id="deleteSupplierName" class="fw-bold text-dark"></span>?
                    All related data will be permanently removed.
                </p>
            </div>

            <!-- Footer with actions -->
            <div class="supplier-delete-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 supplier-delete-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <form id="deleteSupplierForm" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 supplier-delete-confirm-btn">
                        <i class="bi bi-trash me-2"></i>Delete Supplier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
