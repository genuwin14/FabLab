<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editCategoryId" name="category_id">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Category Name</label>
                        <input type="text" name="name" id="editCategoryName"
                            class="form-control rounded-pill bg-light border-0 px-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                        <textarea name="description" id="editCategoryDescription"
                            class="form-control bg-light border-0 rounded-3 px-3 py-2" rows="3"></textarea>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-0 pb-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>