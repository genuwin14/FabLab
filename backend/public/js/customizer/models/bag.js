/**
 * Tote Bag Model Loader
 */

/**
 * Unlike the other products, the bag's atlas doesn't print across the whole
 * tile. Its outer front panel — the face the camera looks at — is packed into
 * the top-left corner at u 0.009..0.315, v 0.002..0.421; the middle of the tile
 * is the *inside* of the back panel, which is where designs were ending up.
 * Inset from the measured panel so an element pushed to the edge can't bleed
 * onto the island packed alongside it — the panel isn't a clean rectangle, it
 * tapers at the bottom corners. These bounds keep all four extremes of the X/Y
 * sliders on fabric.
 *
 * The panel is also unwrapped bottom-to-top: its v runs from the base of the bag
 * up to the rim (corr(v, worldY) = +0.61), while canvas v grows downward. Left
 * alone that renders every design mirrored, so flag it for a vertical flip.
 */
const BAG_PRINT_AREA = { u0: 0.05, v0: 0.04, u1: 0.275, v1: 0.30, flipV: true };

function createBagModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designPrintArea = BAG_PRINT_AREA;

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
