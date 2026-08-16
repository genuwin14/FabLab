/**
 * T-Shirt Model Loader
 */

/**
 * The flat chest, measured off the GLB with tools/measure-print-area.cjs.
 *
 * Two numbers matter and they are not the same. The front island spans
 * u 0.068..0.476 — but that is the whole body, and its outer edges are fabric
 * curving round to the side seams (world x reaches ±0.21). The square-on core,
 * the part you could actually put a print on, is only u 0.154..0.397
 * (x ±0.14). Sizing to the island instead of the core let a wide design wrap
 * onto the side of the body, showing up as a stray sliver at the silhouette.
 *
 * The back sits at u 0.526..0.934 and the sleeves are separate islands, so on
 * FULL_PRINT_AREA the middle of the X slider sat at u 0.5 — off the chest
 * entirely, in the gap before the back panel — and pushing X walked designs
 * onto a neighbouring island, where they render mirrored and cut at the seam.
 *
 * The two axes are judged differently, and must be. u comes from the strictly
 * square-on core because that is where wrapping bites. v is measured loosely,
 * because nothing wraps vertically — above the chest is the collar and below it
 * the hem, both the same island. Holding v to the same strict threshold cut the
 * band to v 0.509..0.858 and lopped the top off tall artwork.
 *
 * Unmirrored on both axes (corr(u,worldX) +0.99, corr(v,worldY) -1.00), so no
 * flips. Widening v costs nothing in element size: sizeScale follows the
 * narrower side, which is u either way.
 */
const TSHIRT_ZONES = [
    {
        id: 'front',
        label: 'Front',
        area: { u0: 0.159, v0: 0.446, u1: 0.392, v1: 0.878 },
        camera: { x: 4, y: 3, z: 8 },
    },
    {
        // Unwrapped mirrored (corr(u,worldX) -0.99), hence flipU: without it
        // every design on the back reads backwards.
        id: 'back',
        label: 'Back',
        area: { u0: 0.615, v0: 0.447, u1: 0.812, v1: 0.833, flipU: true },
        camera: { x: -4, y: 3, z: -8 },
    },
    {
        // Sleeves are tubes, so u runs *around* the arm rather than across it —
        // corr(u,worldX) is ~0 on them. These bounds are the outward-facing arc
        // only, measured with --view=left/right; the rest of the u range curves
        // under the arm and round to the inside.
        id: 'left-sleeve',
        label: 'Left Sleeve',
        area: { u0: 0.178, v0: 0.052, u1: 0.318, v1: 0.193 },
        camera: { x: -8, y: 3, z: 4 },
    },
    {
        id: 'right-sleeve',
        label: 'Right Sleeve',
        area: { u0: 0.716, v0: 0.041, u1: 0.856, v1: 0.184 },
        camera: { x: 8, y: 3, z: 4 },
    },
];

function createTshirtModel() {
    // Clear existing children from model_group
    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    designZones = TSHIRT_ZONES;
    // The visible chest is ~0.24 of the tile wide, so this lands roughly 4 tiles
    // across it. At 1 the customer saw a 4x magnified crop of the image.
    designTextureRepeat = 16;

    const loader = new THREE.GLTFLoader();
    console.log("Loading t-shirt.glb...");

    loader.load('/gbl/t-shirt.glb', function(gltf) {
        const model = gltf.scene;
        model.position.set(0, -1, 0);
        model.scale.set(3.5, 3.5, 3.5);

        model.traverse((child) => {
            if (child.isMesh) {
                child.name = 'base';
                if (child.material) {
                    child.material.color.setHex(getActiveColor());
                    child.material.roughness = 0.8;
                    child.material.metalness = 0.1;
                    child.material.side = THREE.DoubleSide;
                }
            }
        });

        model_group.add(model);
        console.log("T-Shirt model loaded successfully.");

        // Trigger re-render with the currently selected texture
        if (typeof updateModelMaterial === 'function') {
            updateModelMaterial(currentTextureId);
        }

        hideLoadingScreen();
    }, undefined, function(error) {
        console.error('An error happened while loading the T-shirt:', error);
        model_group.add(mesh);
        hideLoadingScreen();
    });
}
