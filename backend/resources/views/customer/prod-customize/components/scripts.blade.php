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

            // Initial model: T-Shirt
            createTshirtModel();

            // 7. Event Listeners
            window.addEventListener('resize', onWindowResize);

            // Hide Loader
            setTimeout(() => {
                const loader = document.getElementById('loader');
                if (loader) loader.style.display = 'none';
            }, 1500);

            animate();
        }

        // --- Model Creation Functions ---
        @include('customer.prod-customize.components.models.mug')
        @include('customer.prod-customize.components.models.t-shirt')
        @include('customer.prod-customize.components.models.shorts')
        @include('customer.prod-customize.components.models.umbrella')

        function getActiveColor() {
            const activeTexture = $('.texture-option.active').data('texture');
            switch (activeTexture) {
                case 'gold': return 0xffc508;
                case 'carbon': return 0x222222;
                case 'chrome': return 0xdddddd;
                case 'matte-black': return 0x111111;
                case 'royal-blue': return 0x0047AB;
                case 'emerald': return 0x50C878;
                default: return 0xffffff;
            }
        }

        function animate() {
            requestAnimationFrame(animate);
            if (controls) controls.update();
            if (renderer && scene && camera) renderer.render(scene, camera);
        }

        function onWindowResize() {
            const container = document.getElementById('three-container');
            if (!container) return;
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
            const btnRotate = document.getElementById('btn-rotate');
            if (btnRotate) btnRotate.classList.toggle('active');
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

            // Size Selection Logic
            $('.btn-size').on('click', function () {
                $('.btn-size').removeClass('active');
                $(this).addClass('active');
                const size = $(this).data('size');
                updateModelSize(size);
            });

            // Shape Selection Logic
            $('.btn-shape').on('click', function () {
                $('.btn-shape').removeClass('active');
                $(this).addClass('active');
                const shape = $(this).data('shape');
                model_group.scale.set(1, 1, 1);
                if (shape === 'mug') createMugModel();
                else if (shape === 't-shirt') createTshirtModel();
                else if (shape === 'shorts') createShortsModel();
                else if (shape === 'umbrella') createUmbrellaModel();
                const currentTexture = $('.texture-option.active').data('texture');
                if (currentTexture) updateModelMaterial(currentTexture);

                // Keep the current size when switching shapes
                const currentSize = $('.btn-size.active').data('size');
                if (currentSize) updateModelSize(currentSize);
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
                    // Check if mesh should receive the "base" material
                    if (child.isMesh && (child.name === 'base' || (child.parent && child.parent.name === 'base'))) {
                        child.material.color.setHex(color);
                        child.material.metalness = metal;
                        child.material.roughness = rough;
                    }
                });
            }

            function updateModelSize(size) {
                if (!model_group) return;
                let scale = 1.0;
                if (size === 'small') scale = 0.85;
                else if (size === 'medium') scale = 1.0;
                else if (size === 'large') scale = 1.15;

                // Visual feedback: brief pop animation
                model_group.scale.set(scale * 1.05, scale * 1.05, scale * 1.05);
                setTimeout(() => {
                    model_group.scale.set(scale, scale, scale);
                }, 100);
            }
        });
    </script>
@endpush