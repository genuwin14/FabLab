/**
 * Polo Shirt Model Loader
 */

/**
 * The chest and back panels, measured off the GLB with tools/measure-print-area.cjs
 * and the world-space sweep it prints.
 *
 * Unlike the t-shirt, these are NOT the file's own UVs. polo.glb ships unwrapped
 * to u -135..376, v -329..354 — hundreds of tiles outside the 0..1 design canvas,
 * so with any wrapping mode the customer's artwork tiled into noise and nothing
 * they placed landed where they put it. applyFrontBackUVs() below throws that
 * away and re-unwraps the garment into the tile, which is what these numbers
 * describe: the front half of the shirt occupies u 0..0.5 and the back half
 * u 0.5..1, both sharing the same vertical band.
 *
 * Within that, the bounds are the square-on core rather than the full panel, for
 * the reason t-shirt.js records: this torso is nearly as deep (5.3) as it is wide
 * (6.0), so only a narrow strip is really facing the customer. Square-on
 * geometry (normal.z > 0.9) holds to about x ±1.8 through the chest, and sizing
 * to the panel's full width instead would let a wide design wrap round to the
 * side seams and show as a sliver at the silhouette.
 *
 * Vertically the band runs from the sleeve line (y 0.0) down 3.2 units, the
 * upper half of a torso whose hem is at y -6.4 — where a chest print actually
 * goes, and clear of the collar and placket above it.
 *
 * No flips on either axis: the projection is built to land unmirrored. u grows
 * with world x on the front and against it on the back, which is screen-right in
 * both cases because the back is viewed from behind; v grows downward against
 * world y, which is the direction the canvas grows.
 */
const POLO_ZONES = [
    {
        id: 'front',
        label: 'Front',
        area: { u0: 0.162, v0: 0.474, u1: 0.341, v1: 0.634 },
        camera: { x: 4, y: 3, z: 8 },
    },
    {
        id: 'back',
        label: 'Back',
        area: { u0: 0.659, v0: 0.474, u1: 0.838, v1: 0.634 },
        camera: { x: -4, y: 3, z: -8 },
    },
];

function createPoloModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designZones = POLO_ZONES;
    // The chest core is ~0.18 of the tile wide, so this lands about 3.5 tiles
    // across it — the same density the t-shirt and the bag read at. The
    // projection is built isotropic, so this many copies is also what shows up
    // vertically rather than the weave coming out stretched.
    designTextureRepeat = 20;

    const loader = new THREE.GLTFLoader();
    console.log("Loading polo.glb...");

    loader.load('/gbl/polo.glb', function(gltf) {
        const model = gltf.scene;
        const shellMeshes = [];

        // One material for the whole garment, exported in seven chunks because
        // the mesh runs past the 65k-vertex index limit. They are all the same
        // surface, so they are all printable and they all have to be projected
        // through ONE shared box — measuring each chunk against its own bounds
        // would give seven different mappings and the design would step at every
        // chunk boundary.
        model.traverse((child) => {
            if (child.isMesh && child.material) shellMeshes.push(child);
        });

        const shellBounds = new THREE.Box3();
        shellMeshes.forEach((mesh) => getMeshBounds(mesh, shellBounds));

        shellMeshes.forEach((mesh) => {
            applyFrontBackUVs(mesh, shellBounds);
            mesh.name = 'base';
            mesh.material.color.setHex(getActiveColor());
            mesh.material.roughness = 0.85;
            mesh.material.metalness = 0.0;
            mesh.material.side = THREE.DoubleSide;
        });

        fitModelToView(model);
        model_group.add(model);
        console.log("Polo model loaded successfully.");

        // Trigger re-render with the currently selected texture
        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(currentTextureId);
        }

        hideLoadingScreen();
    }, undefined, function(error) {
        console.error('An error happened while loading the polo:', error);
        // Fallback
        const geometry = new THREE.BoxGeometry(1.8, 2.2, 0.6);
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
