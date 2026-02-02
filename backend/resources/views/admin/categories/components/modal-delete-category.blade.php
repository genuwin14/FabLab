<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width: 64px; height: 64px;">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Delete Category?</h5>
                <p class="text-muted small mb-4">
                    Are you sure you want to delete <span id="deleteCategoryName" class="fw-bold text-dark"></span>?
                    This action cannot be undone.
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteCategoryForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Delete Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>