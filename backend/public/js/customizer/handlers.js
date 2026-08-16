/**
 * UI Event Handlers and Initialization
 */
$(document).ready(function () {
    // 1. Initialize 3D Engine
    init();

    // 1.1 Check if product selection is required
    if (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.requiresSelection) {
        const productRequiredModal = new bootstrap.Modal(document.getElementById('productRequiredModal'));
        productRequiredModal.show();
    }

    // 2. Control Panel Listeners
    // Initialize the finish from whichever swatch is active (rendered server-side).
    const $initialActiveTexture = $('.texture-option.active').first();
    if ($initialActiveTexture.length) {
        currentTextureId = $initialActiveTexture.data('texture-id');
        currentTextureImagePath = $initialActiveTexture.data('image-path');
    } else {
        const $initialActiveColor = $('.color-option.active').first();
        if ($initialActiveColor.length) {
            currentColorId = $initialActiveColor.data('color-id');
            currentColorHex = $initialActiveColor.data('hex');
        }
    }

    /**
     * A design is either plain or patterned, so the two swatch groups are one
     * either/or choice — picking in one clears the other on both the highlight
     * and the state it drives.
     */
    function clearFinishSelection() {
        $('.texture-option, .color-option').removeClass('active');
        currentTextureId = null;
        currentTextureImagePath = null;
        currentColorId = null;
        currentColorHex = null;
    }

    $(document).on('click', '.texture-option', function () {
        clearFinishSelection();
        $(this).addClass('active');
        currentTextureId = $(this).data('texture-id');
        currentTextureImagePath = $(this).data('image-path');
        updateModelMaterial(currentTextureId);
        if (typeof calculateCustomPrice === 'function') calculateCustomPrice();
    });

    $(document).on('click', '.color-option', function () {
        clearFinishSelection();
        $(this).addClass('active');
        currentColorId = $(this).data('color-id');
        currentColorHex = $(this).data('hex');
        updateModelMaterial();
        if (typeof calculateCustomPrice === 'function') calculateCustomPrice();
    });

    $('.btn-size').on('click', function () {
        $('.btn-size').removeClass('active');
        $(this).addClass('active');
        const size = $(this).data('size');
        updateModelSize(size);
    });

    $('.btn-shape').on('click', function () {
        $('.btn-shape').removeClass('active');
        $(this).addClass('active');
        const shape = $(this).data('shape');
        
        if (model_group) {
            model_group.scale.set(1, 1, 1);
        }

        // The GLBs run to tens of megabytes, so the first switch to a shape is a
        // real download. Put the loader back up — each loader hides it when done.
        $('#loader').stop(true, true).fadeIn(200);

        if (shape === 'mug') createMugModel();
        else if (shape === 't-shirt') createTshirtModel();
        else if (shape === 'umbrella') createUmbrellaModel();
        else if (shape === 'bag') createBagModel();

        if (currentTextureId) updateModelMaterial(currentTextureId);

        const currentSize = $('.btn-size.active').data('size');
        if (currentSize) updateModelSize(currentSize);
    });

    $('.btn-config').on('click', function () {
        $('.btn-config').removeClass('active');
        $(this).addClass('active');
        $('.btn-config i.bi-check2-circle').removeClass('bi-check2-circle').addClass('bi-circle').addClass('opacity-25');
        $(this).find('i').removeClass('bi-circle').removeClass('opacity-25').addClass('bi-check2-circle');
    });

    // 3. Additional Features
    $('#lighting').on('change', function () {
        const isLit = $(this).is(':checked');
        if (isLit) {
            if (!internalLight) {
                internalLight = new THREE.PointLight(0xffaa00, 2, 10);
                internalLight.position.set(0, 0, 0);
                scene.add(internalLight);
            }
        } else if (internalLight) {
            scene.remove(internalLight);
            internalLight = null;
        }
        calculateCustomPrice();
    });

    // 4. Logo Handlers
    $('#logoInput').on('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            const img = new Image();
            img.onload = function () {
                const html = `
                    <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="tiny text-white-50 fw-bold">LOGO ELEMENT <span class="ms-1 logo-fee" style="color: #ffc508;">+${formatPeso(CustomizerPricing.rate('logo'))}</span></span>
                            <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="bg-white rounded p-1" style="width: 40px; height: 40px;">
                                <img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                            <div class="tiny text-white-50 truncate fw-bold" style="max-width: 150px;">${file.name}</div>
                        </div>
                        <div class="row g-2">
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">X</label>
                                <input type="range" class="form-range x-range" min="-50" max="50" value="0">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Y</label>
                                <input type="range" class="form-range y-range" min="-50" max="50" value="0">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Size</label>
                                <input type="range" class="form-range scale-range" min="0.1" max="5" step="0.1" value="1">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Rot</label>
                                <input type="range" class="form-range rotation-range" min="0" max="360" value="0">
                            </div>
                        </div>
                        <div class="tiny text-white-50 mt-2">Size sets the price: less below 1&times;, more above.</div>
                        ${flipControlsHtml(false, false)}
                    </div>`;
                const $item = $(html);
                $item.data('img-obj', img);
                $('#logoList').append($item);
                syncElementsAndRender();
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
        $(this).val('');
    });

    // 5. Text & Shape Adders
    $('#addTextBtn').on('click', function () {
        const html = `
            <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="tiny text-white-50 fw-bold">TEXT ELEMENT <span class="ms-1" style="color: #ffc508;">+${formatPeso(CustomizerPricing.rate('text'))}</span></span>
                    <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                </div>
                <input type="text" class="form-control form-control-sm bg-dark border-white-10 text-white text-input mb-2 shadow-none" placeholder="Enter text...">
                <div class="row g-2 mb-2">
                    <div class="col-8">
                        <select class="form-select form-select-sm bg-dark border-white-10 text-white font-select shadow-none">
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Impact">Impact</option>
                            <option value="Comic Sans MS">Comic Sans</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="color" class="form-control form-control-sm form-control-color bg-dark border-white-10 w-100 color-input mb-0" value="#ffffff">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">X</label>
                        <input type="range" class="form-range x-range" min="-50" max="50" value="0">
                    </div>
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">Y</label>
                        <input type="range" class="form-range y-range" min="-50" max="50" value="0">
                    </div>
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">Size</label>
                        <input type="range" class="form-range scale-range" min="0.5" max="4" step="0.1" value="1">
                    </div>
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">Rot</label>
                        <input type="range" class="form-range rotation-range" min="0" max="360" value="0">
                    </div>
                </div>
                ${flipControlsHtml(false, false)}
            </div>`;
        $('#textList').append(html);
        syncElementsAndRender();
    });

    $('#addShapeBtn').on('click', function () {
        const html = `
            <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="tiny text-white-50 fw-bold">SHAPE ELEMENT <span class="ms-1" style="color: #ffc508;">+${formatPeso(CustomizerPricing.rate('shape'))}</span></span>
                    <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-8">
                        <select class="form-select form-select-sm bg-dark border-white-10 text-white type-select shadow-none">
                            <option value="circle">Circle</option>
                            <option value="line">Line</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="color" class="form-control form-control-sm form-control-color bg-dark border-white-10 w-100 color-input mb-0" value="#ffffff">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">X</label>
                        <input type="range" class="form-range x-range" min="-50" max="50" value="0">
                    </div>
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">Y</label>
                        <input type="range" class="form-range y-range" min="-50" max="50" value="0">
                    </div>
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">Size</label>
                        <input type="range" class="form-range scale-range" min="0.1" max="5" step="0.1" value="1">
                    </div>
                    <div class="col-3">
                        <label class="tiny text-white-50 d-block mb-1">Rot</label>
                        <input type="range" class="form-range rotation-range" min="0" max="360" value="0">
                    </div>
                </div>
                ${flipControlsHtml(false, false)}
            </div>`;
        $('#shapeList').append(html);
        syncElementsAndRender();
    });

    // 6. Generic Event Delegation
    $(document).on('input change', '.customizer-item input, .customizer-item select', syncElementsAndRender);
    $(document).on('click', '.delete-btn', function () {
        $(this).closest('.customizer-item').remove();
        syncElementsAndRender();
    });

    // 7. Save & Cart Listeners
    $('#btn-save-design').on('click', function () {
        if (typeof CustomizerConfig === 'undefined') return;

        const btn = $(this);
        const productId = new URLSearchParams(window.location.search).get('product_id') || CustomizerConfig.productId;
        const recipe = serializeDesign();
        const snapshot = captureSnapshot();

        const originalContent = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        btn.prop('disabled', true);

        $.ajax({
            url: CustomizerConfig.routes.save,
            method: "POST",
            data: {
                _token: CustomizerConfig.csrfToken,
                product_id: productId,
                design_id: CustomizerConfig.designId,
                custom_recipe: recipe,
                custom_snapshot: snapshot
            },
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    // Update designId so subsequent saves update the same record
                    CustomizerConfig.designId = response.design_id;
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON ? xhr.responseJSON.message : 'Error saving design';
                showToast(message, 'error');
            },
            complete: function () {
                btn.html(originalContent);
                btn.prop('disabled', false);
            }
        });
    });

    $('#btn-add-to-cart-custom').on('click', function () {
        if (typeof CustomizerConfig === 'undefined') return;

        const btn = $(this);
        const productId = new URLSearchParams(window.location.search).get('product_id') || CustomizerConfig.productId;

        if (!productId) {
            showToast('Please select a base product first to add to cart!', 'warning');
            return;
        }

        const recipe = serializeDesign();
        const snapshot = captureSnapshot();

        const originalContent = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Adding...');
        btn.prop('disabled', true);

        $.ajax({
            url: CustomizerConfig.routes.addToCart,
            method: "POST",
            data: {
                _token: CustomizerConfig.csrfToken,
                product_id: productId,
                quantity: 1,
                custom_recipe: recipe,
                custom_snapshot: snapshot,
                custom_design_id: CustomizerConfig.designId
            },
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    
                    // Update designId so subsequent saves/adds update the same record
                    CustomizerConfig.designId = response.design_id;

                    if (window.parent && window.parent.updateCartBadge) {
                        window.parent.updateCartBadge(response.cart_count);
                    } else if (typeof updateCartBadge === 'function') {
                        updateCartBadge(response.cart_count);
                    }
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding to cart';
                showToast(message, 'error');
            },
            complete: function () {
                btn.html(originalContent);
                btn.prop('disabled', false);
            }
        });
    });

    // 8. Load Existing Design
    if (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.designRecipe) {
        setTimeout(() => {
            loadDesignRecipe(CustomizerConfig.designRecipe);
        }, 1500);
    }
});
