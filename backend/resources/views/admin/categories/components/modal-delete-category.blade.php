<!-- Delete Category Modal -->
<div class="modal fade category-delete-modal" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered category-delete-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Header -->
            <div class="category-delete-modal-header">
                <div class="category-delete-modal-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="deleteCategoryModalLabel">Delete Category</h5>
                <p class="text-white-50 small mb-0">This action cannot be undone</p>
            </div>

            <!-- Confirmation Body -->
            <div class="modal-body p-4 category-delete-modal-body">
                <p class="text-dark mb-0 text-center">
                    Are you sure you want to delete
                    <span id="deleteCategoryName" class="fw-bold text-dark"></span>?
                    All related data will be permanently removed.
                </p>
            </div>

            <!-- Footer with actions -->
            <div class="category-delete-modal-footer">
                <button type="button" class="btn fw-semibold rounded-pill px-4 category-delete-cancel-btn"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <form id="deleteCategoryForm" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn fw-semibold rounded-pill px-4 category-delete-confirm-btn">
                        <i class="bi bi-trash me-2"></i>Delete Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
