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
            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Status Analytics -->
                    @php
                        $statusMeta = [
                            'draft'     => ['label' => 'Draft',     'icon' => 'bi-pencil-square',    'bg' => 'rgba(108, 117, 125, 0.15)', 'color' => '#5a6268'],
                            'sent'      => ['label' => 'Sent',      'icon' => 'bi-send-fill',        'bg' => 'rgba(13, 110, 253, 0.12)',  'color' => '#0d6efd'],
                            'confirmed' => ['label' => 'Confirmed', 'icon' => 'bi-check2-circle',    'bg' => 'rgba(13, 202, 240, 0.15)',  'color' => '#087990'],
                            'delivered' => ['label' => 'Delivered', 'icon' => 'bi-truck',            'bg' => 'rgba(25, 135, 84, 0.12)',   'color' => '#198754'],
                            'cancelled' => ['label' => 'Cancelled', 'icon' => 'bi-x-circle-fill',    'bg' => 'rgba(220, 53, 69, 0.12)',   'color' => '#dc3545'],
                        ];
                    @endphp
                    <div class="row g-3 mb-4">
                        @foreach($statusMeta as $key => $meta)
                            @php
                                $isActive = $status === $key;
                                $count = $statusCounts[$key] ?? 0;
                                $cardUrl = $isActive
                                    ? route('admin.purchase.index')
                                    : route('admin.purchase.index', ['status' => $key]);
                            @endphp
                            <div class="col-6 col-md-4 col-xl">
                                <a href="{{ $cardUrl }}" class="text-decoration-none">
                                    <div class="card border-0 shadow-sm rounded-4 h-100 purchase-stat-card {{ $isActive ? 'purchase-stat-card-active' : '' }}"
                                        style="--stat-color: {{ $meta['color'] }};">
                                        <div class="card-body p-3 position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-3 d-none d-lg-flex align-items-center justify-content-center flex-shrink-0"
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
                            <form id="purchaseFilterForm" method="GET" action="{{ route('admin.purchase.index') }}"
                                class="row g-2 align-items-center">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">

                                <!-- Search: inline on desktop; icon-triggered dropdown on mobile. -->
                                <div class="col-auto col-lg dropdown search-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none search-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Search">
                                        <i class="bi bi-search text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu search-menu border-0 shadow p-2 p-lg-0">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                                <i class="bi bi-search text-muted"></i>
                                            </span>
                                            <input type="text" name="search" value="{{ $search }}"
                                                class="form-control border-start-0 rounded-end-2 ps-0"
                                                placeholder="Search by PO Number or Supplier...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Status + Date filters: inline on desktop; behind a filter icon on mobile. -->
                                <div class="col-auto dropdown filter-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none filter-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Filters">
                                        <i class="bi bi-funnel text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu filter-menu border-0 shadow p-2 p-lg-0">
                                        <div class="d-flex flex-column flex-lg-row gap-2">
                                            <select name="status" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('purchaseFilterForm').submit()">
                                                <option value="">All Statuses</option>
                                                @foreach(array_keys($statusMeta) as $s)
                                                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>
                                                        {{ $statusMeta[$s]['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <select name="date" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('purchaseFilterForm').submit()">
                                                <option value="">All Time</option>
                                                <option value="today" {{ $date === 'today' ? 'selected' : '' }}>Today</option>
                                                <option value="week" {{ $date === 'week' ? 'selected' : '' }}>This Week</option>
                                                <option value="month" {{ $date === 'month' ? 'selected' : '' }}>This Month</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <a href="{{ route('admin.purchase.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip"
                                        title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#createPOModal" title="Create PO">
                                        <i class="bi bi-plus-lg small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Create PO</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- PO Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden data-table-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                PO Number</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Supplier</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Date</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Status</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">
                                                Total Cost</th>
                                            <th
                                                class="py-3 pe-4 text-primary small text-uppercase fw-bold border-0 text-end">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($purchaseOrders as $po)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <span class="font-monospace fw-bold text-primary">{{ $po->po_number }}</span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $po->supplier->name ?? '—' }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        {{ $po->items->count() }} {{ Str::plural('Item', $po->items->count()) }}
                                                    </div>
                                                </td>
                                                <td class="small text-muted">{{ $po->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    @php
                                                        $meta = $statusMeta[$po->status] ?? $statusMeta['draft'];
                                                    @endphp
                                                    <span
                                                        class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold text-uppercase"
                                                        style="background-color: {{ $meta['bg'] }}; color: {{ $meta['color'] }}; font-size: 0.7rem; letter-spacing: 0.04em;">
                                                        <i class="bi {{ $meta['icon'] }}" style="font-size: 0.75rem;"></i>
                                                        {{ $meta['label'] }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold text-primary">
                                                    ₱{{ number_format($po->total_cost, 2) }}
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('admin.purchase.show', $po->purchase_order_id) }}"
                                                        class="btn btn-light btn-sm rounded-pill px-3 fw-bold shadow-sm border">
                                                        <i class="bi bi-eye me-1 text-primary"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="bi bi-cart-x display-6 d-block mb-3 opacity-50"></i>
                                                    No purchase orders found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <!-- Pagination sits INSIDE .table-responsive, so it
                                     rides the table's single horizontal scrollbar. -->
                                <div
                                    class="pagination-bar border-top d-flex justify-content-between align-items-center gap-2 p-3">
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <label for="perPageSelect" class="text-muted small mb-0">Rows per page:</label>
                                    <select id="perPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                                        onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                        @foreach([10, 25, 50, 100] as $size)
                                            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>
                                                {{ $size }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-muted small text-nowrap">
                                        Showing {{ $purchaseOrders->firstItem() ?? 0 }} to
                                        {{ $purchaseOrders->lastItem() ?? 0 }} of {{ $purchaseOrders->total() }} entries
                                    </span>
                                </div>
                                <nav class="flex-shrink-0">
                                    {{ $purchaseOrders->links() }}
                                </nav>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Purchase Order Modal -->
    <div class="modal fade purchase-modal" id="createPOModal" tabindex="-1" aria-labelledby="createPOModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <!-- Themed Dark Header -->
                <div class="purchase-modal-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="purchase-eyebrow">Admin</span>
                            <span class="purchase-eyebrow-divider">/</span>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="createPOModalLabel">
                                Create Purchase Order
                            </h5>
                        </div>
                        <button type="button" class="purchase-close-btn" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <form action="{{ route('admin.purchase.store') }}" method="POST" id="poForm" class="m-0">
                    @csrf

                    <div class="modal-body p-4 bg-white">
                        <div class="row g-3 g-md-4">
                            <!-- PO Settings -->
                            <div class="col-lg-4">
                                <div class="purchase-side-panel rounded-4 p-4 h-100">
                                    <h6 class="purchase-section-title">
                                        <i class="bi bi-clipboard-data me-2"></i>Order Details
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">
                                            Supplier <span class="text-danger">*</span>
                                        </label>
                                        <select name="supplier_id" id="supplierSelect"
                                            class="form-select purchase-field-input" required
                                            {{ $selectedSupplierId ? 'readonly style="pointer-events: none;"' : '' }}>
                                            <option value="" disabled {{ !$selectedSupplierId ? 'selected' : '' }}>
                                                Select Supplier
                                            </option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->supplier_id }}"
                                                    {{ $selectedSupplierId == $supplier->supplier_id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($selectedSupplierId)
                                            <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">
                                            <div class="form-text text-primary mt-2 small">
                                                <i class="bi bi-lock-fill me-1"></i>Supplier locked from suggestions
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">
                                            Expected Delivery
                                        </label>
                                        <input type="date" name="expected_delivery_date"
                                            class="form-control purchase-field-input">
                                    </div>

                                    <div class="alert border-0 d-flex align-items-start gap-2 p-3 rounded-3 mb-0"
                                        style="background-color: rgba(13, 110, 253, 0.08); color: #0d6efd;">
                                        <i class="bi bi-info-circle-fill mt-1"></i>
                                        <div class="small">
                                            This PO will be saved as <strong>Draft</strong>. You can review and send it
                                            later.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="col-lg-8">
                                <div class="card border shadow-none rounded-4 h-100">
                                    <div
                                        class="card-header bg-white border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0 small text-uppercase">
                                            <i class="bi bi-box-seam me-2 text-primary"></i>Order Items
                                        </h6>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 purchase-btn-save"
                                            id="addItemBtn">
                                            <i class="bi bi-plus-lg me-1"></i>Add Item
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                            <table class="table table-hover align-middle mb-0 modal-table" id="itemsTable">
                                                <thead class="sticky-top" style="z-index: 1;">
                                                    <tr class="bg-primary bg-opacity-10">
                                                        <th class="ps-3 py-2 text-primary small text-uppercase fw-bold border-0"
                                                            style="width: 40%">Product</th>
                                                        <th class="py-2 text-primary small text-uppercase fw-bold border-0"
                                                            style="width: 15%">Qty</th>
                                                        <th class="py-2 text-primary small text-uppercase fw-bold border-0"
                                                            style="width: 20%">Cost (₱)</th>
                                                        <th class="py-2 text-primary small text-uppercase fw-bold border-0 text-end"
                                                            style="width: 20%">Total</th>
                                                        <th class="border-0" style="width: 5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="itemsBody" class="border-top-0">
                                                    <!-- Items will be injected here -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <div id="emptyState"
                                            class="text-center p-5 text-muted {{ !empty($prefillItems) ? 'd-none' : '' }}">
                                            <div class="mb-3">
                                                <i class="bi bi-basket3 fs-1 opacity-25"></i>
                                            </div>
                                            <p class="mb-0 small">No items added yet. Select a supplier and add products.
                                            </p>
                                        </div>

                                        <div class="p-3 border-top"
                                            style="background-color: rgba(13, 110, 253, 0.05);">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-muted text-uppercase small">Grand Total</span>
                                                <h4 class="fw-bold text-primary mb-0">
                                                    ₱<span id="grandTotal">0.00</span>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="purchase-modal-footer">
                        <button type="button" class="btn purchase-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn purchase-btn-save rounded-pill px-4" id="saveBtn">
                            <i class="bi bi-check-lg me-1"></i>Create Purchase Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* ============================================
           Purchase Status Stat Cards
           ============================================ */
        .purchase-stat-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .purchase-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
            border-color: var(--stat-color);
        }
        .purchase-stat-card-active {
            border-color: var(--stat-color) !important;
            box-shadow: 0 0 0 2px var(--stat-color) inset, 0 0.25rem 0.75rem rgba(0, 0, 0, 0.06) !important;
        }
        .purchase-stat-card-active h5 { color: var(--stat-color) !important; }

        /* ============================================
           Purchase Modal Theme (mirrors product modal)
           ============================================ */
        .purchase-modal .modal-content { border-radius: 18px; }

        .purchase-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .purchase-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .purchase-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .purchase-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .purchase-close-btn {
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
        .purchase-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .purchase-side-panel {
            background-color: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .purchase-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .purchase-field-input {
            background-color: #fff !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .purchase-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }

        .purchase-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .purchase-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .purchase-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .purchase-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .purchase-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* ============================================
           Mobile responsiveness ( < lg / 992px )
           See ResponsiveMobileNote.md
           ============================================ */
        @media (min-width: 992px) {
            .search-dd .dropdown-menu.search-menu,
            .filter-dd .dropdown-menu.filter-menu {
                position: static !important;
                display: block !important;
                float: none;
                width: 100%;
                margin: 0;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                background: transparent;
            }
            .filter-dd .filter-menu .form-select { width: auto !important; }
        }
        @media (max-width: 991.98px) {
            .search-dd .dropdown-menu.search-menu {
                width: min(82vw, 360px);
            }
            .filter-dd .dropdown-menu.filter-menu {
                width: min(82vw, 320px);
            }

            /* Edge-to-edge scrollable table; pagination shares its scroll. */
            .data-table-card {
                margin-left: -0.75rem;
                margin-right: -0.75rem;
                border-radius: 0 !important;
            }
            .data-table-card .table {
                min-width: 880px;
            }
            .data-table-card .table th,
            .data-table-card .table td {
                white-space: nowrap;
            }
            .data-table-card .table th:nth-child(2),
            .data-table-card .table td:nth-child(2) {
                min-width: 180px;
            }
            .pagination-bar {
                flex-wrap: nowrap;
                min-width: 880px;
            }
            .pagination-bar .pagination {
                --bs-pagination-padding-x: 0.5rem;
                --bs-pagination-padding-y: 0.25rem;
                --bs-pagination-font-size: 0.8rem;
                margin-bottom: 0;
            }

            /* Create-PO modal (modal-xl, 2-column + dynamic items table). */
            .modal-title { font-size: 1rem; }
            .modal-body { font-size: 0.85rem; }
            .modal .form-label,
            .modal .form-control,
            .modal .form-select,
            .modal .input-group-text,
            .modal .btn,
            .modal small,
            .modal .small { font-size: 0.8rem; }
            .modal .purchase-section-title { font-size: 0.62rem; }
            .purchase-modal .modal-dialog { margin: 0.5rem; }
            .purchase-modal-header { padding: 14px 16px; }
            .purchase-modal .modal-body.p-4 { padding: 1rem !important; }
            .purchase-side-panel { padding: 1rem !important; }

            .purchase-modal-footer {
                padding: 12px 16px;
                flex-direction: column;
                align-items: stretch;
            }
            .purchase-modal-footer > .btn { width: 100%; }

            /* Dynamic items table: keep columns from squishing — swipe. */
            .modal-table { min-width: 560px; }
            .modal-table th,
            .modal-table td { white-space: nowrap; }
        }
    </style>

    <script>
        const supplierItems = @json($supplierItems);
        const prefillData = @json($prefillItems);
        let rowCount = 0;

        document.addEventListener('DOMContentLoaded', function () {
            const supplierSelect = document.getElementById('supplierSelect');
            const itemsBody = document.getElementById('itemsBody');
            const addItemBtn = document.getElementById('addItemBtn');
            const emptyState = document.getElementById('emptyState');
            const createModal = document.getElementById('createPOModal');

            // Open modal automatically if pre-filled data exists (from suggestions)
            if (prefillData && prefillData.length > 0) {
                const modal = new bootstrap.Modal(createModal);
                modal.show();

                if (emptyState) emptyState.classList.add('d-none');
                prefillData.forEach(item => {
                    addRow(item);
                });
            }

            // Handle Supplier Change
            // The confirmation modal is async, so remember the supplier the rows
            // belong to and roll the dropdown back when the change is declined.
            let currentSupplierId = supplierSelect.value;

            supplierSelect.addEventListener('change', function () {
                if (itemsBody.children.length === 0) {
                    currentSupplierId = supplierSelect.value;
                    return;
                }

                const nextSupplierId = supplierSelect.value;
                showConfirmModal({
                    title: 'Change Supplier',
                    subtitle: 'The current items will be cleared',
                    message: 'Changing the supplier will clear all current items. Proceed?',
                    variant: 'warning',
                    confirmText: 'Yes, Change Supplier',
                    cancelText: 'Keep Items'
                }).then(function (confirmed) {
                    if (!confirmed) {
                        supplierSelect.value = currentSupplierId;
                        return;
                    }
                    currentSupplierId = nextSupplierId;
                    itemsBody.innerHTML = '';
                    calculateGrandTotal();
                    if (emptyState) emptyState.classList.remove('d-none');
                });
            });

            addItemBtn.addEventListener('click', function () {
                if (!supplierSelect.value) {
                    showAlertModal({
                        title: 'Supplier Required',
                        message: 'Please select a supplier first.',
                        variant: 'warning'
                    });
                    return;
                }
                if (emptyState) emptyState.classList.add('d-none');
                addRow();
            });

            function addRow(data = null) {
                const supplierId = supplierSelect.value;
                const itemsForSupplier = supplierItems[supplierId] || [];

                if (itemsForSupplier.length === 0 && !data) {
                    showAlertModal({
                        title: 'Nothing to Order',
                        message: 'This supplier has no assigned products or raw materials.',
                        variant: 'warning'
                    });
                    return;
                }

                rowCount++;
                const tr = document.createElement('tr');
                tr.className = 'item-row';

                let typePrefix = '';
                if (data) {
                    if (data.type === 'product') typePrefix = 'product';
                    else if (data.type === 'material') typePrefix = 'material';
                    else if (data.type === 'texture') typePrefix = 'texture';
                }
                const selectedId = data ? `${typePrefix}_${data.id}` : '';
                const qty = data ? data.quantity : 1;
                const cost = data ? data.cost : 0;

                let optionsHtml = '<option value="" disabled selected>Select Item</option>';

                const prods = itemsForSupplier.filter(i => i.type === 'product');
                const mats = itemsForSupplier.filter(i => i.type === 'material');
                const texs = itemsForSupplier.filter(i => i.type === 'texture');

                if (prods.length > 0) {
                    optionsHtml += '<optgroup label="Products">';
                    prods.forEach(p => {
                        const val = `product_${p.id}`;
                        optionsHtml += `<option value="${val}" ${selectedId == val ? 'selected' : ''}>${p.name} (${p.sku})</option>`;
                    });
                    optionsHtml += '</optgroup>';
                }

                if (mats.length > 0) {
                    optionsHtml += '<optgroup label="Raw Materials">';
                    mats.forEach(m => {
                        const val = `material_${m.id}`;
                        optionsHtml += `<option value="${val}" ${selectedId == val ? 'selected' : ''}>${m.name} (${m.sku})</option>`;
                    });
                    optionsHtml += '</optgroup>';
                }

                if (texs.length > 0) {
                    optionsHtml += '<optgroup label="Textures">';
                    texs.forEach(t => {
                        const val = `texture_${t.id}`;
                        optionsHtml += `<option value="${val}" ${selectedId == val ? 'selected' : ''}>${t.name} (${t.sku})</option>`;
                    });
                    optionsHtml += '</optgroup>';
                }

                if (data && !itemsForSupplier.find(i => i.type === data.type && i.id == data.id)) {
                    optionsHtml += `<option value="${selectedId}" selected>${data.name} (Override)</option>`;
                }

                tr.innerHTML = `
                    <td class="ps-3">
                        <select class="form-select form-select-sm border-0 bg-light shadow-sm item-select" required>
                            ${optionsHtml}
                        </select>
                        <input type="hidden" name="items[${rowCount}][product_id]" class="product-id-input" value="${data && data.type === 'product' ? data.id : ''}">
                        <input type="hidden" name="items[${rowCount}][raw_material_id]" class="material-id-input" value="${data && data.type === 'material' ? data.id : ''}">
                        <input type="hidden" name="items[${rowCount}][texture_id]" class="texture-id-input" value="${data && data.type === 'texture' ? data.id : ''}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${rowCount}][quantity]" class="form-control form-control-sm border-0 bg-light shadow-sm quantity-input" value="${qty}" min="0.01" required>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0">₱</span>
                            <input type="number" step="0.01" name="items[${rowCount}][cost]" class="form-control border-0 bg-light shadow-sm cost-input" value="${cost}" min="0" required>
                        </div>
                    </td>
                    <td class="text-end fw-bold text-dark row-total">
                        ₱0.00
                    </td>
                    <td class="text-end pe-3">
                        <button type="button" class="btn btn-link text-danger p-0 remove-row">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </td>
                `;

                itemsBody.appendChild(tr);
                updateRowTotal(tr);
                calculateGrandTotal();

                const itemSelect = tr.querySelector('.item-select');
                const qtyInput = tr.querySelector('.quantity-input');
                const costInput = tr.querySelector('.cost-input');
                const removeBtn = tr.querySelector('.remove-row');

                itemSelect.addEventListener('change', function () {
                    const val = this.value;
                    const [type, id] = val.split('_');
                    const itemData = itemsForSupplier.find(i => i.type === type && i.id == id);
                    if (itemData) {
                        costInput.value = itemData.cost;
                    }
                    const productInput = tr.querySelector('.product-id-input');
                    const materialInput = tr.querySelector('.material-id-input');
                    const textureInput = tr.querySelector('.texture-id-input');
                    productInput.value = '';
                    materialInput.value = '';
                    textureInput.value = '';
                    if (type === 'product') productInput.value = id;
                    else if (type === 'material') materialInput.value = id;
                    else if (type === 'texture') textureInput.value = id;
                    updateRowTotal(tr);
                    calculateGrandTotal();
                });

                qtyInput.addEventListener('input', () => { updateRowTotal(tr); calculateGrandTotal(); });
                costInput.addEventListener('input', () => { updateRowTotal(tr); calculateGrandTotal(); });
                removeBtn.addEventListener('click', () => {
                    tr.remove();
                    calculateGrandTotal();
                    if (itemsBody.children.length === 0 && emptyState) {
                        emptyState.classList.remove('d-none');
                    }
                });
            }

            function updateRowTotal(row) {
                const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const total = qty * cost;
                row.querySelector('.row-total').textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function calculateGrandTotal() {
                let grandTotal = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                    const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                    grandTotal += (qty * cost);
                });
                document.getElementById('grandTotal').textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        });
    </script>
@endsection
