@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('admin.partials.sidebar')
        </aside>
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">
                    <div class="mb-4">
                        <h4 class="fw-bold text-dark mb-1">Reports</h4>
                        <p class="text-muted mb-0">Generate printable inventory and equipment snapshots.</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('admin.reports.materials') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 h-100 report-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 mb-3"
                                            style="width: 56px; height: 56px; background-color: rgba(255, 197, 8, 0.15);">
                                            <i class="bi bi-clipboard-data text-warning fs-3"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">Inventory of Materials</h5>
                                        <p class="text-muted small mb-0">
                                            Snapshot of all products, raw materials, and textures with display, sponsored, damaged, consumed, and available counts.
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('admin.reports.equipment') }}" class="text-decoration-none">
                                <div class="card border-0 shadow-sm rounded-4 h-100 report-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 mb-3"
                                            style="width: 56px; height: 56px; background-color: rgba(13, 110, 253, 0.12);">
                                            <i class="bi bi-tools text-primary fs-3"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">Inventory of Machinery & Equipment</h5>
                                        <p class="text-muted small mb-0">
                                            Asset register with brand, property number, acquisition date, cost, and serviceability status.
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <style>
        .report-card { transition: transform 0.18s ease, box-shadow 0.18s ease; }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        }
    </style>
@endsection
