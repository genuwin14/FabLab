@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('admin.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                {{-- Full width, like the other admin pages. The two panels sit
                     side by side from lg up so the whole price list is on screen
                     at once, instead of size surcharges hiding below the fold. --}}
                <div class="container-fluid">
                    {{-- Save sits with the title. It is outside the form, so it
                         carries form="pricingForm" to submit it from up here. --}}
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 gap-sm-3 mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Customization Pricing</h4>
                            <p class="text-muted small mb-0">
                                What the 3D customizer adds on top of a product's own price. These apply the moment you
                                save — to the live quote customers see and to what the cart charges.
                            </p>
                        </div>
                        <button type="submit" form="pricingForm"
                            class="btn btn-primary fw-semibold px-4 flex-shrink-0 text-nowrap pricing-save-btn">
                            <i class="bi bi-check2-circle me-1"></i>Save Pricing
                        </button>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="fw-semibold mb-1">That price list wasn't saved.</div>
                            <ul class="mb-0 ps-3 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="pricingForm" method="POST" action="{{ route('admin.customization-pricing.update') }}" class="pricing-form">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 g-md-4 align-items-start">

                        <!-- Panel 1: what a customer adds to the design -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm pricing-card">
                                <div class="card-header bg-white border-0 pt-3 pt-md-4 pb-2 px-3 px-md-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="pricing-section-icon"><i class="bi bi-cash-stack"></i></span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">Design element rates</h6>
                                            <small class="text-muted">Charged per element a customer adds to their design.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-3 px-md-4 pb-3 pb-md-4">
                                    @foreach($rates['elements'] ?? [] as $key => $rate)
                                        <div class="rate-entry py-3 border-top">
                                            <div class="pricing-row d-flex justify-content-between align-items-start gap-3">
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold text-dark">
                                                        <i class="bi {{ $rate['icon'] }} me-1 text-muted"></i>{{ $rate['label'] }}
                                                    </div>
                                                    <small class="text-muted">{{ $rate['description'] }}</small>
                                                </div>
                                                <div class="pricing-input flex-shrink-0">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white text-muted fw-semibold">₱</span>
                                                        <input type="number" step="0.01" min="0" max="999999.99"
                                                            class="form-control text-end fw-semibold @error('rates.' . $key) is-invalid @enderror"
                                                            name="rates[{{ $key }}]"
                                                            id="rate-{{ $key }}"
                                                            data-rate-key="{{ $key }}"
                                                            value="{{ old('rates.' . $key, number_format($rate['amount'], 2, '.', '')) }}"
                                                            aria-label="{{ $rate['label'] }} rate">
                                                    </div>
                                                    <small class="text-muted d-block text-end mt-1">{{ $rate['suffix'] }}</small>
                                                </div>
                                            </div>
                                            @include('admin.customization-pricing.partials.materials', ['key' => $key, 'rate' => $rate, 'materials' => $materials])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Panel 2: what the ordered size adds, with the image
                             scale reference tucked underneath it -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm pricing-card">
                                <div class="card-header bg-white border-0 pt-3 pt-md-4 pb-2 px-3 px-md-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="pricing-section-icon"><i class="bi bi-rulers"></i></span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">Size surcharges</h6>
                                            <small class="text-muted">
                                                Added on top when a customer orders that size. Only one ever applies to an
                                                item. Leave a size at 0 to charge nothing extra for it.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-3 px-md-4 pb-3 pb-md-4">
                                    @foreach($rates['sizes'] ?? [] as $key => $rate)
                                        <div class="rate-entry py-3 border-top">
                                            <div class="pricing-row d-flex justify-content-between align-items-start gap-3">
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold text-dark">
                                                        <i class="bi {{ $rate['icon'] }} me-1 text-muted"></i>{{ $rate['label'] }}
                                                    </div>
                                                    <small class="text-muted">{{ $rate['description'] }}</small>
                                                </div>
                                                <div class="pricing-input flex-shrink-0">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white text-muted fw-semibold">₱</span>
                                                        <input type="number" step="0.01" min="0" max="999999.99"
                                                            class="form-control text-end fw-semibold @error('rates.' . $key) is-invalid @enderror"
                                                            name="rates[{{ $key }}]"
                                                            id="rate-{{ $key }}"
                                                            value="{{ old('rates.' . $key, number_format($rate['amount'], 2, '.', '')) }}"
                                                            aria-label="{{ $rate['label'] }} surcharge">
                                                    </div>
                                                    <small class="text-muted d-block text-end mt-1">{{ $rate['suffix'] }}</small>
                                                </div>
                                            </div>
                                            @include('admin.customization-pricing.partials.materials', ['key' => $key, 'rate' => $rate, 'materials' => $materials])
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm pricing-card mt-3 mt-md-4">
                                <div class="card-header bg-white border-0 pt-3 pt-md-4 pb-2 px-3 px-md-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="pricing-section-icon"><i class="bi bi-aspect-ratio"></i></span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">What an uploaded image costs by size</h6>
                                            <small class="text-muted">
                                                The image rate is multiplied by the Size slider, which runs
                                                {{ rtrim(rtrim(number_format($logoMinScale, 2), '0'), '.') }}× to
                                                {{ rtrim(rtrim(number_format($logoMaxScale, 2), '0'), '.') }}×.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-3 px-md-4 pb-3 pb-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0 scale-preview-table">
                                            <thead>
                                                <tr class="text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                                    <th class="ps-0 fw-semibold">Printed size</th>
                                                    @foreach([$logoMinScale, 0.5, 1, 2, $logoMaxScale] as $sample)
                                                        <th class="text-end fw-semibold">{{ rtrim(rtrim(number_format($sample, 2), '0'), '.') }}×</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="ps-0 text-muted small">Charged</td>
                                                    @foreach([$logoMinScale, 0.5, 1, 2, $logoMaxScale] as $sample)
                                                        <td class="text-end fw-semibold text-dark scale-preview-cell"
                                                            data-scale="{{ $sample }}">
                                                            ₱{{ number_format(($rates['elements']['logo']['amount'] ?? 0) * $sample, 2) }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Updates as you type. Sizes beyond the slider's range are clamped to it, so this is
                                        the full span of what one image can cost.
                                    </small>
                                </div>
                            </div>
                        </div>

                        </div>

                        <div class="alert alert-light border rounded-4 small text-muted mt-3 mt-md-4 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Changing these does not reprice designs already sitting in a cart or on a placed order —
                            those keep the price they were quoted. Product base prices are set under
                            <a href="{{ route('admin.products.index') }}" class="fw-semibold">Products</a>, and per-texture
                            surcharges under <a href="{{ route('admin.textures.index') }}" class="fw-semibold">Textures</a>.
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <style>
        .pricing-card { border-radius: 14px; }

        .pricing-section-icon {
            width: 40px;
            height: 40px;
            /* It sits in a d-flex beside the heading, so without this the panel
               with the longest description squashes its icon narrower than the
               others and the three stop matching. */
            flex-shrink: 0;
            border-radius: 10px;
            background-color: #0e2e45;
            color: #ffc508;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        /* The border moved to .rate-entry when each option grew a materials
           list beneath its price, so the divider sits between options rather
           than between a price and its own materials. */
        .rate-entry:first-of-type { border-top: 0 !important; }

        .pricing-input { width: 190px; }

        .text-muted-2 { color: #9aa4b2; font-size: 0.78rem; }

        .rate-materials .material-row .form-select,
        .rate-materials .material-row .form-control { font-size: 0.82rem; }

        /* The quantity is narrow on purpose — four decimals of ink still fits,
           and the material name is the part worth the width. */
        .rate-materials .material-qty { width: 110px; flex-shrink: 0; }

        .rate-materials .add-material-btn { font-size: 0.78rem; }

        /* Not a d-block utility — those are !important, and this element is
           shown and hidden through the hidden attribute. */
        .rate-materials .material-empty { display: block; }
        .rate-materials .material-empty[hidden] { display: none; }

        .pricing-input .form-control:focus {
            border-color: rgba(14, 46, 69, 0.4);
            box-shadow: 0 0 0 0.2rem rgba(255, 197, 8, 0.25);
        }

        .scale-preview-table th,
        .scale-preview-table td { white-space: nowrap; }

        /* ── Mobile ( < sm ) — ResponsiveMobileNote.md §2 ── */
        @media (max-width: 575.98px) {
            .container-fluid h4 { font-size: 1.15rem; }

            /* Stack the label above its price field rather than squeezing both
               onto one line. */
            .pricing-row {
                flex-direction: column;
                gap: 0.75rem !important;
            }
            .pricing-input { width: 100%; }
            .pricing-input small { text-align: left !important; }

            /* The header stacks at this width, so Save gets its own full-width
               row under the title rather than a stub button beside it. */
            .pricing-save-btn { width: 100%; }
        }
    </style>

    <script>
        // Keep the "what an image costs by size" row honest while the admin is
        // still typing, so the effect of a new rate is visible before saving.
        (function () {
            const input = document.getElementById('rate-logo');
            const cells = document.querySelectorAll('.scale-preview-cell');
            if (!input || !cells.length) return;

            const peso = value => '₱' + Number(value).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            function refresh() {
                const rate = parseFloat(input.value);
                cells.forEach(cell => {
                    const scale = parseFloat(cell.dataset.scale);
                    cell.textContent = Number.isFinite(rate) ? peso(rate * scale) : '—';
                });
            }

            input.addEventListener('input', refresh);
            refresh();
        })();

        // Add and remove rows in an option's bill of materials.
        //
        // Row indexes only have to be unique within an option — the controller
        // reads the posted rows as a list and re-keys them on the material id —
        // so a counter that only ever goes up is enough, and removing a row
        // doesn't need the survivors renumbered.
        (function () {
            document.querySelectorAll('.rate-materials').forEach(block => {
                const key = block.dataset.rateKey;
                const rows = block.querySelector('.material-rows');
                const empty = block.querySelector('.material-empty');
                const addBtn = block.querySelector('.add-material-btn');
                let nextIndex = rows.querySelectorAll('.material-row').length;

                const syncEmpty = () => {
                    empty.hidden = rows.querySelectorAll('.material-row').length > 0;
                };

                const template = block.querySelector('.material-row-template');

                addBtn?.addEventListener('click', () => {
                    if (!template) return;

                    const row = template.content.firstElementChild.cloneNode(true);
                    const index = nextIndex++;

                    // The template's fields are unnamed, so they can't post
                    // from inside the <template>. Naming them is what puts the
                    // new row into the form.
                    row.querySelector('select').name = `materials[${key}][${index}][raw_material_id]`;
                    row.querySelector('.material-qty').name = `materials[${key}][${index}][quantity]`;

                    rows.appendChild(row);
                    syncEmpty();
                    row.querySelector('select').focus();
                });

                rows.addEventListener('click', event => {
                    const remove = event.target.closest('.remove-material-btn');
                    if (!remove) return;

                    remove.closest('.material-row').remove();
                    syncEmpty();
                });

                syncEmpty();
            });
        })();
    </script>
@endsection
