/**
 * Shorts Model Loader
 */
function createShortsModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

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
