@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('staff.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="staffSidebarOffcanvas"
            aria-labelledby="staffSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('staff.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('staff.partials.navbar')
            </header>

            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                {{-- The admin's own screen with the inputs taken out: this is a
                     reference card, so staff can quote a customer without having
                     to ask. Every amount here is read-only — there is no staff
                     route that writes to customization_rates. --}}
                <div class="container-fluid">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 gap-sm-3 mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Customization Pricing</h4>
                            <p class="text-muted small mb-0">
                                What the 3D customizer adds on top of a product's own price. Use this to tell a customer
                                what an extra element will cost them.
                            </p>
                        </div>
                        <span class="badge pricing-readonly-badge flex-shrink-0 text-nowrap">
                            <i class="bi bi-eye me-1"></i>View only
                        </span>
                    </div>

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
                                        <div class="pricing-row d-flex justify-content-between align-items-start gap-3 py-3 border-top">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold text-dark">
                                                    <i class="bi {{ $rate['icon'] }} me-1 text-muted"></i>{{ $rate['label'] }}
                                                </div>
                                                <small class="text-muted">{{ $rate['description'] }}</small>
                                            </div>
                                            <div class="pricing-amount flex-shrink-0 text-end">
                                                <div class="fw-bold text-dark pricing-amount-value">
                                                    ₱{{ number_format($rate['amount'], 2) }}
                                                </div>
                                                <small class="text-muted d-block mt-1">{{ $rate['suffix'] }}</small>
                                            </div>
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
                                                item. A size at ₱0.00 costs nothing extra.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-3 px-md-4 pb-3 pb-md-4">
                                    @foreach($rates['sizes'] ?? [] as $key => $rate)
                                        <div class="pricing-row d-flex justify-content-between align-items-start gap-3 py-3 border-top">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold text-dark">
                                                    <i class="bi {{ $rate['icon'] }} me-1 text-muted"></i>{{ $rate['label'] }}
                                                </div>
                                                <small class="text-muted">{{ $rate['description'] }}</small>
                                            </div>
                                            <div class="pricing-amount flex-shrink-0 text-end">
                                                <div class="fw-bold text-dark pricing-amount-value">
                                                    ₱{{ number_format($rate['amount'], 2) }}
                                                </div>
                                                <small class="text-muted d-block mt-1">{{ $rate['suffix'] }}</small>
                                            </div>
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
                                                        <td class="text-end fw-semibold text-dark">
                                                            ₱{{ number_format(($rates['elements']['logo']['amount'] ?? 0) * $sample, 2) }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Sizes beyond the slider's range are clamped to it, so this is the full span of what
                                        one image can cost.
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="alert alert-light border rounded-4 small text-muted mt-3 mt-md-4 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Only an admin can change these rates. A design already sitting in a cart or on a placed order keeps
                        the price it was quoted, so a rate change never moves an existing order. Per-texture surcharges are
                        under <a href="{{ route('staff.textures.index') }}" class="fw-semibold">Textures</a>, per-colour ones
                        under <a href="{{ route('staff.colors.index') }}" class="fw-semibold">Colors</a>.
                    </div>
                </div>
            </main>
        </div>
    </div>

    <style>
        .pricing-card { border-radius: 14px; }

        .pricing-section-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: #0e2e45;
            color: #ffc508;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .pricing-row:first-of-type { border-top: 0 !important; }

        .pricing-amount { min-width: 120px; }
        .pricing-amount-value { font-size: 1.05rem; }

        .pricing-readonly-badge {
            background-color: rgba(14, 46, 69, 0.08);
            color: #0e2e45;
            border: 1px solid rgba(14, 46, 69, 0.15);
            font-weight: 600;
            padding: 0.5rem 0.85rem;
            border-radius: 999px;
        }

        .scale-preview-table th,
        .scale-preview-table td { white-space: nowrap; }

        /* ── Mobile ( < sm ) — ResponsiveMobileNote.md §2 ── */
        @media (max-width: 575.98px) {
            .container-fluid h4 { font-size: 1.15rem; }

            /* The amount keeps its own line under the label rather than being
               squeezed against it. */
            .pricing-row {
                flex-direction: column;
                gap: 0.5rem !important;
            }
            .pricing-amount {
                min-width: 0;
                text-align: left !important;
            }
        }
    </style>
@endsection
