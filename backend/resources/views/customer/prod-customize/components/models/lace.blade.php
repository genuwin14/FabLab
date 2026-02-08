function createLaceModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    // Initialize GLTFLoader
    const loader = new THREE.GLTFLoader();

    console.log("Loading DamagedHelmet.glb...");

    loader.load('/DamagedHelmet.glb', function(gltf) {
        const model = gltf.scene;
        
        // Position and Scale for DamagedHelmet
        model.position.set(0, 0, 0);
        model.scale.set(1.5, 1.5, 1.5);
        
        // Traverse to ensure the material responds to the base color logic
        model.traverse((child) => {
            if (child.isMesh) {
                child.name = 'base';
                // Note: The helmet has its own complex textures, 
                // but setting the name to 'base' allows the color picker to tint it if desired.
                if (child.material && !child.material.map) {
                    child.material.color.setHex(getActiveColor());
                }
            }
        });

        model_group.add(model);
        console.log("Helmet model loaded successfully.");
    }, 
    function (xhr) {
        console.log((xhr.loaded / xhr.total * 100) + '% loaded');
    },
    function(error) {
        console.error('An error happened while loading the helmet:', error);
        
        // Fallback to Box if Helmet fails
        const geometry = new THREE.BoxGeometry(1.5, 1.5, 1.5);
        const material = new THREE.MeshStandardMaterial({ 
            color: getActiveColor(),
            roughness: 0.5,
            metalness: 0.5
        });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.name = 'base';
        model_group.add(mesh);
    });
}