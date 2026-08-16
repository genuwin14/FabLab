/**
 * Umbrella Model Loader
 */

/**
 * The GLB splits the umbrella into the printable dome — the canopy fabric plus
 * the small cap at its apex — and black hardware: shaft, ribs and handle. Only
 * the dome carries the customer's design, so only those meshes are renamed to
 * 'base'; applyMapToBaseMesh() then leaves the metalwork alone.
 */
const UMBRELLA_CANOPY_MATERIALS = ['Cloth_Flower', 'pasted__aiStandardSurface25'];

function createUmbrellaModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    // Correct as-is, unlike the t-shirt and mug: applyPlanarUVs() below throws
    // the authored UVs away — they run to u 3.2, outside the tile — and re-unwraps
    // the canopy into exactly 0..1, so the whole tile really is the print area.
    designZones = SINGLE_ZONE;
    designTextureRepeat = 6;

    const loader = new THREE.GLTFLoader();
    console.log("Loading umbreella_open.glb...");

    loader.load('/gbl/umbreella_open.glb', function(gltf) {
        const model = gltf.scene;
        const canopyMeshes = [];

        model.traverse((child) => {
            if (!child.isMesh || !child.material) return;

            if (UMBRELLA_CANOPY_MATERIALS.includes(child.material.name)) {
                canopyMeshes.push(child);
            } else {
                // Shaft, ribs and handle: kept dark and out of the print area.
                child.material.roughness = 0.35;
                child.material.metalness = 0.6;
            }
            child.material.side = THREE.DoubleSide;
        });

        // Safety net for a swapped-in asset whose materials are named differently:
        // treat the whole model as printable rather than leaving nothing to design on.
        if (canopyMeshes.length === 0) {
            console.warn('No umbrella canopy material matched — treating the whole model as printable.');
            model.traverse((child) => {
                if (child.isMesh && child.material) canopyMeshes.push(child);
            });
        }

        // The canopy's authored UVs sit outside the 0..1 tile and split into two
        // islands, so the design canvas never reached it. Re-unwrap the cloth
        // through one shared top-down projection: a single continuous print area
        // that lines up across the outer skin and the lining underneath.
        const canopyBounds = new THREE.Box3();
        canopyMeshes.forEach((mesh) => getMeshBounds(mesh, canopyBounds));

        canopyMeshes.forEach((mesh) => {
            if (!applyPlanarUVs(mesh, canopyBounds)) return;
            mesh.name = 'base';
            mesh.material.color.setHex(getActiveColor());
            mesh.material.roughness = 0.7;
            mesh.material.metalness = 0.0;
        });

        fitModelToView(model);
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
