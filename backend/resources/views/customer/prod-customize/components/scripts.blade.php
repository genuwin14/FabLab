@push('scripts')
    <!-- Three.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

    <script>
        let scene, camera, renderer, controls, model_group;
        let isRotating = false;
        let clock = new THREE.Clock();
        let internalLight = null;

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

            // Initial model based on product type
            const initialShape = "{{ $initialShape ?? 't-shirt' }}";
            if (initialShape === 'mug') {
                createMugModel();
                $('.btn-shape[data-shape="mug"]').addClass('active');
            } else {
                createTshirtModel();
                $('.btn-shape[data-shape="t-shirt"]').addClass('active');
            }

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


        function getActiveColor() {
            const activeTexture = $('.texture-option.active').data('texture');
            switch (activeTexture) {
                case 'blue': return 0x0000FF;
                case 'black': return 0x000000;
                case 'white': return 0xFFFFFF;
                case 'yellow': return 0xFFC107;
                default: return 0x0000FF;
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

        function loadDesignRecipe(recipe) {
            if (!recipe) return;

            // Load Base Attributes
            if (recipe.color) {
                $(`.texture-option[data-texture="${recipe.color}"]`).trigger('click');
            }
            if (recipe.size) {
                $(`.btn-size[data-size="${recipe.size}"]`).trigger('click');
            }
            if (recipe.features && recipe.features.led_lighting) {
                $('#lighting').prop('checked', true).trigger('change');
            }

            // Load Elements (Simplified approach: trigger button clicks then fill values)
            if (recipe.elements) {
                // Text Elements
                if (recipe.elements.text) {
                    recipe.elements.text.forEach(txt => {
                        $('#addTextBtn').trigger('click');
                        const $item = $('#textList .customizer-item').last();
                        $item.find('.text-input').val(txt.text);
                        $item.find('.font-select').val(txt.font);
                        $item.find('.color-input').val(txt.color);
                        $item.find('.x-range').val(txt.x);
                        $item.find('.y-range').val(txt.y);
                        $item.find('.scale-range').val(txt.scale);
                    });
                }

                // Shape Elements
                if (recipe.elements.shapes) {
                    recipe.elements.shapes.forEach(shp => {
                        $('#addShapeBtn').trigger('click');
                        const $item = $('#shapeList .customizer-item').last();
                        $item.find('.type-select').val(shp.type);
                        $item.find('.color-input').val(shp.color);
                        $item.find('.x-range').val(shp.x);
                        $item.find('.y-range').val(shp.y);
                        $item.find('.scale-range').val(shp.scale);
                        $item.find('.rotation-range').val(shp.rotation);
                    });
                }

                // Logo Elements (Trickier because of image objects)
                if (recipe.elements.logos) {
                    recipe.elements.logos.forEach(logo => {
                        const img = new Image();
                        img.onload = function () {
                            const html = `
                                    <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="tiny text-white-50 fw-bold">LOGO ELEMENT (LOADED)</span>
                                            <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <div class="bg-white rounded p-1" style="width: 40px; height: 40px;">
                                                <img src="${logo.src}" style="width: 100%; height: 100%; object-fit: contain;">
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-3">
                                                <label class="tiny text-white-50 d-block mb-1">X</label>
                                                <input type="range" class="form-range x-range" min="-50" max="50" value="${logo.x}">
                                            </div>
                                            <div class="col-3">
                                                <label class="tiny text-white-50 d-block mb-1">Y</label>
                                                <input type="range" class="form-range y-range" min="-50" max="50" value="${logo.y}">
                                            </div>
                                            <div class="col-3">
                                                <label class="tiny text-white-50 d-block mb-1">Size</label>
                                                <input type="range" class="form-range scale-range" min="0.1" max="5" step="0.1" value="${logo.scale}">
                                            </div>
                                            <div class="col-3">
                                                <label class="tiny text-white-50 d-block mb-1">Rot</label>
                                                <input type="range" class="form-range rotation-range" min="0" max="360" value="${logo.rotation}">
                                            </div>
                                        </div>
                                    </div>`;
                            const $item = $(html);
                            $item.data('img-obj', img);
                            $('#logoList').append($item);
                            syncElementsAndRender();
                        };
                        img.src = logo.src;
                    });
                }
            }

            syncElementsAndRender();
        }

        $(document).ready(function () {
            init();

            // Load existing design if present
            @if(isset($design))
                setTimeout(() => {
                    loadDesignRecipe(@json($design->recipe));
                }, 1000);
            @endif

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

            let textElements = [];
            let shapeElements = [];
            let logoElements = [];

            function renderOverlayOnCanvas(ctx, colorCss) {
                // Fill background color
                ctx.fillStyle = colorCss;
                ctx.fillRect(0, 0, 1024, 1024);

                // 1. Draw Shapes first (background layer)
                shapeElements.forEach(shape => {
                    ctx.save();
                    const sx = 512 + (shape.x * 10);
                    const sy = 512 + (shape.y * 10);
                    ctx.translate(sx, sy);
                    ctx.rotate(shape.rotation * Math.PI / 180);
                    ctx.fillStyle = shape.color;

                    if (shape.type === 'circle') {
                        ctx.beginPath();
                        ctx.arc(0, 0, 50 * shape.scale, 0, Math.PI * 2);
                        ctx.fill();
                    } else if (shape.type === 'line') {
                        const w = 200 * shape.scale;
                        const h = 20 * shape.scale;
                        ctx.fillRect(-w / 2, -h / 2, w, h);
                    }
                    ctx.restore();
                });

                // 2. Draw Logos (middle layer)
                logoElements.forEach(logo => {
                    if (!logo.img || !logo.img.complete) return;
                    ctx.save();
                    const lx = 512 + (logo.x * 10);
                    const ly = 512 + (logo.y * 10);
                    ctx.translate(lx, ly);
                    ctx.rotate(logo.rotation * Math.PI / 180);

                    const w = logo.img.width;
                    const h = logo.img.height;
                    const aspect = w / h;

                    // Base size 200px wide
                    const targetW = 200 * logo.scale;
                    const targetH = targetW / aspect;

                    ctx.drawImage(logo.img, -targetW / 2, -targetH / 2, targetW, targetH);
                    ctx.restore();
                });

                // 3. Draw Text (foreground layer)
                textElements.forEach(textElem => {
                    if (!textElem.text.trim()) return;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = `bold ${48 * textElem.scale}px "${textElem.font}"`;
                    ctx.fillStyle = textElem.color;

                    const tx = 512 + (textElem.x * 10);
                    const ty = 512 + (textElem.y * 10);

                    ctx.translate(tx, ty);
                    ctx.fillText(textElem.text, 0, 0);
                    ctx.restore();
                });
            }

            function updateModelMaterial(type) {
                if (!model_group) return;
                let color = 0x0000FF;
                let metal = 0.0;
                let rough = 0.7; // Matte/non-glossy
                switch (type) {
                    case 'blue': color = 0x0000FF; break;
                    case 'black': color = 0x000000; break;
                    case 'white': color = 0xFFFFFF; break;
                    case 'yellow': color = 0xFFC107; break;
                }

                const colorCss = '#' + color.toString(16).padStart(6, '0');

                model_group.traverse((child) => {
                    // Check if mesh should receive the "base" material
                    if (child.isMesh && (child.name === 'base' || (child.parent && child.parent.name === 'base'))) {

                        // Always check if we have elements to draw
                        if (textElements.length > 0 || shapeElements.length > 0 || logoElements.length > 0) {
                            const canvas = document.createElement('canvas');
                            canvas.width = 1024;
                            canvas.height = 1024;
                            const ctx = canvas.getContext('2d');

                            renderOverlayOnCanvas(ctx, colorCss);

                            const texture = new THREE.CanvasTexture(canvas);
                            texture.flipY = false;

                            if (child.userData.originalMap === undefined) {
                                child.userData.originalMap = child.material.map || null;
                            }

                            child.material.map = texture;
                            child.material.color.setHex(0xFFFFFF); // use white base so map retains original colors
                        } else {
                            if (child.userData.originalMap !== undefined) {
                                child.material.map = child.userData.originalMap;
                            } else {
                                child.material.map = null;
                            }
                            child.material.color.setHex(color);
                        }

                        child.material.metalness = metal;
                        child.material.roughness = rough;
                        child.material.needsUpdate = true;
                    }
                });
            }

            // UI Management
            function syncElementsAndRender() {
                // Sync Text
                textElements = [];
                $('#textList .customizer-item').each(function () {
                    textElements.push({
                        text: $(this).find('.text-input').val(),
                        font: $(this).find('.font-select').val(),
                        color: $(this).find('.color-input').val(),
                        x: parseFloat($(this).find('.x-range').val()),
                        y: parseFloat($(this).find('.y-range').val()),
                        scale: parseFloat($(this).find('.scale-range').val())
                    });
                });

                // Sync Shapes
                shapeElements = [];
                $('#shapeList .customizer-item').each(function () {
                    shapeElements.push({
                        type: $(this).find('.type-select').val(),
                        color: $(this).find('.color-input').val(),
                        x: parseFloat($(this).find('.x-range').val()),
                        y: parseFloat($(this).find('.y-range').val()),
                        scale: parseFloat($(this).find('.scale-range').val()),
                        rotation: parseFloat($(this).find('.rotation-range').val())
                    });
                });

                // Sync Logos
                logoElements = [];
                $('#logoList .customizer-item').each(function () {
                    logoElements.push({
                        img: $(this).data('img-obj'),
                        x: parseFloat($(this).find('.x-range').val()),
                        y: parseFloat($(this).find('.y-range').val()),
                        scale: parseFloat($(this).find('.scale-range').val()),
                        rotation: parseFloat($(this).find('.rotation-range').val())
                    });
                });

                const activeType = $('.texture-option.active').data('texture') || 'blue';
                updateModelMaterial(activeType);
                calculateCustomPrice();
            }

            function calculateCustomPrice() {
                const basePrice = {{ $product->price ?? 0 }};
                let extra = 0;

                extra += textElements.length * 50;
                extra += shapeElements.length * 30;
                extra += logoElements.length * 150;

                if ($('#lighting').is(':checked')) {
                    extra += 500;
                }

                const total = basePrice + extra;
                $('#total-price-display').text('₱' + total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                if (extra > 0) {
                    $('#customization-fee-badge').text('+ ₱' + extra.toLocaleString() + ' Custom').removeClass('bg-accent text-primary').addClass('bg-warning text-dark');
                } else {
                    $('#customization-fee-badge').text('Standard').addClass('bg-accent text-primary').removeClass('bg-warning text-dark');
                }
            }

            // Handle Logo Upload
            $('#logoInput').on('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (event) {
                    const img = new Image();
                    img.onload = function () {
                        const html = `
                                                            <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="tiny text-white-50 fw-bold">LOGO ELEMENT</span>
                                                                    <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                                                                </div>
                                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                                    <div class="bg-white rounded p-1" style="width: 40px; height: 40px;">
                                                                        <img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: contain;">
                                                                    </div>
                                                                    <div class="tiny text-white-50 truncate fw-bold" style="max-width: 150px;">${file.name}</div>
                                                                </div>
                                                                <div class="row g-2">
                                                                    <div class="col-3">
                                                                        <label class="tiny text-white-50 d-block mb-1">X</label>
                                                                        <input type="range" class="form-range x-range" min="-50" max="50" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <label class="tiny text-white-50 d-block mb-1">Y</label>
                                                                        <input type="range" class="form-range y-range" min="-50" max="50" value="0">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <label class="tiny text-white-50 d-block mb-1">Size</label>
                                                                        <input type="range" class="form-range scale-range" min="0.1" max="5" step="0.1" value="1">
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <label class="tiny text-white-50 d-block mb-1">Rot</label>
                                                                        <input type="range" class="form-range rotation-range" min="0" max="360" value="0">
                                                                    </div>
                                                                </div>
                                                            </div>`;
                        const $item = $(html);
                        $item.data('img-obj', img);
                        $('#logoList').append($item);
                        syncElementsAndRender();
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);

                // Clear input so same file can be uploaded again if deleted
                $(this).val('');
            });

            $('#addTextBtn').on('click', function () {
                const html = `
                                                    <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="tiny text-white-50 fw-bold">TEXT ELEMENT</span>
                                                            <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                                                        </div>
                                                        <input type="text" class="form-control form-control-sm bg-dark border-white-10 text-white text-input mb-2 shadow-none" placeholder="Enter text...">
                                                        <div class="row g-2 mb-2">
                                                            <div class="col-8">
                                                                <select class="form-select form-select-sm bg-dark border-white-10 text-white font-select shadow-none">
                                                                    <option value="Arial">Arial</option>
                                                                    <option value="Times New Roman">Times New Roman</option>
                                                                    <option value="Courier New">Courier New</option>
                                                                    <option value="Impact">Impact</option>
                                                                    <option value="Comic Sans MS">Comic Sans</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <input type="color" class="form-control form-control-sm form-control-color bg-dark border-white-10 w-100 color-input mb-0" value="#ffffff">
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-4">
                                                                <label class="tiny text-white-50 d-block mb-1">X Pos</label>
                                                                <input type="range" class="form-range x-range" min="-50" max="50" value="0">
                                                            </div>
                                                            <div class="col-4">
                                                                <label class="tiny text-white-50 d-block mb-1">Y Pos</label>
                                                                <input type="range" class="form-range y-range" min="-50" max="50" value="0">
                                                            </div>
                                                            <div class="col-4">
                                                                <label class="tiny text-white-50 d-block mb-1">Size</label>
                                                                <input type="range" class="form-range scale-range" min="0.5" max="4" step="0.1" value="1">
                                                            </div>
                                                        </div>
                                                    </div>`;
                $('#textList').append(html);
                syncElementsAndRender();
            });

            $('#addShapeBtn').on('click', function () {
                const html = `
                                                    <div class="customizer-item p-3 border border-white-10 rounded bg-dark-glass mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="tiny text-white-50 fw-bold">SHAPE ELEMENT</span>
                                                            <button type="button" class="btn btn-link text-danger p-0 delete-btn"><i class="bi bi-trash"></i></button>
                                                        </div>
                                                        <div class="row g-2 mb-2">
                                                            <div class="col-8">
                                                                <select class="form-select form-select-sm bg-dark border-white-10 text-white type-select shadow-none">
                                                                    <option value="circle">Circle</option>
                                                                    <option value="line">Line</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <input type="color" class="form-control form-control-sm form-control-color bg-dark border-white-10 w-100 color-input mb-0" value="#ffffff">
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-3">
                                                                <label class="tiny text-white-50 d-block mb-1">X</label>
                                                                <input type="range" class="form-range x-range" min="-50" max="50" value="0">
                                                            </div>
                                                            <div class="col-3">
                                                                <label class="tiny text-white-50 d-block mb-1">Y</label>
                                                                <input type="range" class="form-range y-range" min="-50" max="50" value="0">
                                                            </div>
                                                            <div class="col-3">
                                                                <label class="tiny text-white-50 d-block mb-1">Size</label>
                                                                <input type="range" class="form-range scale-range" min="0.1" max="5" step="0.1" value="1">
                                                            </div>
                                                            <div class="col-3">
                                                                <label class="tiny text-white-50 d-block mb-1">Rot</label>
                                                                <input type="range" class="form-range rotation-range" min="0" max="360" value="0">
                                                            </div>
                                                        </div>
                                                    </div>`;
                $('#shapeList').append(html);
                syncElementsAndRender();
            });

            $(document).on('input change', '.customizer-item input, .customizer-item select', syncElementsAndRender);
            $(document).on('click', '.delete-btn', function () {
                $(this).closest('.customizer-item').remove();
                syncElementsAndRender();
            });

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

            // Additional Features Logic

            $('#lighting').on('change', function () {
                const isLit = $(this).is(':checked');

                if (isLit) {
                    if (!internalLight) {
                        // Create a warm glowing light
                        internalLight = new THREE.PointLight(0xffaa00, 2, 10);
                        internalLight.position.set(0, 0, 0); // Position inside the model
                        scene.add(internalLight);
                    }
                } else if (internalLight) {
                    scene.remove(internalLight);
                    internalLight = null;
                }
                if (typeof calculateCustomPrice === 'function') {
                    calculateCustomPrice();
                }
            });

            function serializeDesign() {
                const activeShape = $('.btn-shape.active').data('shape') || 't-shirt';
                const activeSize = $('.btn-size.active').data('size') || 'medium';
                const activeColor = $('.texture-option.active').data('texture') || 'blue';
                const ledLighting = $('#lighting').is(':checked');

                return JSON.stringify({
                    base_style: activeShape,
                    size: activeSize,
                    color: activeColor,
                    features: {
                        led_lighting: ledLighting
                    },
                    elements: {
                        text: textElements,
                        shapes: shapeElements,
                        logos: logoElements.map(logo => ({
                            x: logo.x,
                            y: logo.y,
                            scale: logo.scale,
                            rotation: logo.rotation,
                            src: logo.img.src // Base64 or URL
                        }))
                    }
                });
            }

            function captureSnapshot() {
                if (!renderer || !scene || !camera) return null;
                // Render one last time to be sure
                renderer.render(scene, camera);
                return renderer.domElement.toDataURL('image/png');
            }

            $('#btn-save-design').on('click', function () {
                const btn = $(this);
                const productId = new URLSearchParams(window.location.search).get('product_id');

                const recipe = serializeDesign();
                const snapshot = captureSnapshot();

                // Visual feedback
                const originalContent = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('customer.customize.save') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: productId,
                        custom_recipe: recipe,
                        custom_snapshot: snapshot
                    },
                    success: function (response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON ? xhr.responseJSON.message : 'Error saving design';
                        showToast(message, 'error');
                    },
                    complete: function () {
                        btn.html(originalContent);
                        btn.prop('disabled', false);
                    }
                });
            });

            $('#btn-add-to-cart-custom').on('click', function () {
                const btn = $(this);
                const productId = new URLSearchParams(window.location.search).get('product_id');

                if (!productId) {
                    showToast('Please select a base product first to add to cart!', 'warning');
                    return;
                }

                const recipe = serializeDesign();
                const snapshot = captureSnapshot();

                // Visual feedback
                const originalContent = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Adding...');
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('customer.cart.add') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: productId,
                        quantity: 1,
                        custom_recipe: recipe,
                        custom_snapshot: snapshot
                    },
                    success: function (response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            if (window.parent && window.parent.updateCartBadge) {
                                window.parent.updateCartBadge(response.cart_count);
                            } else if (typeof updateCartBadge === 'function') {
                                updateCartBadge(response.cart_count);
                            }
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding to cart';
                        showToast(message, 'error');
                    },
                    complete: function () {
                        btn.html(originalContent);
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush