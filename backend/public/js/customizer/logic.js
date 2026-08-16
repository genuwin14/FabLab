/**
 * Business logic (price calculation, UI sync, scaling)
 */

/**
 * Customization rates for the live quote.
 *
 * The amounts come from Admin → Customization Pricing via CustomizerConfig.rates,
 * the same rows App\Models\CustomDesign prices the design with when it reaches
 * the cart, so the quote a customer reads is the amount they are charged. The
 * fallbacks below only cover a page served without a rates payload.
 */
const CustomizerPricing = {
    DEFAULT_RATES: {
        text: 50,
        shape: 30,
        logo: 150,
        led_lighting: 500
    },

    // How far the Size slider goes. An uploaded image is billed for the print
    // it makes, so its fee follows the slider: half size costs half, double
    // size costs double. Mirrors CustomDesign::LOGO_MIN_SCALE / LOGO_MAX_SCALE.
    LOGO_MIN_SCALE: 0.1,
    LOGO_MAX_SCALE: 5,

    rate(key) {
        const configured = (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.rates)
            ? CustomizerConfig.rates[key]
            : undefined;
        const value = parseFloat(configured);

        return Number.isFinite(value) ? value : this.DEFAULT_RATES[key];
    },

    normalisedLogoScale(scale) {
        const value = parseFloat(scale);
        if (!Number.isFinite(value)) return 1;
        return Math.max(this.LOGO_MIN_SCALE, Math.min(this.LOGO_MAX_SCALE, value));
    },

    logoFee(scale) {
        const fee = this.rate('logo') * this.normalisedLogoScale(scale);
        return Math.round(fee * 100) / 100;
    }
};

function formatPeso(amount) {
    return '₱' + Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

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

/**
 * Rewrite each uploaded image's fee badge from its own Size slider, so the
 * customer sees the charge move as they drag. Read straight off the card rather
 * than pairing it with logoElements — a restored design fills the two in
 * whatever order its images finish loading.
 */
function refreshLogoFeeLabels() {
    $('#logoList .customizer-item').each(function () {
        const scale = $(this).find('.scale-range').val();
        $(this).find('.logo-fee').text('+' + formatPeso(CustomizerPricing.logoFee(scale)));
    });
}

function calculateCustomPrice() {
    const basePrice = (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.productPrice) ? CustomizerConfig.productPrice : 0;
    let extra = 0;

    extra += textElements.length * CustomizerPricing.rate('text');
    extra += shapeElements.length * CustomizerPricing.rate('shape');
    extra += logoElements.reduce((sum, logo) => sum + CustomizerPricing.logoFee(logo.scale), 0);

    refreshLogoFeeLabels();

    if ($('#lighting').is(':checked')) {
        extra += CustomizerPricing.rate('led_lighting');
    }

    // The finish surcharge — a texture or a plain colour, never both, matching
    // CustomDesign::finishLine() on the server.
    if (currentTextureId && typeof getTextureById === 'function') {
        const tex = getTextureById(currentTextureId);
        if (tex && tex.price_modifier) {
            extra += parseFloat(tex.price_modifier) || 0;
        }
    } else if (currentColorId) {
        const $swatch = $(`.color-option[data-color-id="${currentColorId}"]`).first();
        extra += parseFloat($swatch.data('price-modifier')) || 0;
    }

    extra = Math.round(extra * 100) / 100;
    const total = basePrice + extra;
    $('#total-price-display').text(formatPeso(total));

    if (extra > 0) {
        $('#customization-fee-badge').text('+ ' + formatPeso(extra) + ' Custom').removeClass('bg-accent text-primary').addClass('bg-warning text-dark');
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
