/**
 * T-Shirt Model Loader
 */

/**
 * The front body panel, measured off the GLB: the largest camera-facing UV
 * island, centred on the midline (world x 0.01) and nearest the camera
 * (z +0.05). It occupies u 0.104..0.442, v 0.377..0.899 — barely a third of the
 * tile's width. The rest holds the back and the sleeves as separate islands.
 *
 * Left on FULL_PRINT_AREA the X/Y sliders ranged over the whole tile, so the
 * middle of the slider sat at u 0.5 — past the right edge of the chest, in the
 * gap before the back panel — and pushing X walked designs onto the sleeves,
 * where a neighbouring island renders them mirrored and cut at the seam. That
 * is the "it doesn't cover the shoulders properly" behaviour.
 *
 * Inset 8% from the trimmed bounds so an element at a slider extreme stays on
 * the chest rather than bleeding over the side seam. Unmirrored on both axes
 * (corr(u,worldX) +1.00, corr(v,worldY) -1.00), so no flips.
 */
const TSHIRT_PRINT_AREA = { u0: 0.155, v0: 0.450, u1: 0.395, v1: 0.850 };

function createTshirtModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designPrintArea = TSHIRT_PRINT_AREA;

    const loader = new THREE.GLTFLoader();
    console.log("Loading t-shirt.glb...");

    loader.load('/gbl/t-shirt.glb', function(gltf) {
        const model = gltf.scene;
        model.position.set(0, -1, 0);
        model.scale.set(3.5, 3.5, 3.5);

        model.traverse((child) => {
            if (child.isMesh) {
                child.name = 'base';
                if (child.material) {
                    child.material.color.setHex(getActiveColor());
                    child.material.roughness = 0.8;
                    child.material.metalness = 0.1;
                    child.material.side = THREE.DoubleSide;
                }
            }
        });

        model_group.add(model);
        console.log("T-Shirt model loaded successfully.");

        // Trigger re-render with the currently selected texture
        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(currentTextureId);
        }

        hideLoadingScreen();
    }, undefined, function(error) {
        console.error('An error happened while loading the T-shirt:', error);
        model_group.add(mesh);
        hideLoadingScreen();
    });
}
