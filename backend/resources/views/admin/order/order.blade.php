@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
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
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Status Analytics -->
                    @php
                        $statusMeta = [
                            'pending'          => ['label' => 'Pending',          'icon' => 'bi-hourglass-split',   'bg' => 'rgba(255, 193, 7, 0.15)',  'color' => '#997404'],
                            'approved'         => ['label' => 'Approved',         'icon' => 'bi-clipboard-check',   'bg' => 'rgba(13, 110, 253, 0.12)', 'color' => '#0d6efd'],
                            'processing'       => ['label' => 'Processing',       'icon' => 'bi-arrow-repeat',      'bg' => 'rgba(13, 202, 240, 0.15)', 'color' => '#087990'],
                            'ready_for_pickup' => ['label' => 'Ready for Pickup', 'icon' => 'bi-bag-check',         'bg' => 'rgba(255, 153, 0, 0.15)',  'color' => '#b95900'],
                            'completed'        => ['label' => 'Completed',        'icon' => 'bi-check-circle-fill', 'bg' => 'rgba(25, 135, 84, 0.12)',  'color' => '#198754'],
                            'cancelled'        => ['label' => 'Cancelled',        'icon' => 'bi-x-circle-fill',     'bg' => 'rgba(220, 53, 69, 0.12)',  'color' => '#dc3545'],
                        ];
                    @endphp
                    <div class="row g-3 mb-4">
                        @foreach($statusMeta as $key => $meta)
                            @php
                                $isActive = $status === $key;
                                $count = $statusCounts[$key] ?? 0;
                                $cardUrl = $isActive
                                    ? route('admin.orders.index')
                                    : route('admin.orders.index', ['status' => $key]);
                            @endphp
                            <div class="col-6 col-md-4 col-xl-2">
                                <a href="{{ $cardUrl }}" class="text-decoration-none">
                                    <div class="card border-0 shadow-sm rounded-4 h-100 order-stat-card {{ $isActive ? 'order-stat-card-active' : '' }}"
                                        style="--stat-color: {{ $meta['color'] }};">
                                        <div class="card-body p-3 position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width: 42px; height: 42px; background-color: {{ $meta['bg'] }}; color: {{ $meta['color'] }};">
                                                    <i class="bi {{ $meta['icon'] }}"></i>
                                                </div>
                                                <div class="d-flex flex-column lh-1">
                                                    <span class="text-uppercase fw-bold text-muted"
                                                        style="font-size: 0.65rem; letter-spacing: 0.04em;">
                                                        {{ $meta['label'] }}
                                                    </span>
                                                    <h5 class="fw-bold text-dark mb-0 mt-1">{{ $count }}</h5>
                                                </div>
                                            </div>
                                            <small class="text-muted position-absolute"
                                                style="font-size: 0.65rem; bottom: 6px; right: 10px;">
                                                {{ $isActive ? 'Filtered' : 'Click to filter' }}
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <!-- Filters, Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="orderFilterForm" method="GET" action="{{ route('admin.orders.index') }}"
                                class="d-flex flex-nowrap align-items-center gap-2">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">
                                <div class="input-group flex-grow-1" style="min-width: 0;">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}"
                                        class="form-control border-start-0 rounded-end-2 ps-0"
                                        placeholder="Search by Order ID, Ref No, or Customer...">
                                </div>
                                <select name="status" class="form-select rounded-2 flex-shrink-0 w-auto"
                                    onchange="document.getElementById('orderFilterForm').submit()">
                                    <option value="">All Statuses</option>
                                    @foreach(['pending', 'approved', 'processing', 'ready_for_pickup', 'completed', 'cancelled'] as $s)
                                        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $s)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="date" class="form-select rounded-2 flex-shrink-0 w-auto"
                                    onchange="document.getElementById('orderFilterForm').submit()">
                                    <option value="">All Time</option>
                                    <option value="today" {{ $date === 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ $date === 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ $date === 'month' ? 'selected' : '' }}>This Month</option>
                                </select>
                                <a href="{{ route('admin.orders.index') }}"
                                    class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip"
                                    title="Reset filters">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0">
                                    <i class="bi bi-download small"></i>
                                    <span class="small fw-bold">Export</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Order ID</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Ref No
                                            </th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Customer
                                            </th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Date</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Items</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Total</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Status</th>
                                            <th
                                                class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($orders as $order)
                                            @php
                                                $customCount = $order->orderItems->whereNotNull('custom_design_id')->count();
                                            @endphp
                                            <tr>
                                                <td class="ps-4 py-3 fw-bold text-dark">
                                                    #{{ $order->order_number }}
                                                    @if($customCount > 0)
                                                        <span class="ms-1 text-primary" title="Contains Customized Items">
                                                            <i class="bi bi-palette-fill" style="font-size: 0.75rem;"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($order->payment_reference)
                                                        <span class="font-monospace small text-muted">
                                                            {{ $order->payment_reference }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center"
                                                            style="width: 36px; height: 36px; background-color: #0e2e45; color: #ffc508; font-size: 0.78rem; letter-spacing: 0.02em;">
                                                            {{ strtoupper(substr($order->user->fullname ?? 'G', 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark small mb-0">
                                                                {{ $order->user->fullname ?? 'Guest' }}
                                                            </div>
                                                            <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-muted small">{{ $order->created_at->format('M d, Y') }}</td>
                                                <td class="fw-bold text-dark">{{ $order->orderItems->count() }}</td>
                                                <td class="fw-bold text-primary">
                                                    ₱{{ number_format($order->total_amount, 2) }}
                                                </td>
                                                <td>
                                                    @php
                                                        [$statusBg, $statusColor, $statusIcon] = match ($order->status) {
                                                            'completed' => ['rgba(25, 135, 84, 0.12)', '#198754', 'bi-check-circle-fill'],
                                                            'approved' => ['rgba(13, 110, 253, 0.12)', '#0d6efd', 'bi-clipboard-check'],
                                                            'processing' => ['rgba(13, 202, 240, 0.15)', '#087990', 'bi-arrow-repeat'],
                                                            'ready_for_pickup' => ['rgba(255, 193, 7, 0.18)', '#997404', 'bi-bag-check'],
                                                            'cancelled' => ['rgba(220, 53, 69, 0.12)', '#dc3545', 'bi-x-circle-fill'],
                                                            default => ['rgba(255, 193, 7, 0.18)', '#997404', 'bi-hourglass-split'],
                                                        };
                                                    @endphp
                                                    <span
                                                        class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold"
                                                        style="background-color: {{ $statusBg }}; color: {{ $statusColor }};">
                                                        <i class="bi {{ $statusIcon }}" style="font-size: 0.75rem;"></i>
                                                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    @php
                                                        $orderItemsJson = json_encode($order->orderItems()->with(['product', 'customDesign'])->get());
                                                        $orderJson = json_encode($order);
                                                    @endphp
                                                    @if($order->status == 'pending')
                                                        <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm btn-review-order"
                                                            data-bs-toggle="modal" data-bs-target="#reviewOrderModal"
                                                            data-order="{{ $orderJson }}"
                                                            data-items="{{ $orderItemsJson }}">
                                                            <i class="bi bi-clipboard-check me-1"></i>Review
                                                        </button>
                                                    @else
                                                        <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold shadow-sm border"
                                                            data-bs-toggle="modal" data-bs-target="#viewOrderModal"
                                                            data-order="{{ $orderJson }}"
                                                            data-items="{{ $orderItemsJson }}">
                                                            <i class="bi bi-eye me-1 text-primary"></i>View
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox display-6 d-block mb-3 opacity-50"></i>
                                                    No orders found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div
                                class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="perPageSelect" class="text-muted small mb-0">Rows per page:</label>
                                    <select id="perPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                                        onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                        @foreach([10, 25, 50, 100] as $size)
                                            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>
                                                {{ $size }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-muted small">
                                        Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of
                                        {{ $orders->total() }} entries
                                    </span>
                                </div>
                                <nav>
                                    {{ $orders->links() }}
                                </nav>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    @include('auth.modal-logout')
    @include('admin.order.components.review-modal')
    @include('admin.order.components.view-modal')

    <style>
        /* ============================================
           Order Status Stat Cards
           ============================================ */
        .order-stat-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .order-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
            border-color: var(--stat-color);
        }
        .order-stat-card-active {
            border-color: var(--stat-color) !important;
            box-shadow: 0 0 0 2px var(--stat-color) inset, 0 0.25rem 0.75rem rgba(0, 0, 0, 0.06) !important;
        }
        .order-stat-card-active h3 { color: var(--stat-color) !important; }

        /* ============================================
           Order Review Modal Theme (mirrors product modal)
           ============================================ */
        .order-modal .modal-content { border-radius: 18px; }

        .order-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .order-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .order-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .order-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .order-close-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .order-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .order-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .order-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .order-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }

        .order-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .order-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .order-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .order-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .order-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* Reason quick-pick chips */
        .order-reason-chip {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .order-reason-chip:hover {
            background-color: #0e2e45;
            border-color: #0e2e45;
            color: #ffc508;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reviewModal = document.getElementById('reviewOrderModal');
            if (reviewModal) {
                reviewModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const order = JSON.parse(button.getAttribute('data-order'));
                    const items = JSON.parse(button.getAttribute('data-items'));

                    const form = document.getElementById('reviewOrderForm');
                    form.action = `/admin/orders/${order.order_id}/review`;

                    document.getElementById('reviewOrderNumber').textContent = '#' + order.order_number;
                    document.getElementById('reviewCustomerName').textContent = order.user ? (order.user.fullname || order.user.name || 'Guest') : 'Guest';

                    const tbody = document.getElementById('reviewItemsBody');
                    tbody.innerHTML = '';

                    items.forEach(item => {
                        const product = item.product || {};
                        const stock = product.stock ?? 0;
                        const isAvailable = stock >= item.quantity;
                        const design = item.custom_design || item.customDesign;
                        const isCustom = !!(item.custom_design_id && design);
                        const imgSrc = (isCustom && design.snapshot) ? design.snapshot : (product.image ? product.image : '/img/FABLAB-LOGO.png');
                        const customBadge = isCustom
                            ? '<span class="badge ms-1" style="background-color: rgba(255, 197, 8, 0.18); color: #997404; font-size: 0.55rem; vertical-align: middle;">TAILORED</span>'
                            : '<span class="badge ms-1" style="background-color: rgba(108, 117, 125, 0.12); color: #6c757d; font-size: 0.55rem; vertical-align: middle;">STANDARD</span>';

                        const row = `
                            <tr>
                                <td class="ps-3 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded border bg-white p-1 me-2" style="width: 40px; height: 40px;">
                                            <img src="${imgSrc}" class="w-100 h-100 object-fit-cover rounded" alt="">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">${product.name ?? '-'}${customBadge}</div>
                                            <div class="text-muted font-monospace" style="font-size: 0.7rem;">SKU: ${product.sku ?? '-'}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-dark">${item.quantity}</td>
                                <td class="text-center fw-bold ${stock < item.quantity ? 'text-danger' : 'text-success'}">${stock}</td>
                                <td class="text-end pe-3">
                                    ${isAvailable
                                        ? '<span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold" style="background-color: rgba(25,135,84,0.12); color: #198754;"><i class="bi bi-check-circle-fill" style="font-size:.7rem;"></i>Available</span>'
                                        : '<span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold" style="background-color: rgba(220,53,69,0.12); color: #dc3545;"><i class="bi bi-x-circle-fill" style="font-size:.7rem;"></i>Insufficient</span>'}
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                });
            }
        });
    </script>

    @push('scripts')
        <!-- Three.js Libraries -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

        <!-- Customizer Core Logic (Excluding UI Handlers) -->
        <script src="{{ asset('js/customizer/state.js') }}"></script>
        <script src="{{ asset('js/customizer/core.js') }}"></script>
        <script src="{{ asset('js/customizer/models/mug.js') }}"></script>
        <script src="{{ asset('js/customizer/models/t-shirt.js') }}"></script>
        <script src="{{ asset('js/customizer/models/shorts.js') }}"></script>
        <script src="{{ asset('js/customizer/models/umbrella.js') }}"></script>
        <script src="{{ asset('js/customizer/rendering.js') }}"></script>
        <script src="{{ asset('js/customizer/persistence.js') }}"></script>

        <script>
            // Design Inspection popout — opened from the "View Order" modal's customized items
            $(document).on('click', '.btn-popout-design', function () {
                const design = $(this).data('design');
                if (!design) return;

                $('#detailPopupImage').attr('src', design.snapshot || '').removeClass('d-none');
                $('#preview-loader').removeClass('d-none').html(
                    '<div class="spinner-border text-warning mb-2" role="status"></div>' +
                    '<div class="fw-bold text-uppercase opacity-75" style="font-size: 0.7rem; letter-spacing: 0.06em;">Initializing 3D Scene...</div>'
                );

                let recipe = design.recipe;
                if (typeof recipe === 'string') {
                    try { recipe = JSON.parse(recipe); } catch (e) { recipe = {}; }
                }
                recipe = recipe || {};
                $('#detailPopupRecipe').text(JSON.stringify(recipe, null, 4));

                const modal = new bootstrap.Modal(document.getElementById('designDetailPopup'));
                modal.show();

                setTimeout(() => {
                    try {
                        const container = document.getElementById('admin-three-container');
                        if (container.querySelector('canvas')) {
                            container.querySelector('canvas').remove();
                            if (typeof renderer !== 'undefined' && renderer) {
                                renderer.dispose();
                                renderer = null;
                            }
                        }

                        const itemName = (design.product_name || '').toLowerCase();
                        let baseShape = 't-shirt';
                        if (itemName.includes('mug')) baseShape = 'mug';
                        else if (itemName.includes('umbrella')) baseShape = 'umbrella';
                        else if (itemName.includes('shorts')) baseShape = 'shorts';
                        else if (recipe.base_style) baseShape = recipe.base_style;

                        window.CustomizerConfig = {
                            initialShape: baseShape,
                            activeColor: recipe.color || 'blue'
                        };

                        init('admin-three-container');

                        setTimeout(() => {
                            loadDesignRecipePreview(recipe);
                            $('#preview-loader').addClass('d-none');
                            $('#detailPopupImage').addClass('d-none');
                        }, 800);
                    } catch (err) {
                        console.error('3D Preview failed to initialize:', err);
                        $('#preview-loader').html('<div class="text-danger small">3D engine failed to start. Showing snapshot instead.</div>');
                        $('#detailPopupImage').removeClass('d-none');
                    }
                }, 500);
            });

            $('#designDetailPopup').on('hidden.bs.modal', function () {
                const container = document.getElementById('admin-three-container');
                if (container && container.querySelector('canvas')) {
                    container.querySelector('canvas').remove();
                }
                if (typeof renderer !== 'undefined' && renderer) {
                    renderer.dispose();
                    renderer = null;
                }
            });
        </script>
    @endpush
@endsection
