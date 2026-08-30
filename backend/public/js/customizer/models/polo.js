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
 * Front and back are the whole panel — seam to seam, collar to hem — not a
 * patch on it. Earlier revisions sized them to the square-on core, which is
 * the right instinct for a chest logo and the wrong one for a garment: it
 * left a customer who picked "Front" printing on a slice of the belly. The
 * panel is what the label promises, so the panel is what the zone covers, and
 * a small design is a small design because it was scaled that way.
 *
 * Measured at normal.z > 0.9 the front runs x -2.99..2.96, which is the torso
 * at its full width: the side seams sit at 3.05 and nothing but hem flare goes
 * past them. Vertically it runs y 4.8 down to -6.9, from the collar seam to
 * the bottom hem. The back is the same panel read on the other side of the
 * unwrap.
 *
 * Both are held a hair inside that — x ±2.9 rather than ±3.0. Front and back
 * meet at the side seam, and a zone taken right up to it showed a thread of
 * the front print down the silhouette when you turned the shirt round. Five
 * thousandths of the tile buys that back and costs nothing anyone can see.
 *
 * Artwork does now reach the silhouette, and near the seams it foreshortens
 * the way any print on a curved side does. That is the trade the full panel
 * asks for and it is the right one here — cutting the width back to keep the
 * edges flat is what produced the belly patch.
 *
 * The sleeves are the same projection read further out. Past |x| 3.2 the mesh
 * is sleeve and nothing else — the torso, hem flare included, stops at 3.05 —
 * so the strip beyond that is a panel in its own right, and giving it a zone
 * costs no change to the unwrap the front and back already rely on.
 *
 * The sleeves are not on that projection at all — see planSleeveUVs(), which
 * flattens them along x instead. Squeezed into the body's z-flattening a sleeve
 * has no depth left: every point on the front of the tube at a given x collapses
 * onto one u, and a design put there smeared around the arm and pinched where
 * the surface turned. Every set of bounds tried against that projection was
 * choosing which part of the smear to show. Flattened sideways the tube unwraps
 * properly, and these are simply its rectangle, whole.
 *
 * The rectangles sit in the strip above the torso, which the body projection
 * leaves empty, and each is 0.100 of the tile across against 0.289 for the
 * front. Sleeve artwork therefore lands about a third the size of a front
 * print — about the ratio the real panels have.
 *
 * The cameras are square onto each sleeve, x ±8, the same arrangement
 * t-shirt.js uses and the same 9.4 out as the front and back, so switching
 * panels reframes rather than zooms. A sleeve print is looked at from the side
 * and now unwraps for that view, so that is where the studio opens.
 *
 * No flips on any zone: the projection is built to land unmirrored. u grows
 * with world x on the front and against it on the back, which is screen-right in
 * both cases because the back is viewed from behind; v grows downward against
 * world y, which is the direction the canvas grows. Both sleeves print on their
 * front-facing surface, so both follow the front's convention.
 */
/** Beyond this |x| the mesh is sleeve; the torso stops at 3.05. */
const POLO_SLEEVE_SPLIT = 3.2;

const POLO_ZONES = [
    {
        id: 'front',
        label: 'Front',
        area: { u0: 0.107, v0: 0.236, u1: 0.396, v1: 0.820 },
        camera: { x: 4, y: 3, z: 8 },
    },
    {
        id: 'back',
        label: 'Back',
        area: { u0: 0.605, v0: 0.236, u1: 0.893, v1: 0.820 },
        camera: { x: -4, y: 3, z: -8 },
    },
    {
        id: 'left-sleeve',
        label: 'Left Sleeve',
        area: { u0: 0.026, v0: 0.014, u1: 0.126, v1: 0.170 },
        camera: { x: -8, y: 3, z: 4 },
    },
    {
        id: 'right-sleeve',
        label: 'Right Sleeve',
        area: { u0: 0.195, v0: 0.013, u1: 0.296, v1: 0.166 },
        camera: { x: 8, y: 3, z: 4 },
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

        // Past |x| 3.2 the mesh is sleeve and nothing else: the torso, hem
        // flare included, stops at 3.05. That makes the split clean, and the
        // sleeves get the sideways projection planSleeveUVs() explains rather
        // than being flattened along z with the body.
        const sleeves = planSleeveUVs(shellMeshes, shellBounds, POLO_SLEEVE_SPLIT);

        shellMeshes.forEach((mesh) => {
            // Cut the seam before unwrapping: a triangle with a foot in each
            // projection sweeps the canvas between them.
            applyFrontBackUVs(mesh, shellBounds, sleeves, splitGarmentSeam(mesh, sleeves));
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
