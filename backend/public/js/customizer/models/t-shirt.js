/**
 * T-Shirt Model Loader
 */
function createTshirtModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designPrintArea = FULL_PRINT_AREA;

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
