<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Category Name</label>
                        <input type="text" name="name" class="form-control rounded-pill bg-light border-0 px-3"
                            placeholder="e.g. 3D Printing" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                        <textarea name="description" class="form-control bg-light border-0 rounded-3 px-3 py-2" rows="3"
                            placeholder="Category details..."></textarea>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-0 pb-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>