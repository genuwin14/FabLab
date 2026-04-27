<!-- Add Texture Modal -->
<div class="modal fade" id="addTextureModal" tabindex="-1" aria-labelledby="addTextureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="addTextureModalLabel">Add New Texture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.textures.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="textureName" class="form-label small fw-bold">Texture Name</label>
                        <input type="text" class="form-control rounded-3" id="textureName" name="name" required placeholder="e.g. Matte Finish">
                    </div>
                    <div class="mb-3">
                        <label for="textureDescription" class="form-label small fw-bold">Description (Optional)</label>
                        <textarea class="form-control rounded-3" id="textureDescription" name="description" rows="3" placeholder="Describe the visual characteristics..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="textureImage" class="form-label small fw-bold">Texture Image (Preview)</label>
                        <input type="file" class="form-control rounded-3" id="textureImage" name="image_file" accept="image/*" onchange="previewImage(this, 'addTexturePreview')">
                        <div class="mt-2 text-center">
                            <img id="addTexturePreview" src="#" alt="Preview" class="rounded-3 d-none shadow-sm" style="max-height: 150px; max-width: 100%;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Texture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
