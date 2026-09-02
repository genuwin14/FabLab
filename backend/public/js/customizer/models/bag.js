/**
 * Tote Bag Model Loader
 */

/**
 * The GLB splits the tote into three authored materials, and they line up with
 * the jobs this loader has to do:
 *
 *   Mat_Truoc_Tui ("bag front face")  the front surface, a panel laid over the
 *                                     front of the body
 *   Tui           ("bag")             the body — and the handles, which are the
 *                                     same material but must not be printable
 *   Chi           ("thread")          the stitching, left as authored so it
 *                                     reads as stitching and not as fabric
 *
 * Front and back both print, so both the front panel and the body are 'base'.
 * The handles are not: they are a tube above the bag, and folding them into the
 * projection below would stretch the shared bounds from the bag's 4.33 to the
 * handles' 7.51 and shrink every design to a bit over half its size to make
 * room for a surface nobody prints on. They are 'shell' instead — the finish,
 * without the design.
 *
 * The handles cannot be told from the body by material, so they are told apart
 * by where they sit: the body is the Tui mesh centred within the front panel's
 * vertical span, the handles sit above it. Their materials are cloned, because
 * body and handles arrive sharing one material instance and the design map put
 * on the body would otherwise print on the handles too.
 */
const BAG_PANEL_MATERIAL = 'Mat_Truoc_Tui';
const BAG_FABRIC_MATERIAL = 'Tui';

/**
 * The two printable panels, from the unwrap applyFrontBackUVs() gives the bag.
 *
 * These are NOT the file's own UVs, and that is a change from the single front
 * zone this model shipped with. The front panel arrives unwrapped across the
 * whole tile, which is fine on its own but leaves nowhere for a back: the body
 * carries UVs running to u -1.70..1.97, so its back cannot be addressed as a
 * rectangle of the canvas at all. Re-projecting both through one shared box
 * puts front-facing geometry in u 0..0.5 and back-facing in u 0.5..1, which is
 * what these numbers describe and what lets the bag offer two panels.
 *
 * Measured off the projection rather than guessed. The bag spans x ±1.91 and
 * y 0.01..4.33; the print rectangle is x ±1.61, y 0.554..3.894 — the flat face
 * with a margin on all four sides, because the panel runs from the base fold to
 * the rim and artwork was never meant to wrap over either. Front and back are
 * the same rectangle read on opposite sides of the unwrap, so a design placed
 * on one lands in the same place on the other.
 *
 * No flips on either zone, and the front zone losing the flipV it used to carry
 * is part of the same change: the projection lands unmirrored by construction.
 * v grows downward against world y, which is the direction the canvas grows, so
 * artwork is already the right way up. u grows with world x on the front and
 * against it on the back — screen-right in both cases, because the back is
 * viewed from behind.
 */
const BAG_ZONES = [
    {
        id: 'front',
        label: 'Front',
        area: { u0: 0.040, v0: 0.275, u1: 0.461, v1: 0.711 },
        camera: { x: 4, y: 3, z: 8 },
    },
    {
        id: 'back',
        label: 'Back',
        area: { u0: 0.539, v0: 0.275, u1: 0.960, v1: 0.711 },
        camera: { x: -4, y: 3, z: -8 },
    },
];

function createBagModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designZones = BAG_ZONES;
    // The projection gives the bag's full width half the tile, so this lands 3
    // tiles across the face — the density the t-shirt and polo read at. It is
    // double the 3 the authored unwrap needed, which spread that same face over
    // the whole tile.
    designTextureRepeat = 6;

    const loader = new THREE.GLTFLoader();
    console.log("Loading tote_bag.glb...");

    loader.load('/gbl/tote_bag.glb', function(gltf) {
        const model = gltf.scene;
        const panelMeshes = [];
        const fabricMeshes = [];

        const named = (child, material) => (child.material.name || '').startsWith(material);

        model.traverse((child) => {
            if (!child.isMesh || !child.material) return;

            if (named(child, BAG_PANEL_MATERIAL)) panelMeshes.push(child);
            else if (named(child, BAG_FABRIC_MATERIAL)) fabricMeshes.push(child);

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
            fabricMeshes.length = 0;
        }

        // Split body from handles on height: see the note above.
        const panelBounds = new THREE.Box3();
        panelMeshes.forEach((mesh) => getMeshBounds(mesh, panelBounds));

        const printable = panelMeshes.slice();
        fabricMeshes.forEach((mesh) => {
            const centerY = getMeshBounds(mesh).getCenter(new THREE.Vector3()).y;
            const isBody = centerY >= panelBounds.min.y && centerY <= panelBounds.max.y;

            // Cloned so the design map on the body cannot reach the handles.
            mesh.material = mesh.material.clone();
            if (isBody) printable.push(mesh);
            else mesh.name = 'shell';
        });

        // One shared box for every printable mesh, so the front panel and the
        // body land on the same projection and a design does not step where one
        // meets the other.
        const printBounds = new THREE.Box3();
        printable.forEach((mesh) => getMeshBounds(mesh, printBounds));

        printable.forEach((mesh) => {
            applyFrontBackUVs(mesh, printBounds);
            mesh.name = 'base';
            mesh.material.color.setHex(getActiveColor());
        });

        model.traverse((child) => {
            if (child.isMesh && child.name === 'shell') child.material.color.setHex(getActiveColor());
        });

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
