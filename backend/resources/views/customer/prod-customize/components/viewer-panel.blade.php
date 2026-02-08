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
        <button class="btn btn-control-round" onclick="toggleAutoRotate()" id="btn-rotate" title="Auto Rotate">
            <i class="bi bi-arrow-repeat"></i>
        </button>
        <button class="btn btn-control-round" onclick="downloadImage()" title="Snapshot">
            <i class="bi bi-camera"></i>
        </button>
    </div>

    <!-- Status Display -->
    <div class="position-absolute top-0 end-0 m-4">
        <div class="bg-dark-glass p-3 rounded-4 border border-white-10 backdrop-blur d-flex align-items-center gap-3">
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
            <span class="text-white-50 small"><i class="bi bi-info-circle me-1"></i> Left Click to Rotate | Right Click
                to Pan | Scroll to Zoom</span>
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