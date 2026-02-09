<!-- LEFT PANEL: Components, Features, Textures -->
<div class="col-md-4 col-lg-3 d-flex flex-column border-end border-white-10 bg-dark-glass customizer-sidebar">
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
        <!-- Step 0: Choose Base Shape -->
        <div class="mb-5">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">0. Choose Base
                Style</label>
            <div class="row g-2">
                <div class="col-4">
                    <button class="btn btn-shape active w-100" data-shape="t-shirt" title="Cotton T-Shirt">
                        <div class="shape-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="width: 24px; height: 24px;">
                                <path
                                    d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z">
                                </path>
                            </svg>
                        </div>
                        <div class="tiny mt-1 fw-bold">T-Shirt</div>
                    </button>
                </div>
                <div class="col-4">
                    <button class="btn btn-shape w-100" data-shape="shorts" title="Casual Shorts">
                        <div class="shape-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="width: 24px; height: 24px;">
                                <path d="M4 2v10l3 10h4v-7h2v7h4l3-10V2H4z"></path>
                            </svg>
                        </div>
                        <div class="tiny mt-1 fw-bold">Shorts</div>
                    </button>
                </div>
                <div class="col-4">
                    <button class="btn btn-shape w-100" data-shape="mug" title="Ceramic Mug">
                        <div class="shape-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="width: 24px; height: 24px;">
                                <path d="M17 8h1a4 4 0 1 1 0 8h-1"></path>
                                <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"></path>
                                <line x1="6" y1="2" x2="6" y2="4"></line>
                                <line x1="10" y1="2" x2="10" y2="4"></line>
                                <line x1="14" y1="2" x2="14" y2="4"></line>
                            </svg>
                        </div>
                        <div class="tiny mt-1 fw-bold">Mug</div>
                    </button>
                </div>
                <div class="col-4">
                    <button class="btn btn-shape w-100" data-shape="umbrella" title="Vintage Umbrella">
                        <div class="shape-icon">
                            <i class="bi bi-umbrella"></i>
                        </div>
                        <div class="tiny mt-1 fw-bold">Umbrella</div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 1: Select Size -->
        <div class="mb-5">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">1. Select Size</label>
            <div class="row g-2">
                <div class="col-4">
                    <button class="btn btn-size w-100" data-size="small" title="Small Size">
                        <div class="shape-icon fw-bold">S</div>
                        <div class="tiny mt-1 fw-bold">Small</div>
                    </button>
                </div>
                <div class="col-4">
                    <button class="btn btn-size active w-100" data-size="medium" title="Medium Size">
                        <div class="shape-icon fw-bold">M</div>
                        <div class="tiny mt-1 fw-bold">Medium</div>
                    </button>
                </div>
                <div class="col-4">
                    <button class="btn btn-size w-100" data-size="large" title="Large Size">
                        <div class="shape-icon fw-bold">L</div>
                        <div class="tiny mt-1 fw-bold">Large</div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Component Selection -->
        <div class="mb-5">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">2. Select
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

        <!-- Step 3: Materials & Textures -->
        <div class="mb-5">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">3. Materials &
                Finishes</label>
            <div class="row g-3">
                <div class="col-4">
                    <div class="texture-option active" data-texture="gold" title="Gold">
                        <div class="texture-preview" style="background: linear-gradient(45deg, #FFD700, #FDB931);">
                        </div>
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

        <!-- Step 4: Additional Features -->
        <div class="mb-4">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">4. Additional
                Features</label>
            <div class="feature-item mb-3">
                <div class="form-check form-switch custom-switch p-0 d-flex justify-content-between align-items-center">
                    <label class="form-check-label text-white small" for="glossyFinish">Premium Glossy Finish</label>
                    <input class="form-check-input" type="checkbox" id="glossyFinish" checked>
                </div>
            </div>
            <div class="feature-item mb-3">
                <div class="form-check form-switch custom-switch p-0 d-flex justify-content-between align-items-center">
                    <label class="form-check-label text-white small" for="engraving">Laser Engraving</label>
                    <input class="form-check-input" type="checkbox" id="engraving">
                </div>
            </div>
            <div class="feature-item">
                <div class="form-check form-switch custom-switch p-0 d-flex justify-content-between align-items-center">
                    <label class="form-check-label text-white small" for="lighting">Internal LED Lighting</label>
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