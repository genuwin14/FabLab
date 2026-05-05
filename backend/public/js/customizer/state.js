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

// Currently selected texture (DB-backed)
let currentTextureId = null;
let currentTextureImagePath = null;

// Cached THREE.Texture instances by image_path so we don't reload on every click
const textureCache = {};
