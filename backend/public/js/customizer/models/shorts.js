/**
 * Shorts Model Loader
 */
function createShortsModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    // Uncalibrated, but harmlessly so: /gbl/shorts.glb does not exist, so this
    // always falls through to the box below, whose faces each take the whole
    // 0..1 tile. Ship a real shorts GLB and this needs measuring like the
    // t-shirt and mug were.
    designZones = SINGLE_ZONE;
    designTextureRepeat = 8;

    const loader = new THREE.GLTFLoader();
    console.log("Loading shorts.glb...");

    loader.load('/gbl/shorts.glb', function(gltf) {
        const model = gltf.scene;
        model.position.set(0, -1, 0);
        model.scale.set(100, 100, 100);

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
        console.log("Shorts model loaded successfully.");

        // Trigger re-render with the currently selected texture
        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(currentTextureId);
        }

        hideLoadingScreen();
    }, undefined, function(error) {
        console.error('An error happened while loading the shorts:', error);
        // Fallback
        const geometry = new THREE.BoxGeometry(2, 1.5, 0.8);
        const material = new THREE.MeshStandardMaterial({
            color: getActiveColor(),
            roughness: 0.8,
            metalness: 0.1
        });
        const mesh = new THREE.Mesh(geometry, material);
        model_group.add(mesh);
        hideLoadingScreen();
    });
}
