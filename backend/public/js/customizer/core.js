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
