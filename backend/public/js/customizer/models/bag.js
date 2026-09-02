/**
 * Tote Bag Model Loader
 */

/**
 * The GLB splits the tote into three authored materials, and they line up with
 * the three jobs this loader has to do:
 *
 *   Mat_Truoc_Tui ("bag front face")  the print panel — a separate surface laid
 *                                     over the front of the body, unwrapped to
 *                                     its own clean 0..1 tile
 *   Tui           ("bag")             body and handles: the fabric that has to
 *                                     follow the colour picker
 *   Chi           ("thread")          the stitching, left as authored so it
 *                                     reads as stitching and not as fabric
 *
 * Only the front panel becomes 'base', so the design lands there and nowhere
 * else. The body carries UVs that run to u -1.70..1.97 — fine for the tiled
 * weave it was authored for, but it would repeat a design across the bag four
 * times over — so it becomes 'shell' instead: same finish, no design.
 */
const BAG_PANEL_MATERIAL = 'Mat_Truoc_Tui';
const BAG_FABRIC_MATERIAL = 'Tui';

/**
 * The front panel owns the whole tile, so unlike the old bag asset — whose front
 * was packed into a 0.23-wide corner of a shared atlas — there is no island to
 * steer around here. What is left to decide is the margin, and these are the
 * flat printable face rather than the panel's full extent: the panel runs from
 * the base fold to the rim, and artwork was never meant to wrap over either.
 *
 * Measured, not guessed. The face spans x ±1.03 and y 0.02..2.40; these bounds
 * put the print at x ±0.87, y 0.32..2.16 — centred on the face with a margin on
 * all four sides. The v inset is the larger number because the unwrap crowds
 * the bottom of the tile: v 0..0.05 is all base curve, worth only y 0.04..0.11.
 *
 * The panel is unwrapped bottom-to-top (corr(v, worldY) = +1.00), while canvas
 * v grows downward, so it needs the same vertical flip the old asset did or
 * every design renders upside down.
 */
const BAG_ZONES = [{ id: 'front', label: 'Front', area: { u0: 0.10, v0: 0.15, u1: 0.90, v1: 0.86, flipV: true }, camera: { x: 4, y: 3, z: 8 } }];

function createBagModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designZones = BAG_ZONES;
    // The panel is the full tile now, so this is 3 tiles across the face — the
    // density the t-shirt and polo read at. The old asset needed 12 to get
    // there because its panel was under a quarter of the tile wide.
    designTextureRepeat = 3;

    const loader = new THREE.GLTFLoader();
    console.log("Loading tote_bag.glb...");

    loader.load('/gbl/tote_bag.glb', function(gltf) {
        const model = gltf.scene;
        const panelMeshes = [];

        const named = (child, material) => (child.material.name || '').startsWith(material);

        model.traverse((child) => {
            if (!child.isMesh || !child.material) return;

            if (named(child, BAG_PANEL_MATERIAL)) {
                panelMeshes.push(child);
            } else if (named(child, BAG_FABRIC_MATERIAL)) {
                // Body and handles: no design, but they take the selected
                // colour and texture so the bag reads as one piece of fabric.
                child.name = 'shell';
            }

            child.material.color.setHex(getActiveColor());
            child.material.roughness = 0.85;
            child.material.metalness = 0.0;
            child.material.side = THREE.DoubleSide;
        });

        // Safety net for a swapped-in asset whose materials are named differently:
        // treat the whole model as printable rather than leaving nothing to design on.
        if (panelMeshes.length === 0) {
            console.warn('No bag front-panel material matched — treating the whole model as printable.');
            model.traverse((child) => {
                if (child.isMesh && child.material) panelMeshes.push(child);
            });
        }

        panelMeshes.forEach((mesh) => { mesh.name = 'base'; });

        fitModelToView(model);
        setModel(model);
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
        setModel(mesh);
        hideLoadingScreen();
    });
}
