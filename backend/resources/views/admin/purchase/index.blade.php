@extends('layout.app')

@section('content')
    <div class="d-flex min-vh-100" style="background-color: #f8f9fa;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('admin.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block" style="width: 280px;"></div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow-x: hidden;">
            <!-- Top Navbar -->
            <header class="sticky-top bg-white shadow-sm" style="z-index: 1030;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4">
                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Purchase Orders</h4>
                            <p class="text-muted small mb-0">Manage supplier orders and restocking.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.purchase.create') }}" class="btn btn-primary rounded-pill px-3">
                                <i class="bi bi-plus-lg me-2"></i>Create New Order
                            </a>
                        </div>
                    </div>

                    <!-- PO Table -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-4 border-0 small text-uppercase text-muted">PO Number</th>
                                            <th class="border-0 small text-uppercase text-muted">Supplier</th>
                                            <th class="border-0 small text-uppercase text-muted">Date</th>
                                            <th class="border-0 small text-uppercase text-muted">Status</th>
                                            <th class="border-0 small text-uppercase text-muted text-end">Total Cost</th>
                                            <th class="border-0 small text-uppercase text-muted text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchaseOrders as $po)
                                            <tr>
                                                <td class="ps-4 py-3 font-monospace fw-bold text-dark">{{ $po->po_number }}</td>
                                                <td>
                                                    <div class="fw-bold text-dark small">{{ $po->supplier->name }}</div>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        {{ $po->items->count() }} Items
                                                    </div>
                                                </td>
                                                <td class="small text-muted">{{ $po->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = 'bg-secondary text-secondary';
                                                        if ($po->status === 'draft')
                                                            $statusClass = 'bg-secondary text-secondary';
                                                        if ($po->status === 'sent')
                                                            $statusClass = 'bg-primary text-white';
                                                        if ($po->status === 'confirmed')
                                                            $statusClass = 'bg-info text-info';
                                                        if ($po->status === 'delivered')
                                                            $statusClass = 'bg-success text-success';
                                                        if ($po->status === 'cancelled')
                                                            $statusClass = 'bg-danger text-danger';
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusClass }} bg-opacity-10 rounded-pill px-2 py-1 text-uppercase"
                                                        style="font-size: 0.7rem;">
                                                        {{ ucfirst($po->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold text-dark">₱{{ number_format($po->total_cost, 2) }}
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('admin.purchase.show', $po->id) }}"
                                                        class="btn btn-light btn-sm rounded-circle text-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox fs-1 opacity-25 d-block mb-2"></i>
                                                    No purchase orders found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="p-3 border-top">
                                {{ $purchaseOrders->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
@endsection