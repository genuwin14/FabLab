/**
 * Core Three.js initialization and main loop
 */
function init(containerId = 'three-container') {
    const container = document.getElementById(containerId);
    if (!container) return;

    // 1. Scene setup
    scene = new THREE.Scene();

    // 2. Camera setup
    camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(4, 3, 8);

    // 3. Renderer setup
    renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true,
        preserveDrawingBuffer: true // Required for screenshots
    });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.0;
    renderer.outputEncoding = THREE.sRGBEncoding;
    container.appendChild(renderer.domElement);

    // 4. Controls
    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.autoRotate = false;
    controls.autoRotateSpeed = 2.0;

    // 5. Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
    scene.add(ambientLight);

    const directLight1 = new THREE.DirectionalLight(0xffffff, 1.0);
    directLight1.position.set(5, 5, 5);
    scene.add(directLight1);

    const directLight2 = new THREE.DirectionalLight(0xffffff, 0.5);
    directLight2.position.set(-5, 2, -5);
    scene.add(directLight2);

    const rimLight = new THREE.PointLight(0xffc508, 0.8);
    rimLight.position.set(0, 5, -5);
    scene.add(rimLight);

    // 6. Environment / Objects
    model_group = new THREE.Group();
    scene.add(model_group);

    // Initial model based on product type from config
    const initialShape = (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.initialShape) ? CustomizerConfig.initialShape : 't-shirt';
    
    if (initialShape === 'mug') {
        createMugModel();
        $('.btn-shape[data-shape="mug"]').addClass('active');
    } else if (initialShape === 't-shirt') {
        createTshirtModel();
        $('.btn-shape[data-shape="t-shirt"]').addClass('active');
    } else if (initialShape === 'shorts') {
        createShortsModel();
        $('.btn-shape[data-shape="shorts"]').addClass('active');
    } else if (initialShape === 'umbrella') {
        createUmbrellaModel();
        $('.btn-shape[data-shape="umbrella"]').addClass('active');
    } else if (initialShape === 'bag') {
        createBagModel();
        $('.btn-shape[data-shape="bag"]').addClass('active');
    }

    // 7. Event Listeners - Remove old listener if exists before adding new one
    if (window._currentResizeHandler) {
        window.removeEventListener('resize', window._currentResizeHandler);
    }
    window._currentResizeHandler = () => onWindowResize(containerId);
    window.addEventListener('resize', window._currentResizeHandler);

    animate();
}

/**
 * Frame a freshly loaded GLB the same way as every other product.
 *
 * The source files come from different authors at wildly different scales and
 * origins — the bag and the umbrella are each a fraction of a unit across and
 * sit well off to one side of their own origin. Rather than hand-tuning a magic
 * scale per file, measure the model, size its largest dimension to `targetSize`
 * (matching the ~2.5 units the mug and t-shirt end up at), then recentre it so
 * OrbitControls pivots through the middle of the product.
 *
 * Call this BEFORE adding the model to model_group: the group carries the S/M/L
 * size scale, and measuring through it would cancel that out.
 */
function fitModelToView(model, targetSize = 2.5) {
    const size = new THREE.Box3().setFromObject(model).getSize(new THREE.Vector3());
    const largest = Math.max(size.x, size.y, size.z);
    if (!isFinite(largest) || largest <= 0) return;

    model.scale.setScalar(targetSize / largest);

    // Re-measure once scaled, then shift the model so its centre lands on the origin.
    const center = new THREE.Box3().setFromObject(model).getCenter(new THREE.Vector3());
    model.position.sub(center);
}

/**
 * Grow `target` to cover a mesh's vertices, with the mesh's own transform
 * applied. Measured against the loaded model's root, so call it before the
 * model joins model_group.
 */
function getMeshBounds(mesh, target) {
    const box = target || new THREE.Box3();
    const position = mesh.geometry ? mesh.geometry.attributes.position : null;
    if (!position) return box;

    mesh.updateWorldMatrix(true, false);
    const point = new THREE.Vector3();
    for (let i = 0; i < position.count; i++) {
        box.expandByPoint(point.fromBufferAttribute(position, i).applyMatrix4(mesh.matrixWorld));
    }
    return box;
}

/**
 * Re-unwrap a mesh by projecting it straight down onto the XZ plane.
 *
 * Every design is painted onto one 1024x1024 canvas, which only reaches the
 * model if the print surface is unwrapped across that tile. Not every GLB is:
 * the umbrella canopy ships unwrapped at u 2.18..3.20 — wholly outside the tile,
 * so with three's default clamped wrapping the canopy sampled a single column of
 * edge pixels and nothing the customer added ever showed up. Its island also
 * splits in two with a dead band through the middle, so simply rescaling it into
 * the tile would drop anything centred straight into the gap.
 *
 * A top-down projection gives one continuous print area instead, with the canvas
 * centre on the canopy — which is also how a design would really be printed onto
 * a dome. Pass `bounds` to project several meshes through one shared box so a
 * design lines up across them.
 *
 * Returns false if the mesh has no footprint to project onto.
 */
function applyPlanarUVs(mesh, bounds) {
    const position = mesh.geometry ? mesh.geometry.attributes.position : null;
    if (!position) return false;

    const box = bounds || getMeshBounds(mesh);
    const spanX = box.max.x - box.min.x;
    const spanZ = box.max.z - box.min.z;
    if (!(spanX > 0) || !(spanZ > 0)) return false;

    mesh.updateWorldMatrix(true, false);
    const point = new THREE.Vector3();
    const uv = new Float32Array(position.count * 2);

    for (let i = 0; i < position.count; i++) {
        point.fromBufferAttribute(position, i).applyMatrix4(mesh.matrixWorld);
        uv[i * 2] = (point.x - box.min.x) / spanX;
        uv[i * 2 + 1] = (point.z - box.min.z) / spanZ;
    }

    mesh.geometry.setAttribute('uv', new THREE.BufferAttribute(uv, 2));
    return true;
}

/**
 * Utility to hide loading screens across both editor and preview modal
 */
function hideLoadingScreen() {
    // Target both potential loader IDs to avoid conflicts
    const $loaders = $('#loader, #preview-loader');
    if ($loaders.length > 0) {
        $loaders.stop(true, true).fadeOut(400);
    }
}

function animate() {
    requestAnimationFrame(animate);
    if (controls) controls.update();
    if (renderer && scene && camera) renderer.render(scene, camera);
}

function onWindowResize(containerId = 'three-container') {
    const container = document.getElementById(containerId);
    if (!container) return;
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

function resetCamera() {
    camera.position.set(4, 3, 8);
    if(controls) controls.target.set(0, 0, 0);
}

/**
 * Swing the viewer round to look at a panel head-on.
 *
 * Editing the back of a shirt while staring at its front is guesswork, so
 * picking a panel moves the camera to it. All four sit the same distance out,
 * so switching panels reframes rather than zooms.
 */
function focusZoneCamera(zone) {
    if (!zone || !zone.camera || !camera) return;

    camera.position.set(zone.camera.x, zone.camera.y, zone.camera.z);
    if (controls) {
        controls.target.set(0, 0, 0);
        controls.update();
    }
}

function toggleAutoRotate() {
    isRotating = !isRotating;
    if(controls) controls.autoRotate = isRotating;
    const btnRotate = document.getElementById('btn-rotate');
    if (btnRotate) btnRotate.classList.toggle('active');
}

function downloadImage() {
    const link = document.createElement('a');
    link.download = 'custom-product-design.png';
    link.href = renderer.domElement.toDataURL('image/png');
    link.click();
}
