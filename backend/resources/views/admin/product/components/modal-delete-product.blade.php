<!-- Delete Product Modal -->
<div class="modal fade product-delete-modal" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered product-delete-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="product-delete-modal-header">
                <div class="product-delete-modal-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="deleteProductModalLabel">Delete Product</h5>
                <p class="text-white-50 small mb-0">This action cannot be undone</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 product-delete-modal-body">
                <p class="text-dark mb-0 text-center">
                    Are you sure you want to delete
                    <span id="deleteProductName" class="fw-bold text-dark"></span>?
                    All related data will be permanently removed.
                </p>
            </div>

            <!-- Footer with actions -->
            <div class="product-delete-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 product-delete-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <form id="deleteProductForm" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 product-delete-confirm-btn">
                        <i class="bi bi-trash me-2"></i>Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
