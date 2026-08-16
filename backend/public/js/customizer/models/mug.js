/**
 * Ceramic Mug Model Loader
 */

/**
 * The slice of the outer wall that faces the default camera, measured off the
 * GLB. The wall's UVs wrap the full circumference across u, and the seam falls
 * near the front, so the camera-facing arc arrives as two pieces: u 0..0.222 at
 * 0.95 square-on to the camera, and u 0.825..1 at only 0.56. This is the
 * square-on one.
 *
 * v matters as much as u here: the wall only occupies v 0..0.485, so on
 * FULL_PRINT_AREA the bottom half of the Y slider put designs on the rim, the
 * inside of the cup and its base. Rim to base runs with v (corr(v,worldY)
 * -1.00), so no flip.
 */
const MUG_PRINT_AREA = { u0: 0.020, v0: 0.040, u1: 0.205, v1: 0.440 };

function createMugModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designPrintArea = MUG_PRINT_AREA;

    const loader = new THREE.GLTFLoader();
    console.log("Loading cup.glb...");

    loader.load('/gbl/cup.glb', function(gltf) {
        const model = gltf.scene;

        // Position and Scale for Cup
        model.position.set(0, -1, 0);
        model.scale.set(10, 10, 10);

        model.traverse((child) => {
            if (child.isMesh) {
                child.name = 'base';
                if (child.material) {
                    child.material.color.setHex(getActiveColor());
                    child.material.roughness = 0.1;
                    child.material.metalness = 0.2;
                }
            }
        });

        model_group.add(model);
        console.log("Cup model loaded successfully.");

        // Trigger re-render with the currently selected texture
        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(currentTextureId);
        }

        hideLoadingScreen();
    }, undefined, function(error) {
        console.error('An error happened while loading the cup:', error);
        // Fallback
        const geometry = new THREE.CylinderGeometry(1, 1, 2.2, 32);
        const material = new THREE.MeshStandardMaterial({
            color: getActiveColor(),
            roughness: 0.1,
            metalness: 0.2
        });
        model_group.add(mesh);
        hideLoadingScreen();
    });
}
