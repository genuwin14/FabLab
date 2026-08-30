/**
 * Polo Shirt Model Loader
 */

/**
 * The four printable panels — chest, back and both sleeves — measured off the
 * GLB with tools/measure-print-area.cjs and the world-space sweep it prints.
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
 * Vertically the band sits between the placket and the waist: y 1.4 down to
 * y -2.2. The armpit is at y 0.0, so a band hung below that line — as this one
 * was — prints on the belly rather than the chest, which is what it looked
 * like on the garment. The top is set just under where the placket and its
 * last button stop, at y 1.75, because artwork any higher rides the button
 * strip. 0.179 tall against 0.179 wide, so a square logo arrives square.
 *
 * The sleeves are the same projection read further out. Past |x| 3.2 the mesh
 * is sleeve and nothing else — the torso, hem flare included, stops at 3.05 —
 * so the strip beyond that is a panel in its own right, and giving it a zone
 * costs no change to the unwrap the front and back already rely on.
 *
 * Their bounds are the flat middle of the sleeve, and deliberately not all of
 * it. This projection flattens along z and a sleeve is a tube, so the fabric
 * turns away from the projection at both ends of its width: inboard it creases
 * into the armhole, outboard it rolls under the arm. Artwork taken all the way
 * out to either came out ragged along that edge. Sweeping the sleeve at
 * normal.z > 0.9 in half-unit slices, the strip that stays flat the whole way
 * down is x -4.3..-3.4, and these bounds are that with a little margin.
 *
 * That leaves u 0.056 of the tile against 0.179 for the chest, so sleeve
 * artwork lands about a third the size of a chest print — which is roughly
 * what a real sleeve print is. Vertically the band stops short of the shoulder
 * cap above (y 2.9) and the cuff hem below (y 0.5), the two places the tube
 * turns hardest.
 *
 * None of this straightens out a hard side-on view, and nothing here can. A
 * design projected along z has no width left when you look along x, so from
 * dead side the sleeve print squeezes to a sliver. Fixing that means giving
 * the sleeve a projection along its own axis, which needs a UV seam at the
 * armhole that this mesh has no vertices for — a re-export of polo.glb with a
 * real atlas, not a number in this file. The zone camera opens on the
 * front-quarter, which is where it reads.
 *
 * No flips on any zone: the projection is built to land unmirrored. u grows
 * with world x on the front and against it on the back, which is screen-right in
 * both cases because the back is viewed from behind; v grows downward against
 * world y, which is the direction the canvas grows. Both sleeves print on their
 * front-facing surface, so both follow the front's convention.
 */
const POLO_ZONES = [
    {
        id: 'front',
        label: 'Front',
        area: { u0: 0.162, v0: 0.405, u1: 0.341, v1: 0.584 },
        camera: { x: 4, y: 3, z: 8 },
    },
    {
        id: 'back',
        label: 'Back',
        area: { u0: 0.659, v0: 0.405, u1: 0.838, v1: 0.584 },
        camera: { x: -4, y: 3, z: -8 },
    },
    {
        // x -5.04..-3.2. The camera swings out to the left but stays in front,
        // because this face is front-facing: viewed from the side it is edge-on
        // and the design vanishes into the silhouette.
        id: 'left-sleeve',
        label: 'Left Sleeve',
        area: { u0: 0.032, v0: 0.330, u1: 0.088, v1: 0.450 },
        camera: { x: -5.6, y: 2, z: 7.3 },
    },
    {
        id: 'right-sleeve',
        label: 'Right Sleeve',
        area: { u0: 0.414, v0: 0.330, u1: 0.470, v1: 0.450 },
        camera: { x: 5.6, y: 2, z: 7.3 },
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
