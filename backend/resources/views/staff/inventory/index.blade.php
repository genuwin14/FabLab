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
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('staff.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto; overflow-x: hidden;">
                <div class="container-fluid">

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Filters, Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="inventoryFilterForm" method="GET" action="{{ route('staff.inventory.index') }}"
                                class="row g-2 align-items-center">

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
                                            <input type="text" name="search" value="{{ $search ?? '' }}"
                                                class="form-control border-start-0 rounded-end-2 ps-0"
                                                placeholder="Search by item name or SKU...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Type + Stock filters: inline on desktop; behind a filter icon on mobile. -->
                                <div class="col-auto dropdown filter-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none filter-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Filters">
                                        <i class="bi bi-funnel text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu filter-menu border-0 shadow p-2 p-lg-0">
                                        <div class="d-flex flex-column flex-lg-row gap-2">
                                            <select name="type" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('inventoryFilterForm').submit()">
                                                <option value="">All Types</option>
                                                <option value="Product" {{ ($type ?? '') === 'Product' ? 'selected' : '' }}>Products</option>
                                                <option value="Raw Material" {{ ($type ?? '') === 'Raw Material' ? 'selected' : '' }}>Raw Materials</option>
                                                <option value="Texture" {{ ($type ?? '') === 'Texture' ? 'selected' : '' }}>Textures</option>
                                            </select>
                                            <select name="stock_status" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('inventoryFilterForm').submit()">
                                                <option value="">Stock Status</option>
                                                <option value="low_stock" {{ ($stockStatus ?? '') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                                <option value="out_of_stock" {{ ($stockStatus ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <a href="{{ route('staff.inventory.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <a href="{{ route('staff.purchase.index') }}"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        title="View Purchase Orders">
                                        <i class="bi bi-receipt small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">View Purchase Orders</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($allLowStockItems->isEmpty())
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 p-md-5 text-center">
                                <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                                <h5 class="fw-bold text-dark">All Stock Levels Optimal</h5>
                                <p class="text-muted">There are no products or raw materials below their low stock threshold at this time.</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Attention Needed</h6>
                                <p class="mb-0 small">{{ $allLowStockItems->count() }} items are running low on stock. Review suggestions below.</p>
                            </div>
                        </div>

                        <!-- Suggestions grouped by Supplier -->
                        <div class="row g-3 g-md-4">
                            @foreach($groupedSuggestions as $supplierId => $items)
                                <div class="col-xl-6">
                                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                        <div class="card-header bg-white border-bottom-0 pt-3 pt-md-4 px-3 px-md-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-truck text-primary fs-4"></i>
                                                </div>
                                                <div>
                                                    @if($supplierId === 'no_supplier')
                                                        <h6 class="fw-bold text-dark mb-0">No Default Supplier</h6>
                                                        <small class="text-danger">Ask admin to assign suppliers</small>
                                                    @else
                                                        <h6 class="fw-bold text-dark mb-0">{{ $suppliers[$supplierId]->name ?? 'Unknown Supplier' }}</h6>
                                                        <small class="text-muted">{{ $items->count() }} items to reorder</small>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($supplierId !== 'no_supplier')
                                                <a href="{{ route('staff.purchase.create', ['supplier_id' => $supplierId]) }}" class="btn btn-primary btn-sm rounded-pill px-3 flex-shrink-0">
                                                    Create Validated PO
                                                </a>
                                            @endif
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0 inv-table">
                                                    <thead class="bg-light bg-opacity-50">
                                                        <tr>
                                                            <th class="ps-4 border-0 small text-uppercase text-muted">Item Name</th>
                                                            <th class="border-0 small text-uppercase text-muted">Type</th>
                                                            <th class="border-0 small text-uppercase text-muted text-center">Current</th>
                                                            <th class="border-0 small text-uppercase text-muted text-center">Threshold</th>
                                                            <th class="border-0 small text-uppercase text-muted text-end pe-4">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($items as $item)
                                                            <tr>
                                                                <td class="ps-4 py-3">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        @php
                                                                            if ($item->type === 'Product') {
                                                                                $thumb = $item->image ?? null;
                                                                                $fallbackIcon = 'bi-image';
                                                                            } elseif ($item->type === 'Texture') {
                                                                                $thumb = $item->image_path ?? null;
                                                                                $fallbackIcon = 'bi-layers';
                                                                            } else {
                                                                                $thumb = $item->image_path ?? null;
                                                                                $fallbackIcon = 'bi-box';
                                                                            }
                                                                        @endphp
                                                                        @if($thumb)
                                                                            <img src="{{ $thumb }}" alt="{{ $item->name }}"
                                                                                class="rounded-2 object-fit-cover border"
                                                                                style="width: 36px; height: 36px; flex-shrink: 0;">
                                                                        @else
                                                                            <div class="bg-light rounded-2 d-flex align-items-center justify-content-center text-primary border"
                                                                                style="width: 36px; height: 36px; flex-shrink: 0;">
                                                                                <i class="bi {{ $fallbackIcon }}"></i>
                                                                            </div>
                                                                        @endif
                                                                        <div>
                                                                            <div class="fw-bold text-dark small">{{ $item->name }}</div>
                                                                            @if($item->type === 'Product')
                                                                                <div class="text-muted small font-monospace" style="font-size: 0.75rem;">{{ $item->sku }}</div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @php
                                                                        $typeBadgeClass = 'bg-secondary text-white';
                                                                        if ($item->type === 'Product') $typeBadgeClass = 'bg-info text-dark';
                                                                        if ($item->type === 'Texture') $typeBadgeClass = 'bg-warning text-dark';
                                                                    @endphp
                                                                    <span class="badge {{ $typeBadgeClass }} rounded-pill small" style="font-size: 0.7rem;">
                                                                        {{ $item->type }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-center fw-bold text-danger">{{ $item->display_stock }} {{ $item->unit }}</td>
                                                                <td class="text-center text-muted">{{ $item->display_threshold }}</td>
                                                                <td class="text-end pe-4">
                                                                    @if($item->display_stock == 0)
                                                                         <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Out of Stock</span>
                                                                    @else
                                                                         <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Low Stock</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @if($supplierId === 'no_supplier')
                                            <div class="card-footer bg-white border-top-0 p-3 text-center">
                                                <small class="text-muted">Ask the admin to assign default suppliers in <a href="{{ route('staff.products.index') }}">Products</a> or <a href="{{ route('staff.raw-materials.index') }}">Raw Materials</a> to enable automated reordering.</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <style>
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

            /* Suggestion tables sit inside grid cards (header + footer), so
               no edge-to-edge — just keep columns from squishing: swipe. */
            .inv-table {
                min-width: 560px;
            }
            .inv-table th,
            .inv-table td {
                white-space: nowrap;
            }
            .inv-table th:first-child,
            .inv-table td:first-child {
                min-width: 200px;
            }
        }
    </style>
@endsection
