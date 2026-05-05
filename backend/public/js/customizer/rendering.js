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

    // 1. Draw Shapes first (background layer)
    shapeElements.forEach(shape => {
        ctx.save();
        const sx = 512 + (shape.x * 10);
        const sy = 512 + (shape.y * 10);
        ctx.translate(sx, sy);
        ctx.rotate((shape.rotation || 0) * Math.PI / 180);
        ctx.fillStyle = shape.color;

        if (shape.type === 'circle') {
            ctx.beginPath();
            ctx.arc(0, 0, 50 * shape.scale, 0, Math.PI * 2);
            ctx.fill();
        } else if (shape.type === 'line') {
            const w = 200 * shape.scale;
            const h = 20 * shape.scale;
            ctx.fillRect(-w / 2, -h / 2, w, h);
        }
        ctx.restore();
    });

    // 2. Draw Logos (middle layer)
    logoElements.forEach(logo => {
        if (!logo.img || !logo.img.complete) return;
        ctx.save();
        const lx = 512 + (logo.x * 10);
        const ly = 512 + (logo.y * 10);
        ctx.translate(lx, ly);
        ctx.rotate((logo.rotation || 0) * Math.PI / 180);

        const w = logo.img.width;
        const h = logo.img.height;
        const aspect = w / h;

        const targetW = 200 * logo.scale;
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
        ctx.font = `bold ${48 * textElem.scale}px "${textElem.font}"`;
        ctx.fillStyle = textElem.color;

        const tx = 512 + (textElem.x * 10);
        const ty = 512 + (textElem.y * 10);

        ctx.translate(tx, ty);
        ctx.fillText(textElem.text, 0, 0);
        ctx.restore();
    });
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
