/**
 * Design data serialization and persistence
 */

/**
 * The Flip H / Flip V pair every element card carries. Shared by the "restore a
 * saved design" templates below and the "add element" templates in handlers.js
 * (which loads after this file) so the two can't drift apart.
 */
function flipControlsHtml(flipH, flipV) {
    return `
        <div class="d-flex gap-3 mt-2">
            <label class="tiny text-white-50 d-flex align-items-center gap-1 mb-0" style="cursor: pointer;">
                <input type="checkbox" class="form-check-input flip-h-input mt-0"${flipH ? ' checked' : ''}> Flip H
            </label>
            <label class="tiny text-white-50 d-flex align-items-center gap-1 mb-0" style="cursor: pointer;">
                <input type="checkbox" class="form-check-input flip-v-input mt-0"${flipV ? ' checked' : ''}> Flip V
            </label>
        </div>`;
}

function loadDesignRecipe(recipe) {
    if (!recipe) return;

    // 1. Update UI state for base attributes WITHOUT triggering render
    // The finish is either/or, so restoring one clears whatever the page
    // defaulted to in the other group.
    // A finish the product no longer offers has no swatch to highlight, but the
    // design still has to open looking the way it was saved — so fall back to
    // what the recipe itself carries rather than reverting to blank white.
    if (recipe.texture_id) {
        $('.texture-option, .color-option').removeClass('active');
        currentColorId = null;
        currentColorHex = null;
        currentTextureId = recipe.texture_id;

        const $match = $(`.texture-option[data-texture-id="${recipe.texture_id}"]`);
        if ($match.length) {
            $match.addClass('active');
            currentTextureImagePath = $match.data('image-path');
        } else {
            currentTextureImagePath = recipe.texture_image || null;
        }
    } else if (recipe.color_id || recipe.color_hex) {
        $('.texture-option, .color-option').removeClass('active');
        currentTextureId = null;
        currentTextureImagePath = null;
        currentColorId = recipe.color_id || null;

        const $match = $(`.color-option[data-color-id="${recipe.color_id}"]`);
        if ($match.length) {
            $match.addClass('active');
            currentColorHex = $match.data('hex');
        } else {
            currentColorHex = recipe.color_hex || null;
        }
    }
    if (recipe.size) {
        $('.btn-size').removeClass('active');
        $(`.btn-size[data-size="${recipe.size}"]`).addClass('active');
        updateModelSize(recipe.size);
    }
    if (recipe.features && recipe.features.led_lighting) {
        $('#lighting').prop('checked', true).trigger('change');
    }

    // 2. Directly populate the element arrays from the recipe (bypass DOM round-trip)
    textElements = [];
    shapeElements = [];
    logoElements = [];

    if (recipe.elements) {
        // --- Text Elements ---
        if (recipe.elements.text) {
            recipe.elements.text.forEach(txt => {
                textElements.push({
                    text: txt.text || '',
                    font: txt.font || 'Arial',
                    color: txt.color || '#ffffff',
                    x: parseFloat(txt.x) || 0,
                    y: parseFloat(txt.y) || 0,
                    scale: parseFloat(txt.scale) || 1,
                    rotation: parseFloat(txt.rotation) || 0,
                    flipH: !!txt.flipH,
                    flipV: !!txt.flipV
                });

                const html = `
                    <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="tiny text-white-50 fw-bold">TEXT ELEMENT</span>
                            <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                        </div>
                        <input type="text" class="form-control form-control-sm bg-dark border-white-10 text-white text-input mb-2 shadow-none" placeholder="Enter text..." value="${(txt.text || '').replace(/"/g, '&quot;')}">
                        <div class="row g-2 mb-2">
                            <div class="col-8">
                                <select class="form-select form-select-sm bg-dark border-white-10 text-white font-select shadow-none">
                                    <option value="Arial" ${txt.font === 'Arial' ? 'selected' : ''}>Arial</option>
                                    <option value="Times New Roman" ${txt.font === 'Times New Roman' ? 'selected' : ''}>Times New Roman</option>
                                    <option value="Courier New" ${txt.font === 'Courier New' ? 'selected' : ''}>Courier New</option>
                                    <option value="Impact" ${txt.font === 'Impact' ? 'selected' : ''}>Impact</option>
                                    <option value="Comic Sans MS" ${txt.font === 'Comic Sans MS' ? 'selected' : ''}>Comic Sans</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <input type="color" class="form-control form-control-sm form-control-color bg-dark border-white-10 w-100 color-input mb-0" value="${txt.color || '#ffffff'}">
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">X</label>
                                <input type="range" class="form-range x-range" min="-50" max="50" value="${parseFloat(txt.x) || 0}">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Y</label>
                                <input type="range" class="form-range y-range" min="-50" max="50" value="${parseFloat(txt.y) || 0}">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Size</label>
                                <input type="range" class="form-range scale-range" min="0.5" max="4" step="0.1" value="${parseFloat(txt.scale) || 1}">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Rot</label>
                                <input type="range" class="form-range rotation-range" min="0" max="360" value="${parseFloat(txt.rotation) || 0}">
                            </div>
                        </div>
                        ${flipControlsHtml(txt.flipH, txt.flipV)}
                    </div>`;
                $('#textList').append(html);
            });
        }

        // --- Shape Elements ---
        if (recipe.elements.shapes) {
            recipe.elements.shapes.forEach(shp => {
                shapeElements.push({
                    type: shp.type || 'circle',
                    color: shp.color || '#ffffff',
                    x: parseFloat(shp.x) || 0,
                    y: parseFloat(shp.y) || 0,
                    scale: parseFloat(shp.scale) || 1,
                    rotation: parseFloat(shp.rotation) || 0,
                    flipH: !!shp.flipH,
                    flipV: !!shp.flipV
                });

                const html = `
                    <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="tiny text-white-50 fw-bold">SHAPE ELEMENT</span>
                            <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-8">
                                <select class="form-select form-select-sm bg-dark border-white-10 text-white type-select shadow-none">
                                    <option value="circle" ${shp.type === 'circle' ? 'selected' : ''}>Circle</option>
                                    <option value="line" ${shp.type === 'line' ? 'selected' : ''}>Line</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <input type="color" class="form-control form-control-sm form-control-color bg-dark border-white-10 w-100 color-input mb-0" value="${shp.color || '#ffffff'}">
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">X</label>
                                <input type="range" class="form-range x-range" min="-50" max="50" value="${parseFloat(shp.x) || 0}">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Y</label>
                                <input type="range" class="form-range y-range" min="-50" max="50" value="${parseFloat(shp.y) || 0}">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Size</label>
                                <input type="range" class="form-range scale-range" min="0.1" max="5" step="0.1" value="${parseFloat(shp.scale) || 1}">
                            </div>
                            <div class="col-3">
                                <label class="tiny text-white-50 d-block mb-1">Rot</label>
                                <input type="range" class="form-range rotation-range" min="0" max="360" value="${parseFloat(shp.rotation) || 0}">
                            </div>
                        </div>
                        ${flipControlsHtml(shp.flipH, shp.flipV)}
                    </div>`;
                $('#shapeList').append(html);
            });
        }

        // --- Logo Elements (async due to image loading) ---
        if (recipe.elements.logos) {
            recipe.elements.logos.forEach(logo => {
                const img = new Image();
                img.onload = function () {
                    logoElements.push({
                        img: img,
                        x: parseFloat(logo.x) || 0,
                        y: parseFloat(logo.y) || 0,
                        scale: parseFloat(logo.scale) || 1,
                        rotation: parseFloat(logo.rotation) || 0,
                        flipH: !!logo.flipH,
                        flipV: !!logo.flipV
                    });

                    const html = `
                        <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="tiny text-white-50 fw-bold">LOGO ELEMENT (LOADED) <span class="ms-1 logo-fee" style="color: #ffc508;">&nbsp;</span></span>
                                <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="bg-white rounded p-1" style="width: 40px; height: 40px;">
                                    <img src="${logo.src}" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-3">
                                    <label class="tiny text-white-50 d-block mb-1">X</label>
                                    <input type="range" class="form-range x-range" min="-50" max="50" value="${parseFloat(logo.x) || 0}">
                                </div>
                                <div class="col-3">
                                    <label class="tiny text-white-50 d-block mb-1">Y</label>
                                    <input type="range" class="form-range y-range" min="-50" max="50" value="${parseFloat(logo.y) || 0}">
                                </div>
                                <div class="col-3">
                                    <label class="tiny text-white-50 d-block mb-1">Size</label>
                                    <input type="range" class="form-range scale-range" min="0.1" max="5" step="0.1" value="${parseFloat(logo.scale) || 1}">
                                </div>
                                <div class="col-3">
                                    <label class="tiny text-white-50 d-block mb-1">Rot</label>
                                    <input type="range" class="form-range rotation-range" min="0" max="360" value="${parseFloat(logo.rotation) || 0}">
                                </div>
                            </div>
                            <div class="tiny text-white-50 mt-2">Size sets the price: less below 1&times;, more above.</div>
                            ${flipControlsHtml(logo.flipH, logo.flipV)}
                        </div>`;
                    const $item = $(html);
                    $item.data('img-obj', img);
                    $('#logoList').append($item);
                    
                    updateModelMaterial(currentTextureId);
                    calculateCustomPrice();
                };
                img.src = logo.src;
            });
        }
    }

    updateModelMaterial(currentTextureId);
    calculateCustomPrice();
}

/**
 * Serialize design to JSON string
 */
function serializeDesign() {
    const activeShape = $('.btn-shape.active').data('shape') || (typeof CustomizerConfig !== 'undefined' ? CustomizerConfig.initialShape : 't-shirt');
    const activeSize = $('.btn-size.active').data('size') || 'medium';
    const ledLighting = $('#lighting').is(':checked');

    return JSON.stringify({
        base_style: activeShape,
        size: activeSize,
        // Exactly one of these is set — the finish is either/or. The image path
        // and hex ride along so a preview can render the design even if the
        // texture or colour is retired from the catalogue later.
        texture_id: currentTextureId,
        texture_image: currentTextureId ? currentTextureImagePath : null,
        color_id: currentColorId,
        color_hex: currentColorHex,
        features: {
            led_lighting: ledLighting
        },
        elements: {
            text: textElements,
            shapes: shapeElements,
            logos: logoElements.map(logo => ({
                x: logo.x,
                y: logo.y,
                scale: logo.scale,
                rotation: logo.rotation,
                flipH: logo.flipH,
                flipV: logo.flipV,
                src: logo.img.src
            }))
        }
    });
}

function captureSnapshot() {
    if (!renderer || !scene || !camera) return null;
    renderer.render(scene, camera);
    return renderer.domElement.toDataURL('image/png');
}

/**
 * Simplified version for previewing without UI synchronization
 */
function loadDesignRecipePreview(recipe) {
    if (!recipe) return;

    // Resolve the finish from the recipe (preview mode skips DOM updates).
    // The recipe carries the colour's hex so an admin previewing an order does
    // not need the colors table loaded into the page.
    currentTextureId = null;
    currentTextureImagePath = null;
    currentColorId = null;
    currentColorHex = null;

    if (recipe.texture_id) {
        currentTextureId = recipe.texture_id;
        // The catalogue is preferred — it has the current image if the texture
        // was re-uploaded — with the recipe's own copy as the fallback.
        const lookup = getTextureById(recipe.texture_id);
        currentTextureImagePath = (lookup && lookup.image_path) || recipe.texture_image || null;
    } else if (recipe.color_hex) {
        currentColorId = recipe.color_id || null;
        currentColorHex = recipe.color_hex;
    }

    textElements = [];
    shapeElements = [];
    logoElements = [];

    if (recipe.elements) {
        if (recipe.elements.text) {
            recipe.elements.text.forEach(txt => {
                textElements.push({
                    text: txt.text || '',
                    font: txt.font || 'Arial',
                    color: txt.color || '#ffffff',
                    x: parseFloat(txt.x) || 0,
                    y: parseFloat(txt.y) || 0,
                    scale: parseFloat(txt.scale) || 1,
                    rotation: parseFloat(txt.rotation) || 0,
                    flipH: !!txt.flipH,
                    flipV: !!txt.flipV
                });
            });
        }

        if (recipe.elements.shapes) {
            recipe.elements.shapes.forEach(shp => {
                shapeElements.push({
                    type: shp.type || 'circle',
                    color: shp.color || '#ffffff',
                    x: parseFloat(shp.x) || 0,
                    y: parseFloat(shp.y) || 0,
                    scale: parseFloat(shp.scale) || 1,
                    rotation: parseFloat(shp.rotation) || 0,
                    flipH: !!shp.flipH,
                    flipV: !!shp.flipV
                });
            });
        }

        if (recipe.elements.logos) {
            recipe.elements.logos.forEach(logo => {
                const img = new Image();
                img.onload = function () {
                    logoElements.push({
                        img: img,
                        x: parseFloat(logo.x) || 0,
                        y: parseFloat(logo.y) || 0,
                        scale: parseFloat(logo.scale) || 1,
                        rotation: parseFloat(logo.rotation) || 0,
                        flipH: !!logo.flipH,
                        flipV: !!logo.flipV
                    });
                    updateModelMaterial(currentTextureId);
                };
                img.src = logo.src;
            });
        }
    }

    updateModelMaterial(currentTextureId);
}
