/**
 * Ceramic Mug Model Loader
 */
function createMugModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

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

        // Trigger re-render of design elements
        let activeTexture = 'blue';
        if ($('.texture-option.active').length > 0) {
            activeTexture = $('.texture-option.active').data('texture');
        } else if (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.activeColor) {
            activeTexture = CustomizerConfig.activeColor;
        }

        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(activeTexture);
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
