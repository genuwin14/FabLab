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
