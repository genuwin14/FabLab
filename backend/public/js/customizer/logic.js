/**
 * Business logic (price calculation, UI sync, scaling)
 */
function syncElementsAndRender() {
    // Sync Text
    textElements = [];
    $('#textList .customizer-item').each(function () {
        textElements.push({
            text: $(this).find('.text-input').val(),
            font: $(this).find('.font-select').val(),
            color: $(this).find('.color-input').val(),
            x: parseFloat($(this).find('.x-range').val()),
            y: parseFloat($(this).find('.y-range').val()),
            scale: parseFloat($(this).find('.scale-range').val()),
            rotation: parseFloat($(this).find('.rotation-range').val()) || 0,
            flipH: $(this).find('.flip-h-input').is(':checked'),
            flipV: $(this).find('.flip-v-input').is(':checked')
        });
    });

    // Sync Shapes
    shapeElements = [];
    $('#shapeList .customizer-item').each(function () {
        shapeElements.push({
            type: $(this).find('.type-select').val(),
            color: $(this).find('.color-input').val(),
            x: parseFloat($(this).find('.x-range').val()),
            y: parseFloat($(this).find('.y-range').val()),
            scale: parseFloat($(this).find('.scale-range').val()),
            rotation: parseFloat($(this).find('.rotation-range').val()),
            flipH: $(this).find('.flip-h-input').is(':checked'),
            flipV: $(this).find('.flip-v-input').is(':checked')
        });
    });

    // Sync Logos
    logoElements = [];
    $('#logoList .customizer-item').each(function () {
        logoElements.push({
            img: $(this).data('img-obj'),
            x: parseFloat($(this).find('.x-range').val()),
            y: parseFloat($(this).find('.y-range').val()),
            scale: parseFloat($(this).find('.scale-range').val()),
            rotation: parseFloat($(this).find('.rotation-range').val()),
            flipH: $(this).find('.flip-h-input').is(':checked'),
            flipV: $(this).find('.flip-v-input').is(':checked')
        });
    });

    updateModelMaterial(currentTextureId);
    calculateCustomPrice();
}

function calculateCustomPrice() {
    const basePrice = (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.productPrice) ? CustomizerConfig.productPrice : 0;
    let extra = 0;

    extra += textElements.length * 50;
    extra += shapeElements.length * 30;
    extra += logoElements.length * 150;

    if ($('#lighting').is(':checked')) {
        extra += 500;
    }

    // Add texture price modifier if a texture is selected
    if (currentTextureId && typeof getTextureById === 'function') {
        const tex = getTextureById(currentTextureId);
        if (tex && tex.price_modifier) {
            extra += parseFloat(tex.price_modifier) || 0;
        }
    }

    const total = basePrice + extra;
    $('#total-price-display').text('₱' + total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    if (extra > 0) {
        $('#customization-fee-badge').text('+ ₱' + extra.toLocaleString() + ' Custom').removeClass('bg-accent text-primary').addClass('bg-warning text-dark');
    } else {
        $('#customization-fee-badge').text('Standard').addClass('bg-accent text-primary').removeClass('bg-warning text-dark');
    }
}

function updateModelSize(size) {
    if (!model_group) return;
    let scale = 1.0;
    if (size === 'small') scale = 0.85;
    else if (size === 'medium') scale = 1.0;
    else if (size === 'large') scale = 1.15;

    // Visual feedback: brief pop animation
    model_group.scale.set(scale * 1.05, scale * 1.05, scale * 1.05);
    setTimeout(() => {
        model_group.scale.set(scale, scale, scale);
    }, 100);
}
