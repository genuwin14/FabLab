<!-- Delete Raw Material Modal -->
<div class="modal fade" id="deleteRawMaterialModal" tabindex="-1" aria-labelledby="deleteRawMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteRawMaterialModalLabel">Delete Raw Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteRawMaterialForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4">
                    <p>Are you sure you want to delete <strong id="deleteMaterialName"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Delete Material</button>
                </div>
            </form>
        </div>
    </div>
</div>
