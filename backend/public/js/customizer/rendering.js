/**
 * Rendering and model material functions
 *
 * Texture model: each texture record has an image_path (URL or base64). We load
 * it as a THREE.Texture and assign as material.map. If a customer adds custom
 * elements (text/shapes/logos), we composite them onto a canvas overlaid on the
 * base texture and assign that canvas as the map instead.
 */

/**
 * Compat shim used by the model loaders (mug.js, t-shirt.js, shorts.js,
 * umbrella.js) to set an initial mesh color before updateModelMaterial runs.
 * Always returns white because textures are now image maps that overlay this.
 */
function getActiveColor() {
    return 0xFFFFFF;
}

/**
 * Look up a texture entry from CustomizerConfig.textures by id.
 * Returns null if not found.
 */
function getTextureById(textureId) {
    if (typeof CustomizerConfig === 'undefined' || !CustomizerConfig.textures) return null;
    return CustomizerConfig.textures.find(t => String(t.id) === String(textureId)) || null;
}

/**
 * Fallback: pick the first texture in the config (used as default on load).
 */
function getDefaultTexture() {
    if (typeof CustomizerConfig === 'undefined' || !CustomizerConfig.textures) return null;
    return CustomizerConfig.textures[0] || null;
}

/**
 * Returns a CSS color string for canvas overlay backgrounds. Used when we need
 * a flat fallback during overlay compositing (logos/text/shapes layered on top).
 * Since textures are now arbitrary images, we just use a neutral white/grey
 * fallback when an image_path is missing.
 */
function getOverlayBaseColor() {
    return '#ffffff';
}

/**
 * Load a texture image_path into a THREE.Texture (cached).
 */
function loadThreeTextureFromPath(imagePath, onLoaded) {
    if (!imagePath) {
        onLoaded(null);
        return;
    }
    if (textureCache[imagePath]) {
        onLoaded(textureCache[imagePath]);
        return;
    }
    const loader = new THREE.TextureLoader();
    loader.load(
        imagePath,
        (tex) => {
            tex.flipY = false;
            textureCache[imagePath] = tex;
            onLoaded(tex);
        },
        undefined,
        (err) => {
            console.warn('Failed to load texture image:', imagePath, err);
            onLoaded(null);
        }
    );
}

/**
 * Composite design elements (shapes, logos, text) onto a 1024x1024 canvas
 * over either the base texture image or a flat color fallback.
 */
function renderOverlayOnCanvas(ctx, baseImage) {
    if (baseImage) {
        try {
            ctx.drawImage(baseImage, 0, 0, 1024, 1024);
        } catch (e) {
            ctx.fillStyle = getOverlayBaseColor();
            ctx.fillRect(0, 0, 1024, 1024);
        }
    } else {
        ctx.fillStyle = getOverlayBaseColor();
        ctx.fillRect(0, 0, 1024, 1024);
    }

    // Elements sit relative to the model's printable panel, not the whole tile.
    // On the bag that panel is a corner of the atlas — drawing at the middle of
    // the canvas put designs on the inside of its back panel. For a model that
    // prints across the whole tile these work out to the original numbers
    // exactly: 10px per position unit and 1x sizes.
    const area = designPrintArea || FULL_PRINT_AREA;
    const areaX = area.u0 * 1024;
    const areaY = area.v0 * 1024;
    const areaW = (area.u1 - area.u0) * 1024;
    const areaH = (area.v1 - area.v0) * 1024;
    const centerX = areaX + areaW / 2;
    const centerY = areaY + areaH / 2;
    const unitX = 10 * (areaW / 1024);
    const unitY = 10 * (areaH / 1024);
    const sizeScale = Math.min(areaW, areaH) / 1024;

    // Keep elements off neighbouring UV islands when pushed to the edge.
    ctx.save();
    ctx.beginPath();
    ctx.rect(areaX, areaY, areaW, areaH);
    ctx.clip();

    // A panel can be unwrapped mirrored — the bag's runs bottom-to-top, so a
    // design drawn straight onto the canvas came out reflected. Mirror the whole
    // print area to compensate, which rights the artwork and makes the X/Y
    // sliders push the design the way the customer expects.
    if (area.flipU || area.flipV) {
        ctx.translate(centerX, centerY);
        ctx.scale(area.flipU ? -1 : 1, area.flipV ? -1 : 1);
        ctx.translate(-centerX, -centerY);
    }

    // 1. Draw Shapes first (background layer)
    shapeElements.forEach(shape => {
        ctx.save();
        const sx = centerX + (shape.x * unitX);
        const sy = centerY + (shape.y * unitY);
        ctx.translate(sx, sy);
        ctx.rotate((shape.rotation || 0) * Math.PI / 180);
        ctx.fillStyle = shape.color;

        if (shape.type === 'circle') {
            ctx.beginPath();
            ctx.arc(0, 0, 50 * shape.scale * sizeScale, 0, Math.PI * 2);
            ctx.fill();
        } else if (shape.type === 'line') {
            const w = 200 * shape.scale * sizeScale;
            const h = 20 * shape.scale * sizeScale;
            ctx.fillRect(-w / 2, -h / 2, w, h);
        }
        ctx.restore();
    });

    // 2. Draw Logos (middle layer)
    logoElements.forEach(logo => {
        if (!logo.img || !logo.img.complete) return;
        ctx.save();
        const lx = centerX + (logo.x * unitX);
        const ly = centerY + (logo.y * unitY);
        ctx.translate(lx, ly);
        ctx.rotate((logo.rotation || 0) * Math.PI / 180);

        const w = logo.img.width;
        const h = logo.img.height;
        const aspect = w / h;

        const targetW = 200 * logo.scale * sizeScale;
        const targetH = targetW / aspect;

        ctx.drawImage(logo.img, -targetW / 2, -targetH / 2, targetW, targetH);
        ctx.restore();
    });

    // 3. Draw Text (foreground layer)
    textElements.forEach(textElem => {
        if (!textElem.text.trim()) return;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = `bold ${48 * textElem.scale * sizeScale}px "${textElem.font}"`;
        ctx.fillStyle = textElem.color;

        const tx = centerX + (textElem.x * unitX);
        const ty = centerY + (textElem.y * unitY);

        ctx.translate(tx, ty);
        ctx.rotate((textElem.rotation || 0) * Math.PI / 180);
        ctx.fillText(textElem.text, 0, 0);
        ctx.restore();
    });

    ctx.restore(); // release the print-area clip
}

/**
 * Apply the active texture to the model. If a textureId is passed it overrides
 * `currentTextureId`. Otherwise uses the active state.
 *
 * Always re-renders: if there are overlay elements, composites them onto a
 * canvas above the base texture image; otherwise applies the texture directly
 * as material.map.
 */
function updateModelMaterial(textureId) {
    if (!model_group) return;

    if (textureId !== undefined && textureId !== null && textureId !== '') {
        currentTextureId = textureId;
        const lookup = getTextureById(textureId);
        if (lookup) currentTextureImagePath = lookup.image_path;
    }

    const imagePath = currentTextureImagePath;
    const hasOverlay = textElements.length > 0 || shapeElements.length > 0 || logoElements.length > 0;

    // Step 1: load the base image (raw HTMLImageElement) so we can draw it onto
    // overlay canvases. We only need this if hasOverlay — otherwise we apply the
    // cached THREE.Texture directly without canvas roundtrip.
    if (hasOverlay) {
        const baseImg = new Image();
        baseImg.crossOrigin = 'anonymous';
        const applyOverlay = () => {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 1024;
            const ctx = canvas.getContext('2d');
            renderOverlayOnCanvas(ctx, baseImg.complete && baseImg.naturalWidth > 0 ? baseImg : null);

            const canvasTexture = new THREE.CanvasTexture(canvas);
            canvasTexture.flipY = false;

            applyMapToBaseMesh(canvasTexture);
        };

        if (imagePath) {
            baseImg.onload = applyOverlay;
            baseImg.onerror = applyOverlay; // fall back to flat color
            baseImg.src = imagePath;
        } else {
            applyOverlay();
        }
    } else {
        // Just apply the texture directly (cached after first load)
        loadThreeTextureFromPath(imagePath, (threeTex) => {
            applyMapToBaseMesh(threeTex);
        });
    }
}

/**
 * Apply a THREE.Texture (or null) to the `base` mesh of the model.
 */
function applyMapToBaseMesh(threeTex) {
    if (!model_group) return;
    model_group.traverse((child) => {
        if (child.isMesh && (child.name === 'base' || (child.parent && child.parent.name === 'base'))) {
            if (child.userData.originalMap === undefined) {
                child.userData.originalMap = child.material.map || null;
            }

            if (threeTex) {
                child.material.map = threeTex;
                child.material.color.setHex(0xFFFFFF);
            } else {
                child.material.map = child.userData.originalMap;
                child.material.color.setHex(0xFFFFFF);
            }

            child.material.metalness = 0.0;
            child.material.roughness = 0.7;
            child.material.needsUpdate = true;
        }
    });
}
