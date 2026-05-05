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
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Inventory Monitoring</h4>
                            <p class="text-muted small mb-0">Track stock levels and reorder suggestions.</p>
                        </div>
                        <div class="d-flex gap-2">
                             <a href="{{ route('staff.purchase.index') }}" class="btn btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-receipt me-2"></i>View Purchase Orders
                            </a>
                        </div>
                    </div>

                    @if($allLowStockItems->isEmpty())
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-5 text-center">
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
                        <div class="row g-4">
                            @foreach($groupedSuggestions as $supplierId => $items)
                                <div class="col-xl-6">
                                    <div class="card border-0 shadow-sm rounded-4 h-100">
                                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-truck text-primary fs-4"></i>
                                                </div>
                                                <div>
                                                    @if($supplierId === 'no_supplier')
                                                        <h6 class="fw-bold text-dark mb-0">No Default Supplier</h6>
                                                        <small class="text-danger">Assign suppliers to automate ordering</small>
                                                    @else
                                                        <h6 class="fw-bold text-dark mb-0">{{ $suppliers[$supplierId]->name ?? 'Unknown Supplier' }}</h6>
                                                        <small class="text-muted">{{ $items->count() }} items to reorder</small>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($supplierId !== 'no_supplier')
                                                <a href="{{ route('staff.purchase.create', ['supplier_id' => $supplierId]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                                    Create Validated PO
                                                </a>
                                            @endif
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
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
                                                                        @if($item->type === 'Product')
                                                                            <div class="bg-light rounded-2 d-flex align-items-center justify-content-center overflow-hidden" style="width: 32px; height: 32px; flex-shrink: 0; background-image: url('{{ $item->image }}'); background-size: cover;"></div>
                                                                        @else
                                                                            <div class="bg-light rounded-2 d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px; flex-shrink: 0;">
                                                                                <i class="bi bi-box"></i>
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
                                                                    <span class="badge {{ $item->type === 'Product' ? 'bg-info text-dark' : 'bg-secondary text-white' }} rounded-pill small" style="font-size: 0.7rem;">
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
                                                <small class="text-muted">Check <a href="{{ route('staff.products.index') }}">Products</a> or <a href="{{ route('staff.raw-materials.index') }}">Raw Materials</a> to fix this.</small>
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
@endsection
