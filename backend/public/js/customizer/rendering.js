/**
 * Rendering and model material functions
 */
function getActiveColor() {
    let texture = null;
    
    // 1. Try to get from UI
    if ($('.texture-option.active').length > 0) {
        texture = $('.texture-option.active').data('texture');
    } 
    // 2. Fallback to global config
    else if (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.activeColor) {
        texture = CustomizerConfig.activeColor;
    }
    
    switch (texture || 'blue') {
        case 'blue': return 0x0000FF;
        case 'black': return 0x000000;
        case 'white': return 0xFFFFFF;
        case 'yellow': return 0xFFC107;
        default: return 0x0000FF;
    }
}

function renderOverlayOnCanvas(ctx, colorCss) {
    // Fill background color
    ctx.fillStyle = colorCss;
    ctx.fillRect(0, 0, 1024, 1024);

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

function updateModelMaterial(type) {
    if (!model_group) return;
    let color = 0x0000FF;
    let metal = 0.0;
    let rough = 0.7;
    switch (type) {
        case 'blue': color = 0x0000FF; break;
        case 'black': color = 0x000000; break;
        case 'white': color = 0xFFFFFF; break;
        case 'yellow': color = 0xFFC107; break;
    }

    const colorCss = '#' + color.toString(16).padStart(6, '0');

    model_group.traverse((child) => {
        if (child.isMesh && (child.name === 'base' || (child.parent && child.parent.name === 'base'))) {
            if (textElements.length > 0 || shapeElements.length > 0 || logoElements.length > 0) {
                const canvas = document.createElement('canvas');
                canvas.width = 1024;
                canvas.height = 1024;
                const ctx = canvas.getContext('2d');

                renderOverlayOnCanvas(ctx, colorCss);

                const texture = new THREE.CanvasTexture(canvas);
                texture.flipY = false;

                if (child.userData.originalMap === undefined) {
                    child.userData.originalMap = child.material.map || null;
                }

                child.material.map = texture;
                child.material.color.setHex(0xFFFFFF);
            } else {
                if (child.userData.originalMap !== undefined) {
                    child.material.map = child.userData.originalMap;
                } else {
                    child.material.map = null;
                }
                child.material.color.setHex(color);
            }

            child.material.metalness = metal;
            child.material.roughness = rough;
            child.material.needsUpdate = true;
        }
    });
}
