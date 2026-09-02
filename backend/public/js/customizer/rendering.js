/**
 * Rendering and model material functions
 *
 * Texture model: each texture record has an image_path (URL or base64). We load
 * it as a THREE.Texture and assign as material.map. If a customer adds custom
 * elements (text/shapes/logos), we composite them onto a canvas overlaid on the
 * base texture and assign that canvas as the map instead.
 */

/**
 * Initial mesh color the model loaders (mug.js, t-shirt.js, shorts.js,
 * umbrella.js, bag.js) paint on before updateModelMaterial runs.
 *
 * White under a texture, since a texture is an image map that covers it, but
 * the selected plain colour when there is one — that way a model swapped
 * mid-design comes in already the right colour instead of flashing white.
 */
function getActiveColor() {
    if (currentColorHex && !currentTextureId) {
        const parsed = parseInt(currentColorHex.replace('#', ''), 16);
        if (Number.isFinite(parsed)) return parsed;
    }
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
 * The flat background an overlay canvas is painted on when there is no texture
 * image to draw under the design elements — the selected plain colour, or white
 * when nothing is selected.
 */
function getOverlayBaseColor() {
    return (currentColorHex && !currentTextureId) ? currentColorHex : '#ffffff';
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
            // The renderer outputs sRGB, so a texture it is not told about is
            // treated as already-linear and gets gamma applied twice — which
            // reads as a washed-out, faded version of the image. GLTFLoader
            // sets this on the model's own maps; ours have to say so too.
            tex.encoding = THREE.sRGBEncoding;
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
 * Mirror an element about its own axes. Applied after the element's rotation,
 * so a flip reads as "mirror this artwork" rather than "mirror the canvas" —
 * the same way it behaves in a design tool.
 */
function applyElementFlip(ctx, element) {
    if (element.flipH || element.flipV) {
        ctx.scale(element.flipH ? -1 : 1, element.flipV ? -1 : 1);
    }
}

/**
 * Tile a texture across the model at the current model's repeat count.
 *
 * Set at apply time rather than at load: loadThreeTextureFromPath caches by
 * image path, so one THREE.Texture is shared by every model that uses it and
 * the count has to follow whichever is on screen.
 *
 * Only for textures applied straight to the material — the overlay canvas has
 * its tiling painted in already and must stay at 1:1.
 */
function applyTextureTiling(threeTex) {
    if (!threeTex) return;

    const repeat = Math.max(1, designTextureRepeat || 1);
    threeTex.wrapS = THREE.RepeatWrapping;
    threeTex.wrapT = THREE.RepeatWrapping;
    threeTex.repeat.set(repeat, repeat);
    threeTex.needsUpdate = true;
}

/**
 * Lay the base texture down under the design, tiled the same number of times
 * the no-overlay path tiles it, so adding a logo doesn't change how the
 * material reads.
 */
function paintBaseTexture(ctx, baseImage) {
    const flat = () => {
        ctx.fillStyle = getOverlayBaseColor();
        ctx.fillRect(0, 0, 1024, 1024);
    };

    if (!baseImage) return flat();

    try {
        const repeat = Math.max(1, designTextureRepeat || 1);

        if (repeat === 1) {
            ctx.drawImage(baseImage, 0, 0, 1024, 1024);
            return;
        }

        // Scale one copy into an offscreen cell and tile that, so the cell lands
        // on an exact pixel boundary and the seams don't shimmer.
        const cell = Math.max(1, Math.round(1024 / repeat));
        const tile = document.createElement('canvas');
        tile.width = cell;
        tile.height = cell;
        tile.getContext('2d').drawImage(baseImage, 0, 0, cell, cell);

        ctx.fillStyle = ctx.createPattern(tile, 'repeat');
        ctx.fillRect(0, 0, 1024, 1024);
    } catch (e) {
        flat();
    }
}

/**
 * Composite design elements (shapes, logos, text) onto a 1024x1024 canvas
 * over either the base texture image or a flat color fallback.
 */
function renderOverlayOnCanvas(ctx, baseImage) {
    paintBaseTexture(ctx, baseImage);

    // Each panel is composited independently, so a t-shirt's front, back and
    // sleeves can carry different artwork without one bleeding into another.
    const zones = (designZones && designZones.length) ? designZones : SINGLE_ZONE;
    const fallback = zones[0].id;

    for (const zone of zones) {
        const mine = (el) => ((el && el.zone) || fallback) === zone.id;

        renderZoneOnCanvas(ctx, zone.area || FULL_PRINT_AREA, {
            shapes: shapeElements.filter(mine),
            logos: logoElements.filter(mine),
            texts: textElements.filter(mine),
        });
    }
}

/**
 * Composite one panel's elements into its slice of the canvas.
 *
 * Positions and sizes are relative to the panel, not the tile: on the bag that
 * panel is a corner of the atlas, and drawing at the middle of the canvas put
 * designs on the inside of its back panel. For a model that prints across the
 * whole tile these work out to the original numbers exactly — 10px per position
 * unit and 1x sizes.
 */
function renderZoneOnCanvas(ctx, area, elements) {
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
    elements.shapes.forEach(shape => {
        ctx.save();
        const sx = centerX + (shape.x * unitX);
        const sy = centerY + (shape.y * unitY);
        ctx.translate(sx, sy);
        ctx.rotate((shape.rotation || 0) * Math.PI / 180);
        applyElementFlip(ctx, shape);
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
    elements.logos.forEach(logo => {
        if (!logo.img || !logo.img.complete) return;
        ctx.save();
        const lx = centerX + (logo.x * unitX);
        const ly = centerY + (logo.y * unitY);
        ctx.translate(lx, ly);
        ctx.rotate((logo.rotation || 0) * Math.PI / 180);
        applyElementFlip(ctx, logo);

        const w = logo.img.width;
        const h = logo.img.height;
        const aspect = w / h;

        const targetW = 200 * logo.scale * sizeScale;
        const targetH = targetW / aspect;

        ctx.drawImage(logo.img, -targetW / 2, -targetH / 2, targetW, targetH);
        ctx.restore();
    });

    // 3. Draw Text (foreground layer)
    elements.texts.forEach(textElem => {
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
        applyElementFlip(ctx, textElem);
        ctx.fillText(textElem.text, 0, 0);
        ctx.restore();
    });

    ctx.restore(); // release the print-area clip
}

/**
 * Apply the active finish to the model. If a textureId is passed it overrides
 * `currentTextureId`. Otherwise uses the active state — which may be a plain
 * colour instead of a texture.
 *
 * Always re-renders: if there are overlay elements, composites them onto a
 * canvas above the base texture image (or the plain colour); otherwise applies
 * the texture, or the colour, directly to the material.
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
            // Canvas pixels are sRGB, same as any image — say so, or the design
            // fades the moment an element is added and the model switches to
            // this path.
            canvasTexture.encoding = THREE.sRGBEncoding;

            applyMapToBaseMesh(canvasTexture);
        };

        if (imagePath) {
            baseImg.onload = applyOverlay;
            baseImg.onerror = applyOverlay; // fall back to flat color
            baseImg.src = imagePath;
        } else {
            applyOverlay();
        }
    } else if (imagePath) {
        // Just apply the texture directly (cached after first load)
        loadThreeTextureFromPath(imagePath, (threeTex) => {
            applyTextureTiling(threeTex);
            applyMapToBaseMesh(threeTex);
        });
    } else if (currentColorHex) {
        // A plain finish: no image map at all, just tint the material. Drawing
        // it as a 1024px canvas would cost memory for a single flat colour.
        applyMapToBaseMesh(null, currentColorHex);
    } else {
        applyMapToBaseMesh(null);
    }

    applyFinishToShellMeshes();
}

/**
 * Apply the active finish to any `shell` meshes: parts of the product that are
 * the same fabric as the print panel but carry no design.
 *
 * A single-mesh model has none of these and this is a no-op. The tote bag needs
 * them — its body and handles are a separate mesh from the front panel, so
 * without this they would keep whatever colour they were painted at load and
 * sit there in white while the panel followed the colour picker.
 *
 * They take the texture or the colour but never the design canvas, which is
 * what separates them from `base`.
 */
function applyFinishToShellMeshes() {
    if (!model_group) return;

    let hasShell = false;
    model_group.traverse((child) => {
        if (child.isMesh && child.name === "shell") hasShell = true;
    });
    if (!hasShell) return;

    const paint = (threeTex, tintHex) => {
        model_group.traverse((child) => {
            if (!child.isMesh || child.name !== "shell") return;

            if (child.userData.originalMap === undefined) {
                child.userData.originalMap = child.material.map || null;
            }

            if (threeTex) {
                child.material.map = threeTex;
                child.material.color.setHex(0xFFFFFF);
            } else if (tintHex) {
                child.material.map = null;
                // Same sRGB conversion applyMapToBaseMesh() explains.
                child.material.color.set(tintHex).convertSRGBToLinear();
            } else {
                child.material.map = child.userData.originalMap;
                child.material.color.setHex(0xFFFFFF);
            }

            child.material.needsUpdate = true;
        });
    };

    if (currentTextureImagePath) {
        loadThreeTextureFromPath(currentTextureImagePath, (threeTex) => {
            applyTextureTiling(threeTex);
            paint(threeTex, null);
        });
    } else {
        paint(null, currentColorHex || null);
    }
}

/**
 * Apply a THREE.Texture to the `base` mesh of the model.
 *
 * With no texture, `tintHex` paints the mesh a plain colour; with neither, the
 * mesh falls back to whatever map the GLB shipped with.
 */
function applyMapToBaseMesh(threeTex, tintHex) {
    if (!model_group) return;
    model_group.traverse((child) => {
        if (child.isMesh && (child.name === 'base' || (child.parent && child.parent.name === 'base'))) {
            if (child.userData.originalMap === undefined) {
                child.userData.originalMap = child.material.map || null;
            }

            if (threeTex) {
                child.material.map = threeTex;
                child.material.color.setHex(0xFFFFFF);
            } else if (tintHex) {
                child.material.map = null;
                // Same sRGB story as the textures: a hex out of the colour
                // picker is sRGB, but material colours are read as linear, so
                // without converting, every plain finish renders pale.
                child.material.color.set(tintHex).convertSRGBToLinear();
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
