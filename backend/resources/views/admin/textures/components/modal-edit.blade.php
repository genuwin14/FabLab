<!-- Edit Texture Modal -->
<div class="modal fade" id="editTextureModal" tabindex="-1" aria-labelledby="editTextureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="editTextureModalLabel">Edit Texture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTextureForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editTextureName" class="form-label small fw-bold">Texture Name</label>
                        <input type="text" class="form-control rounded-3" id="editTextureName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTextureDescription" class="form-label small fw-bold">Description (Optional)</label>
                        <textarea class="form-control rounded-3" id="editTextureDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editTextureImage" class="form-label small fw-bold">Update Image (Optional)</label>
                        <input type="file" class="form-control rounded-3" id="editTextureImage" name="image_file" accept="image/*" onchange="previewImage(this, 'editTexturePreview')">
                        <div class="mt-2 text-center">
                            <img id="editTexturePreview" src="#" alt="Preview" class="rounded-3 shadow-sm" style="max-height: 150px; max-width: 100%;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Texture</button>
                </div>
            </form>
        </div>
    </div>
</div>
