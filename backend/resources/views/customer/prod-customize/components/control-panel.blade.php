<!-- LEFT PANEL: Components, Features, Textures -->
<div class="col-md-4 col-lg-3 d-flex flex-column border-end border-white-10 bg-dark-glass customizer-sidebar">
    <div class="p-3 p-md-4 border-bottom border-white-10">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-box-seam text-accent"></i>
            <h5 class="text-white fw-bold mb-0">{{ $product ? $product->name : 'Customizer' }}</h5>
        </div>
        <p class="text-white-50 small mb-0">
            {{ $product ? 'Configuring ' . $product->name : 'Configure your unique design' }}
        </p>
    </div>

    <div class="flex-grow-1 overflow-y-auto p-3 p-md-4 customizer-scrollbar">
        @if(!$product)
        <!-- Step 0: Choose Base Shape -->
        <div class="mb-5">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">0. Choose Base
                Style</label>
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-shape {{ ($initialShape ?? 't-shirt') === 't-shirt' ? 'active' : '' }} w-100"
                        data-shape="t-shirt" title="Cotton T-Shirt">
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

                <div class="col-6">
                    <button class="btn btn-shape {{ ($initialShape ?? 't-shirt') === 'mug' ? 'active' : '' }} w-100"
                        data-shape="mug" title="Ceramic Mug">
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

                <div class="col-6">
                    <button class="btn btn-shape {{ ($initialShape ?? 't-shirt') === 'umbrella' ? 'active' : '' }} w-100"
                        data-shape="umbrella" title="Golf Umbrella">
                        <div class="shape-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="width: 24px; height: 24px;">
                                <path d="M23 12a11.05 11.05 0 0 0-22 0z"></path>
                                <path d="M18 19a3 3 0 0 1-6 0v-7"></path>
                            </svg>
                        </div>
                        <div class="tiny mt-1 fw-bold">Umbrella</div>
                    </button>
                </div>

                <div class="col-6">
                    <button class="btn btn-shape {{ ($initialShape ?? 't-shirt') === 'bag' ? 'active' : '' }} w-100"
                        data-shape="bag" title="Tote Bag">
                        <div class="shape-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="width: 24px; height: 24px;">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </div>
                        <div class="tiny mt-1 fw-bold">Bag</div>
                    </button>
                </div>
            </div>
        </div>
        @endif

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



        <!-- Step 2: Materials & Finishes — a plain colour OR a patterned texture,
             never both. Picking one clears the other. -->
        <div class="mb-5">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">2. Materials &
                Finishes</label>

            @if($colors->isNotEmpty())
                <div class="finish-group-label tiny text-white-50 fw-bold mb-2">
                    <i class="bi bi-palette me-1"></i>PLAIN COLORS
                </div>
                <div class="row g-3 mb-4">
                    @foreach($colors as $index => $color)
                        {{-- A texture is the default finish when the product has any;
                             otherwise the first colour stands in as the default. --}}
                        <div class="col-3 text-center">
                            <div class="color-option {{ $textures->isEmpty() && $index === 0 ? 'active' : '' }}"
                                data-color-id="{{ $color->color_id }}"
                                data-hex="{{ $color->hex_code }}"
                                data-price-modifier="{{ $color->price_modifier ?? 0 }}"
                                title="{{ $color->name }}{{ $color->price_modifier > 0 ? ' (+₱' . number_format($color->price_modifier, 2) . ')' : '' }}">
                                <div class="color-preview" style="background-color: {{ $color->hex_code }};"></div>
                            </div>
                            <div class="texture-price tiny fw-bold mt-1 text-truncate"
                                style="color: {{ $color->price_modifier > 0 ? '#ffc508' : 'rgba(255,255,255,0.5)' }};">
                                {{ $color->price_modifier > 0 ? '+₱' . number_format($color->price_modifier, 2) : 'Free' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($colors->isNotEmpty() && $textures->isNotEmpty())
                <div class="finish-group-label tiny text-white-50 fw-bold mb-2">
                    <i class="bi bi-layers me-1"></i>TEXTURES
                </div>
            @endif

            @if($textures->isEmpty() && $colors->isEmpty())
                <div class="text-white-50 small text-center py-3 border border-white-10 rounded">
                    <i class="bi bi-info-circle me-1"></i> No finishes available for this product.
                </div>
            @elseif($textures->isNotEmpty())
                <div class="row g-3">
                    @foreach($textures as $index => $texture)
                        <div class="col-3 text-center">
                            <div class="texture-option {{ $index === 0 ? 'active' : '' }}"
                                data-texture-id="{{ $texture->texture_id }}"
                                data-image-path="{{ $texture->image_url }}"
                                data-price-modifier="{{ $texture->price_modifier ?? 0 }}"
                                title="{{ $texture->name }}{{ $texture->price_modifier > 0 ? ' (+₱' . number_format($texture->price_modifier, 2) . ')' : '' }}">
                                @if($texture->image_url)
                                    <div class="texture-preview" style="background-image: url('{{ $texture->image_url }}'); background-size: cover; background-position: center;"></div>
                                @else
                                    <div class="texture-preview d-flex align-items-center justify-content-center bg-secondary text-white-50">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="texture-price tiny fw-bold mt-1 text-truncate"
                                style="color: {{ $texture->price_modifier > 0 ? '#ffc508' : 'rgba(255,255,255,0.5)' }};">
                                {{ $texture->price_modifier > 0 ? '+₱' . number_format($texture->price_modifier, 2) : 'Free' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Design panel picker — populated from the loaded model's zones, and
             hidden entirely for models that only have one. -->
        <div class="mb-5" id="zonePicker" style="display: none;">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-2 d-block">Design Area</label>
            <div id="zoneTabs" class="d-flex flex-wrap gap-2"></div>
            <div class="tiny text-white-50 mt-2">
                Text, shapes and logos are added to the selected area. Switching moves the
                view round to it.
            </div>
        </div>

        <!-- Step 3: Custom Text -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="text-accent small text-uppercase fw-bold tracking-wider mb-0">3. Custom Text
                    <span class="ms-1 fw-normal" style="font-size: 0.65rem; text-transform: none; color: rgba(255,255,255,0.6);">+₱{{ number_format($rates['text'] ?? 50, 2) }} each</span>
                </label>
                <button type="button" id="addTextBtn"
                    class="btn btn-tiny btn-outline-accent rounded-pill px-2 py-1 small" style="font-size: 0.65rem;">
                    <i class="bi bi-plus-lg"></i> Add Text
                </button>
            </div>
            <div id="textList" class="d-grid gap-3">
                <!-- Dynamic text items go here -->
            </div>
        </div>

        <!-- Step 4: Custom Shapes -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="text-accent small text-uppercase fw-bold tracking-wider mb-0">4. Custom Shapes
                    <span class="ms-1 fw-normal" style="font-size: 0.65rem; text-transform: none; color: rgba(255,255,255,0.6);">+₱{{ number_format($rates['shape'] ?? 30, 2) }} each</span>
                </label>
                <button type="button" id="addShapeBtn"
                    class="btn btn-tiny btn-outline-accent rounded-pill px-2 py-1 small" style="font-size: 0.65rem;">
                    <i class="bi bi-plus-lg"></i> Add Shape
                </button>
            </div>
            <div id="shapeList" class="d-grid gap-3">
                <!-- Dynamic shape items go here -->
            </div>
        </div>

        <!-- Step 5: Custom Logos / Images -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="text-accent small text-uppercase fw-bold tracking-wider mb-0">5. Custom Logos
                    <span class="ms-1 fw-normal" style="font-size: 0.65rem; text-transform: none; color: rgba(255,255,255,0.6);">+₱{{ number_format($rates['logo'] ?? 150, 2) }} each at 1&times; size, priced by size</span>
                </label>
                <div>
                    <input type="file" id="logoInput" class="d-none" accept="image/*,.svg">
                    <button type="button" onclick="document.getElementById('logoInput').click()"
                        class="btn btn-tiny btn-outline-accent rounded-pill px-2 py-1 small"
                        style="font-size: 0.65rem;">
                        <i class="bi bi-upload"></i> Upload Logo
                    </button>
                </div>
            </div>
            <div id="logoList" class="d-grid gap-3">
                <!-- Dynamic logo items go here -->
            </div>
        </div>

        <!-- Step 6: Additional Features -->
        <div class="mb-4">
            <label class="text-accent small text-uppercase fw-bold tracking-wider mb-3 d-block">6. Additional
                Features</label>

            <div class="feature-item">
                <div class="form-check form-switch custom-switch p-0 d-flex justify-content-between align-items-center">
                    <label class="form-check-label text-white small" for="lighting">Internal LED Lighting
                        <span class="ms-1 fw-bold" style="font-size: 0.7rem; color: #ffc508;">+₱{{ number_format($rates['led_lighting'] ?? 500, 2) }}</span>
                    </label>
                    <input class="form-check-input" type="checkbox" id="lighting">
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Footer -->
    <div class="p-3 p-md-4 border-top border-white-10 bg-darker-glass">
        <div class="d-flex justify-content-between align-items-end mb-3 mb-md-4">
            <div>
                <div class="text-white-50 tiny text-uppercase fw-bold mb-1">Total Estimated Price</div>
                <div id="total-price-display" class="text-white h4 fw-bold mb-0">
                    ₱{{ number_format($product->price ?? 0, 2) }}</div>
            </div>
            <div class="text-end">
                <span id="customization-fee-badge"
                    class="badge bg-accent text-primary rounded-pill px-3">Standard</span>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-6">
                <button id="btn-save-design"
                    class="btn btn-outline-light border-white-10 w-100 rounded-pill py-2 small fw-bold"
                    {{ $requiresSelection ? 'disabled' : '' }}>
                    <i class="bi bi-save me-1"></i> Save
                </button>
            </div>
            <div class="col-6">
                <button id="btn-add-to-cart-custom"
                    class="btn btn-accent w-100 rounded-pill py-2 small fw-bold shadow-gold"
                    {{ $requiresSelection ? 'disabled' : '' }}>
                    <i class="bi bi-cart-plus me-1"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>