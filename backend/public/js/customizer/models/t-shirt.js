/**
 * T-Shirt Model Loader
 */

/**
 * The front body panel, measured off the GLB with tools/measure-print-area.cjs:
 * the island at u 0.068..0.476, v 0.357..0.899, centred on the midline and
 * nearest the camera. (It comes back as a pair — the outer face carries 26,722
 * camera-facing triangles, the lining behind it 20 — sharing one UV region.)
 * The back sits at u 0.526..0.934 and the sleeves are separate islands again.
 *
 * Left on FULL_PRINT_AREA the X/Y sliders ranged over the whole tile, so the
 * middle of the slider sat at u 0.5 — past the right edge of the chest, in the
 * gap before the back panel — and pushing X walked designs onto a neighbouring
 * island, which renders them mirrored and cut at the seam. That is the
 * "doesn't cover the shoulders properly" behaviour.
 *
 * Inset 8% from the trimmed bounds so an element at a slider extreme stays on
 * the chest rather than bleeding over the side seam. Unmirrored on both axes
 * (corr(u,worldX) +0.99, corr(v,worldY) -1.00), so no flips.
 */
const TSHIRT_PRINT_AREA = { u0: 0.107, v0: 0.436, u1: 0.423, v1: 0.847 };

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
