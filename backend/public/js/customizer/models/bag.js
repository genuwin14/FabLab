/**
 * Tote Bag Model Loader
 */
function createBagModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    const loader = new THREE.GLTFLoader();
    console.log("Loading bag.glb...");

    loader.load('/gbl/bag.glb', function(gltf) {
        const model = gltf.scene;

        // A single mesh with a single material, so the whole bag is printable.
        // Its own baseColorTexture is kept as the fallback map by
        // applyMapToBaseMesh() and comes back when no texture is selected.
        model.traverse((child) => {
            if (child.isMesh) {
                child.name = 'base';
                if (child.material) {
                    child.material.color.setHex(getActiveColor());
                    child.material.roughness = 0.85;
                    child.material.metalness = 0.0;
                    child.material.side = THREE.DoubleSide;
                }
            }
        });

        fitModelToView(model);
        model_group.add(model);
        console.log("Bag model loaded successfully.");

        // Trigger re-render with the currently selected texture
        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(currentTextureId);
        }

        hideLoadingScreen();
    }, undefined, function(error) {
        console.error('An error happened while loading the bag:', error);
        // Fallback
        const geometry = new THREE.BoxGeometry(1.8, 2, 0.6);
        const material = new THREE.MeshStandardMaterial({
            color: getActiveColor(),
            roughness: 0.85,
            metalness: 0.0
        });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.name = 'base';
        model_group.add(mesh);
        hideLoadingScreen();
    });
}
