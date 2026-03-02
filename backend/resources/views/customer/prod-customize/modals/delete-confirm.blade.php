<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger display-4"></i>
                </div>
                <h5 class="fw-bold mb-2">Delete Design?</h5>
                <p class="text-muted small mb-0">Are you sure you want to delete this design? This action cannot be
                    undone.</p>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2">
                <button type="button" class="btn btn-soft-secondary flex-grow-1 rounded-3 fw-bold py-2"
                    data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-delete-btn"
                    class="btn btn-danger flex-grow-1 rounded-3 fw-bold py-2">Delete</button>
            </div>
        </div>
    </div>
</div>