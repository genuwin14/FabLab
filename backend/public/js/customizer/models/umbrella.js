/**
 * Umbrella Model Loader
 */
function createUmbrellaModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    const loader = new THREE.GLTFLoader();
    console.log("Loading umbrella.glb...");

    loader.load('/gbl/umbrella.glb', function(gltf) {
        const model = gltf.scene;
        model.position.set(0, -1, 0);
        model.scale.set(0.01, 0.01, 0.01);

        model.traverse((child) => {
            if (child.isMesh) {
                child.name = 'base';
                if (child.material) {
                    child.material.color.setHex(getActiveColor());
                    child.material.roughness = 0.4;
                    child.material.metalness = 0.3;
                    child.material.side = THREE.DoubleSide;
                }
            }
        });

        model_group.add(model);
        console.log("Umbrella model loaded successfully.");

        // Trigger re-render with the currently selected texture
        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(currentTextureId);
        }

        hideLoadingScreen();
    }, undefined, function(error) {
        console.error('An error happened while loading the umbrella:', error);
        // Fallback
        const geometry = new THREE.ConeGeometry(2, 2, 32);
        const material = new THREE.MeshStandardMaterial({
            color: getActiveColor(),
            roughness: 0.5,
            metalness: 0.5
        });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.name = 'base';
        model_group.add(mesh);
        hideLoadingScreen();
    });
}
