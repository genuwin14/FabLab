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
 * Where the current model's printable panel sits on the design canvas, in UV
 * space.
 *
 * Design elements are placed relative to this rather than to the tile as a
 * whole, and the X/Y sliders and element sizes renormalise to it — so ±50 on a
 * slider always means "the edge of the panel", whatever slice of the atlas that
 * panel occupies.
 *
 * Nearly every model needs it: a garment atlas packs front, back and sleeves
 * side by side, so the middle of the tile is rarely the middle of the panel a
 * customer sees. The bag's outer front sits at u 0.009..0.315, the t-shirt's
 * chest at u 0.104..0.442, the mug's camera-facing wall at u 0..0.222. Left at
 * FULL_PRINT_AREA a design drifts onto a neighbouring island, where it renders
 * mirrored and cut at the seam.
 *
 * Measure a new model rather than guessing: take the largest camera-facing UV
 * island, inset it ~8%, and flip an axis where the panel is unwrapped mirrored
 * (canvas v grows downward, so an unmirrored panel wants corr(v, worldY) < 0).
 * Each model loader sets this.
 */
const FULL_PRINT_AREA = { u0: 0, v0: 0, u1: 1, v1: 1 };
let designPrintArea = FULL_PRINT_AREA;
