/**
 * Design data serialization and persistence
 */
function loadDesignRecipe(recipe) {
    if (!recipe) return;

    // 1. Update UI state for base attributes WITHOUT triggering render
    if (recipe.color) {
        $('.texture-option').removeClass('active');
        $(`.texture-option[data-texture="${recipe.color}"]`).addClass('active');
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
                    scale: parseFloat(txt.scale) || 1
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
                            <div class="col-4">
                                <label class="tiny text-white-50 d-block mb-1">X Pos</label>
                                <input type="range" class="form-range x-range" min="-50" max="50" value="${parseFloat(txt.x) || 0}">
                            </div>
                            <div class="col-4">
                                <label class="tiny text-white-50 d-block mb-1">Y Pos</label>
                                <input type="range" class="form-range y-range" min="-50" max="50" value="${parseFloat(txt.y) || 0}">
                            </div>
                            <div class="col-4">
                                <label class="tiny text-white-50 d-block mb-1">Size</label>
                                <input type="range" class="form-range scale-range" min="0.5" max="4" step="0.1" value="${parseFloat(txt.scale) || 1}">
                            </div>
                        </div>
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
                    rotation: parseFloat(shp.rotation) || 0
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
                        rotation: parseFloat(logo.rotation) || 0
                    });

                    const html = `
                        <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="tiny text-white-50 fw-bold">LOGO ELEMENT (LOADED)</span>
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
                        </div>`;
                    const $item = $(html);
                    $item.data('img-obj', img);
                    $('#logoList').append($item);
                    
                    const activeType = $('.texture-option.active').data('texture') || 'blue';
                    updateModelMaterial(activeType);
                    calculateCustomPrice();
                };
                img.src = logo.src;
            });
        }
    }

    const activeType = $('.texture-option.active').data('texture') || 'blue';
    updateModelMaterial(activeType);
    calculateCustomPrice();
}

/**
 * Serialize design to JSON string
 */
function serializeDesign() {
    const activeShape = $('.btn-shape.active').data('shape') || 't-shirt';
    const activeSize = $('.btn-size.active').data('size') || 'medium';
    const activeColor = $('.texture-option.active').data('texture') || 'blue';
    const ledLighting = $('#lighting').is(':checked');

    return JSON.stringify({
        base_style: activeShape,
        size: activeSize,
        color: activeColor,
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

    // Set global active color for model loaders to reference
    if (typeof CustomizerConfig !== 'undefined') {
        CustomizerConfig.activeColor = recipe.color || 'blue';
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
                    scale: parseFloat(txt.scale) || 1
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
                    rotation: parseFloat(shp.rotation) || 0
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
                        rotation: parseFloat(logo.rotation) || 0
                    });
                    updateModelMaterial(recipe.color || 'blue');
                };
                img.src = logo.src;
            });
        }
    }

    updateModelMaterial(recipe.color || 'blue');
}
