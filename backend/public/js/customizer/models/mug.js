/**
 * Ceramic Mug Model Loader
 */

function createMugModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    /**
     * Left uncalibrated on purpose — a cylinder is not a flat panel.
     *
     * The mug's outer wall is one island wrapping the whole circumference,
     * u 0..1, so unlike the t-shirt's chest there is no narrower u range to
     * clamp to. Constraining u to the arc that happens to face the camera cut
     * the print area to 18% of the tile, which drags sizeScale down with it and
     * clips anything a customer scales up.
     *
     * What *is* wrong here: the wall only occupies v 0..0.5 (v 0 is the rim,
     * v 0.5 the base), so the lower half of the Y slider runs off it onto the
     * base and the inside. Fixing that alone would still move every saved mug
     * design, so it wants doing deliberately rather than as a side effect.
     * Rotating the mesh so the UV seam faces away from the camera would let a
     * centred print area work properly.
     */
    designZones = SINGLE_ZONE;

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
