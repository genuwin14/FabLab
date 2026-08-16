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
 * whole. Most models are unwrapped so the whole tile prints, but an atlas can
 * pack the panel a customer actually sees into a corner — the bag's outer front
 * is at u 0.009..0.315, v 0.002..0.421, while the middle of its tile is the
 * inside of the back panel. Each model loader sets this.
 */
const FULL_PRINT_AREA = { u0: 0, v0: 0, u1: 1, v1: 1 };
let designPrintArea = FULL_PRINT_AREA;
