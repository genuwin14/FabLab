@extends('layout.app')

@section('content')
    <div class="d-flex h-screen overflow-hidden" style="background-color: #05111a;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white-10"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('customer.partials.sidebar')
        </aside>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0 text-white" tabindex="-1" id="customerSidebarOffcanvas"
            aria-labelledby="customerSidebarOffcanvasLabel" style="width: 280px; background-color: #05111a;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('customer.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('customer.partials.navbar')
            </header>

            <!-- 2-Panel Customizer Layout -->
            <main class="flex-grow-1 d-flex flex-column flex-md-row overflow-hidden">

                <!-- LEFT PANEL: Components, Features, Textures -->
                <div
                    class="col-md-4 col-lg-3 d-flex flex-column border-end border-white-10 bg-dark-glass customizer-sidebar">
                    <div class="p-4 border-bottom border-white-10">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-box-seam text-accent"></i>
                            <h5 class="text-white fw-bold mb-0">{{ $product ? $product->name : 'Customizer' }}</h5>
                        </div>
                        <p class="text-white-50 small mb-0">
                            {{ $product ? 'Configuring ' . $product->name : 'Configure your unique design' }}
                        </p>
                    </div>

                    <div class="flex-grow-1 overflow-y-auto p-4 customizer-scrollbar">
                        <!-- Step 1: Component Selection -->
                        <div class="mb-5">
                            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">1. Select
                                Component</label>
                            <div class="component-list d-grid gap-2">
                                <button class="btn btn-config active" data-component="base">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-box"><i class="bi bi-layers"></i></div>
                                        <div class="text-start">
                                            <div class="fw-bold small">Main Base</div>
                                            <div class="text-white-50 tiny">Primary Structure</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-check2-circle ms-auto"></i>
                                </button>
                                <button class="btn btn-config" data-component="accents">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-box"><i class="bi bi-stars"></i></div>
                                        <div class="text-start">
                                            <div class="fw-bold small">Accent Trim</div>
                                            <div class="text-white-50 tiny">Decorative Elements</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-circle ms-auto opacity-25"></i>
                                </button>
                                <button class="btn btn-config" data-component="hardware">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-box"><i class="bi bi-nut"></i></div>
                                        <div class="text-start">
                                            <div class="fw-bold small">Hardware</div>
                                            <div class="text-white-50 tiny">Fixtures & Bolts</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-circle ms-auto opacity-25"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Materials & Textures -->
                        <div class="mb-5">
                            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">2. Materials
                                & Finishes</label>
                            <div class="row g-3">
                                <div class="col-4">
                                    <div class="texture-option active" data-texture="gold" title="Gold">
                                        <div class="texture-preview"
                                            style="background: linear-gradient(45deg, #FFD700, #FDB931);"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="texture-option" data-texture="carbon" title="Carbon Fiber">
                                        <div class="texture-preview"
                                            style="background: radial-gradient(circle, #2c3e50 0%, #000000 100%);"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="texture-option" data-texture="chrome" title="Chrome">
                                        <div class="texture-preview"
                                            style="background: linear-gradient(135deg, #e6e9f0 0%, #eef1f5 100%);"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="texture-option" data-texture="matte-black" title="Matte Black">
                                        <div class="texture-preview" style="background-color: #1a1a1a;"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="texture-option" data-texture="royal-blue" title="Royal Blue">
                                        <div class="texture-preview" style="background-color: #0047AB;"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="texture-option" data-texture="emerald" title="Emerald Green">
                                        <div class="texture-preview" style="background-color: #50C878;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Additional Features -->
                        <div class="mb-4">
                            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">3.
                                Additional Features</label>
                            <div class="feature-item mb-3">
                                <div
                                    class="form-check form-switch custom-switch p-0 d-flex justify-content-between align-items-center">
                                    <label class="form-check-label text-white small" for="glossyFinish">Premium Glossy
                                        Finish</label>
                                    <input class="form-check-input" type="checkbox" id="glossyFinish" checked>
                                </div>
                            </div>
                            <div class="feature-item mb-3">
                                <div
                                    class="form-check form-switch custom-switch p-0 d-flex justify-content-between align-items-center">
                                    <label class="form-check-label text-white small" for="engraving">Laser Engraving</label>
                                    <input class="form-check-input" type="checkbox" id="engraving">
                                </div>
                            </div>
                            <div class="feature-item">
                                <div
                                    class="form-check form-switch custom-switch p-0 d-flex justify-content-between align-items-center">
                                    <label class="form-check-label text-white small" for="lighting">Internal LED
                                        Lighting</label>
                                    <input class="form-check-input" type="checkbox" id="lighting">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="p-4 border-top border-white-10 bg-darker-glass">
                        <div class="d-flex justify-content-between align-items-end mb-4">
                            <div>
                                <div class="text-white-50 tiny text-uppercase fw-bold mb-1">Estimated Base Price</div>
                                <div class="text-white h4 fw-bold mb-0">₱4,500.00</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-accent text-primary rounded-pill px-3">+ ₱500 Premium</span>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-outline-light border-white-10 w-100 rounded-pill py-2 small fw-bold">
                                    <i class="bi bi-save me-1"></i> Save
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-accent w-100 rounded-pill py-2 small fw-bold shadow-gold">
                                    <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL: 3D Visualization -->
                <div class="flex-grow-1 position-relative bg-viewer overflow-hidden">
                    <!-- Canvas Container -->
                    <div id="three-container" class="w-100 h-100 h-md-100" style="min-height: 400px;"></div>

                    <!-- Viewer Controls -->
                    <div
                        class="position-absolute bottom-0 start-50 translate-middle-x mb-4 d-flex gap-2 p-2 bg-dark-glass rounded-pill border border-white-10 backdrop-blur shadow-lg">
                        <button class="btn btn-control-round" onclick="resetCamera()" title="Reset Camera">
                            <i class="bi bi-camera-reels"></i>
                        </button>
                        <div class="vr bg-white opacity-10 mx-1"></div>
                        <button class="btn btn-control-round" onclick="toggleAutoRotate()" id="btn-rotate"
                            title="Auto Rotate">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <button class="btn btn-control-round" onclick="downloadImage()" title="Snapshot">
                            <i class="bi bi-camera"></i>
                        </button>
                    </div>

                    <!-- Status Display -->
                    <div class="position-absolute top-0 end-0 m-4">
                        <div
                            class="bg-dark-glass p-3 rounded-4 border border-white-10 backdrop-blur d-flex align-items-center gap-3">
                            <div class="status-indicator online"></div>
                            <div>
                                <div class="text-white small fw-bold line-height-1">Real-time Visualization</div>
                                <div class="text-white-50 tiny">Engine: Three.js WebGL Core</div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Tooltip -->
                    <div class="position-absolute top-0 start-0 m-4 d-none d-lg-block">
                        <div class="bg-dark-glass p-2 rounded-pill border border-white-10 px-3 backdrop-blur">
                            <span class="text-white-50 small"><i class="bi bi-info-circle me-1"></i> Left Click to Rotate |
                                Right Click to Pan | Scroll to Zoom</span>
                        </div>
                    </div>

                    <!-- Loader -->
                    <div id="loader" class="position-absolute top-50 start-50 translate-middle text-center">
                        <div class="glitch-loader mb-3">
                            <div class="cube text-accent">
                                <i class="bi bi-box" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <h6 class="text-white fw-bold tracking-widest text-uppercase">Materializing...</h6>
                        <p class="text-white-50 tiny">Optimizing 3D Assets</p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <style>
        /* Modern Utilities */
        .text-accent {
            color: #ffc508 !important;
        }

        .bg-accent {
            background-color: #ffc508 !important;
        }

        .border-white-10 {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .bg-dark-glass {
            background-color: rgba(5, 17, 26, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }

        .bg-darker-glass {
            background-color: rgba(3, 10, 16, 0.8);
            backdrop-filter: blur(10px);
        }

        .bg-viewer {
            background-color: #030a10;
            background-image: radial-gradient(circle at 50% 50%, #0e2e45 0%, #030a10 70%);
        }

        .backdrop-blur {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .tiny {
            font-size: 0.7rem;
        }

        .line-height-1 {
            line-height: 1;
        }

        .shadow-gold {
            box-shadow: 0 4px 15px rgba(255, 197, 8, 0.2);
        }

        /* Customizer Sidebar Styles */
        .customizer-sidebar {
            z-index: 100;
        }

        .btn-config {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 12px;
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-config:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
            color: white;
        }

        .btn-config.active {
            background: rgba(255, 197, 8, 0.1);
            border-color: #ffc508;
            box-shadow: 0 0 15px rgba(255, 197, 8, 0.1);
            color: white;
        }

        .icon-box {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #ffc508;
        }

        .texture-option {
            border-radius: 50%;
            padding: 4px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .texture-option:hover {
            border-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .texture-option.active {
            border-color: #ffc508;
            box-shadow: 0 0 10px rgba(255, 197, 8, 0.3);
        }

        .texture-preview {
            aspect-ratio: 1/1;
            border-radius: 50%;
            width: 100%;
        }

        /* Custom Switch Styling */
        .custom-switch .form-check-input {
            width: 40px;
            height: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            margin-left: 10px;
        }

        .custom-switch .form-check-input:checked {
            background-color: #ffc508;
            border-color: #ffc508;
        }

        /* Viewer Controls */
        .btn-control-round {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-control-round:hover {
            background: rgba(255, 197, 8, 0.1);
            color: #ffc508;
            transform: translateY(-2px);
        }

        .btn-control-round.active {
            background: #ffc508;
            color: #0e2e45;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            position: relative;
        }

        .status-indicator.online {
            background-color: #00ff88;
            box-shadow: 0 0 10px #00ff88;
        }

        .status-indicator::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            border: 1px solid inherit;
            animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }

            80%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        /* Scrollbar */
        .customizer-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .customizer-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        .customizer-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .customizer-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 197, 8, 0.3);
        }

        /* Loader Animation */
        .glitch-loader {
            animation: bounce 2s infinite ease-in-out;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }


        @media (max-width: 768px) {
            .customizer-sidebar {
                max-height: 50vh;
            }
        }
    </style>

    @push('scripts')
        <!-- Three.js Library -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

        <script>
            let scene, camera, renderer, controls, model_group;
            let isRotating = false;
            let clock = new THREE.Clock();

            function init() {
                const container = document.getElementById('three-container');
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

                // Adding a stylized placeholder product
                createPlaceholderProduct();

                // 7. Event Listeners
                window.addEventListener('resize', onWindowResize);

                // Hide Loader
                setTimeout(() => {
                    document.getElementById('loader').style.display = 'none';
                }, 1500);

                animate();
            }

            function createPlaceholderProduct() {
                // Main Base
                const baseGeom = new THREE.BoxGeometry(2, 0.5, 2);
                const baseMat = new THREE.MeshStandardMaterial({
                    color: 0x1a1a1a,
                    roughness: 0.1,
                    metalness: 0.9
                });
                const base = new THREE.Mesh(baseGeom, baseMat);
                model_group.add(base);

                // Center Piece
                const centerGeom = new THREE.CylinderGeometry(0.8, 1, 1.5, 32);
                const centerMat = new THREE.MeshStandardMaterial({
                    color: 0xffc508,
                    roughness: 0.2,
                    metalness: 0.8
                });
                const center = new THREE.Mesh(centerGeom, centerMat);
                center.position.y = 1;
                center.name = 'base';
                model_group.add(center);

                // Accents
                const ringGeom = new THREE.TorusGeometry(1.1, 0.05, 16, 100);
                const ringMat = new THREE.MeshStandardMaterial({ color: 0xffffff, emissive: 0xffffff, emissiveIntensity: 0.5 });
                const ring = new THREE.Mesh(ringGeom, ringMat);
                ring.rotation.x = Math.PI / 2;
                ring.position.y = 0.5;
                ring.name = 'accents';
                model_group.add(ring);
            }

            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }

            function onWindowResize() {
                const container = document.getElementById('three-container');
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            }

            // --- Interaction Functions ---

            function resetCamera() {
                camera.position.set(4, 3, 8);
                controls.target.set(0, 0, 0);
            }

            function toggleAutoRotate() {
                isRotating = !isRotating;
                controls.autoRotate = isRotating;
                document.getElementById('btn-rotate').classList.toggle('active');
            }

            function downloadImage() {
                const link = document.createElement('a');
                link.download = 'custom-product-design.png';
                link.href = renderer.domElement.toDataURL('image/png');
                link.click();
            }

            $(document).ready(function () {
                init();

                // Texture Selection Logic
                $('.texture-option').on('click', function () {
                    $('.texture-option').removeClass('active');
                    $(this).addClass('active');

                    const texture = $(this).data('texture');
                    updateModelMaterial(texture);
                });

                // Component Selection Logic
                $('.btn-config').on('click', function () {
                    $('.btn-config').removeClass('active');
                    $(this).addClass('active');

                    $('.btn-config i.bi-check2-circle').removeClass('bi-check2-circle').addClass('bi-circle').addClass('opacity-25');
                    $(this).find('i').removeClass('bi-circle').removeClass('opacity-25').addClass('bi-check2-circle');
                });

                function updateModelMaterial(type) {
                    if (!model_group) return;

                    let color = 0xffc508;
                    let metal = 0.8;
                    let rough = 0.2;

                    switch (type) {
                        case 'gold': color = 0xffc508; metal = 0.9; rough = 0.1; break;
                        case 'carbon': color = 0x222222; metal = 0.5; rough = 0.5; break;
                        case 'chrome': color = 0xdddddd; metal = 0.95; rough = 0.05; break;
                        case 'matte-black': color = 0x111111; metal = 0.1; rough = 0.8; break;
                        case 'royal-blue': color = 0x0047AB; metal = 0.7; rough = 0.3; break;
                        case 'emerald': color = 0x50C878; metal = 0.7; rough = 0.3; break;
                    }

                    model_group.traverse((child) => {
                        if (child.isMesh && child.name === 'base') {
                            child.material.color.setHex(color);
                            child.material.metalness = metal;
                            child.material.roughness = rough;
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection