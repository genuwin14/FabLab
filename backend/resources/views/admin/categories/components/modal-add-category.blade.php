<!-- Add Category Modal -->
<div class="modal fade category-modal" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <!-- Themed Dark Header -->
            <div class="category-modal-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="category-eyebrow">Admin</span>
                        <span class="category-eyebrow-divider">/</span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="addCategoryModalLabel">Add New Category</h5>
                    </div>
                    <button type="button" class="category-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body p-0 bg-white">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf

                    <div class="p-4">
                        <h6 class="category-section-title">
                            <i class="bi bi-tag me-2"></i>Category Details
                        </h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control category-field-input"
                                placeholder="e.g. 3D Printing" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                            <textarea name="description" class="form-control category-field-input" rows="4"
                                placeholder="Category details..."></textarea>
                        </div>
                    </div>

                    <div class="category-modal-footer">
                        <button type="button" class="btn category-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn category-btn-save rounded-pill px-4">
                            <i class="bi bi-check2 me-1"></i>Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
