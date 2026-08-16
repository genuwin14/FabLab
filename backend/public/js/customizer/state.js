/**
 * Customizer Global State
 */
let scene, camera, renderer, controls, model_group;
let isRotating = false;
let clock = new THREE.Clock();
let internalLight = null;

// Design elements state arrays
let textElements = [];
let shapeElements = [];
let logoElements = [];

/**
 * The design's finish. A product is either plain or patterned, so exactly one
 * of these two is set at a time — selecting either one clears the other.
 */
let currentTextureId = null;
let currentTextureImagePath = null;
let currentColorId = null;
let currentColorHex = null;

// Cached THREE.Texture instances by image_path so we don't reload on every click
const textureCache = {};

/**
 * Where a model's printable panels sit on the design canvas, in UV space.
 *
 * Elements are placed relative to their own panel rather than to the tile as a
 * whole, and the X/Y sliders and element sizes renormalise to it — so ±50 on a
 * slider always means "the edge of this panel", whatever slice of the atlas the
 * panel occupies.
 *
 * Nearly every model needs this: a garment atlas packs front, back and sleeves
 * side by side, so the middle of the tile is rarely the middle of any panel.
 * Left at FULL_PRINT_AREA a design drifts onto a neighbouring island, where it
 * renders mirrored and cut at the seam.
 *
 * Measure a new model rather than guessing — tools/measure-print-area.cjs, one
 * pass per --view. Two traps it encodes, both of which shipped once:
 *   - Size the panel to its square-on core, not the island's full extent. A
 *     garment island runs round to the side seams and a design sized to it
 *     wraps onto the side of the body.
 *   - Only the axis that curves around the body wants that strict treatment.
 *     Judging the vertical the same way lops the top off tall artwork.
 */
const FULL_PRINT_AREA = { u0: 0, v0: 0, u1: 1, v1: 1 };

/**
 * The panels a customer can put a design on, in the order they are offered.
 *
 * Every model declares at least one. Each element records which panel it
 * belongs to, so a t-shirt can carry different artwork front, back and on each
 * sleeve. `camera` is where to move the viewer to look at that panel head-on.
 */
const SINGLE_ZONE = [{ id: 'front', label: 'Front', area: FULL_PRINT_AREA, camera: { x: 4, y: 3, z: 8 } }];
let designZones = SINGLE_ZONE;

/** The panel currently being edited. Elements are added to this one. */
let currentZoneId = 'front';

/**
 * How many times a texture image tiles across the model's UV tile.
 *
 * A texture is a material sample — a weave, a grain — not a picture, so it has
 * to read at something like its real size. Stretching one copy over the whole
 * atlas magnifies it enormously: a t-shirt's chest is barely 40% of the tile
 * across, so a single copy shows the customer a blown-up crop rather than
 * fabric. Each model sets its own count, because the same number means
 * different things on a chest and on a mug wall.
 */
let designTextureRepeat = 1;

/** Elements saved before panels existed belong to whatever comes first. */
function defaultZoneId() {
    return (designZones && designZones.length) ? designZones[0].id : 'front';
}

function getZoneById(zoneId) {
    return (designZones || []).find(z => z.id === zoneId) || null;
}

/** Which panel an element sits on, tolerating recipes that predate panels. */
function zoneOf(element) {
    return (element && element.zone) || defaultZoneId();
}
