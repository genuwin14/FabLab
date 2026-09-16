<!-- Delete Confirmation Modal -->
<div class="modal fade qv-modal" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-body p-4 p-lg-5 text-center">
                <button type="button" class="qv-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>

                <span class="qv-icon qv-icon-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>

                <span class="qv-eyebrow d-block mx-auto mb-3" style="width: fit-content;">My Designs</span>
                <h5 class="qv-title mb-2">Delete Design?</h5>
                <p class="text-muted small mb-4">
                    Are you sure you want to delete this design? This action cannot be undone.
                </p>

                <div class="qv-actions">
                    <button type="button" id="confirm-delete-btn" class="btn btn-danger btn-lg">
                        <i class="bi bi-trash3 me-2"></i> Delete
                    </button>
                    <button type="button" class="btn btn-light py-2 small" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
