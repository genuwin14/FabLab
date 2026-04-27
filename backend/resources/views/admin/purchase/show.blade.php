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
                <div class="container-fluid" style="max-width: 1000px;">

                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="{{ route('admin.purchase.index') }}" class="btn btn-light rounded-pill px-3 shadow-sm border small fw-bold text-muted hover-accent">
                            <i class="bi bi-arrow-left me-2"></i>Back to Purchase Orders
                        </a>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-dark mb-0">Purchase Order Details</h4>
                        <div class="d-flex gap-2">
                            <!-- Status Actions -->
                            <form action="{{ route('admin.purchase.updateStatus', $purchaseOrder->purchase_order_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                @if($purchaseOrder->status === 'draft')
                                    <button name="status" value="sent" class="btn btn-primary rounded-pill px-4">
                                        Mark as Sent
                                    </button>
                                @elseif($purchaseOrder->status === 'sent')
                                    <button name="status" value="confirmed" class="btn btn-info text-white rounded-pill px-4">
                                        Mark as Confirmed
                                    </button>
                                @elseif($purchaseOrder->status === 'confirmed')
                                    <button name="status" value="delivered" class="btn btn-success rounded-pill px-4">
                                        Mark as Delivered
                                    </button>
                                @endif
                                
                                @if($purchaseOrder->status !== 'delivered' && $purchaseOrder->status !== 'cancelled')
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 ms-2" data-bs-toggle="modal" data-bs-target="#cancelPOModal">
                                        Cancel Order
                                    </button>
                                @endif
                            </form>
                             
                             <a href="#" class="btn btn-light rounded-pill px-3 ms-2 text-muted border" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Print
                            </a>
                        </div>
                    </div>

                    <!-- Order Card -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <div class="row">
                                <div class="col-md-6">
                                     <div class="small text-uppercase text-muted fw-bold mb-1">Status</div>
                                     @php
                                        $statusClass = 'bg-secondary text-secondary';
                                        if($purchaseOrder->status === 'draft') $statusClass = 'bg-secondary text-dark bg-opacity-10';
                                        if($purchaseOrder->status === 'sent') $statusClass = 'bg-primary text-white bg-opacity-10';
                                        if($purchaseOrder->status === 'confirmed') $statusClass = 'bg-info text-info bg-opacity-10';
                                        if($purchaseOrder->status === 'delivered') $statusClass = 'bg-success text-success bg-opacity-10';
                                        if($purchaseOrder->status === 'cancelled') $statusClass = 'bg-danger text-danger bg-opacity-10';
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill text-uppercase">
                                        {{ $purchaseOrder->status }}
                                    </span>
                                </div>
                                <div class="col-md-6 text-md-end">
                                     <h5 class="fw-bold mb-0 text-dark">{{ $purchaseOrder->po_number }}</h5>
                                     <div class="text-muted small">Created on {{ $purchaseOrder->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Supplier Details</h6>
                                    <div class="fw-bold">{{ $purchaseOrder->supplier->name }}</div>
                                    <div class="text-muted small">{{ $purchaseOrder->supplier->address }}</div>
                                    <div class="text-muted small mt-2">
                                        <i class="bi bi-envelope me-1"></i> {{ $purchaseOrder->supplier->email }}<br>
                                        <i class="bi bi-telephone me-1"></i> {{ $purchaseOrder->supplier->phone }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Order Info</h6>
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <div class="small text-muted text-uppercase">Created By</div>
                                            <div class="fw-bold small">{{ $purchaseOrder->creator->fullname ?? 'Unknown Admin' }}</div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="small text-muted text-uppercase">Expected Delivery</div>
                                            <div class="fw-bold small">{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') : 'Not specified' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Order Items</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="small text-uppercase text-muted">Product</th>
                                            <th class="small text-uppercase text-muted text-center">Unit</th>
                                            <th class="small text-uppercase text-muted text-center" style="width: 100px;">Qty</th>
                                            <th class="small text-uppercase text-muted text-end" style="width: 150px;">Unit Cost</th>
                                            <th class="small text-uppercase text-muted text-end" style="width: 150px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrder->items as $item)
                                            <tr>
                                                <td>
                                                    @if($item->product_id)
                                                        <div class="fw-bold small">{{ $item->product->name }}</div>
                                                        <div class="text-muted small font-monospace">{{ $item->product->sku }}</div>
                                                    @elseif($item->raw_material_id)
                                                        <div class="fw-bold small">{{ $item->rawMaterial->name }}</div>
                                                        <div class="text-muted small">Raw Material</div>
                                                    @else
                                                        <div class="text-danger small italic">Item deleted</div>
                                                    @endif
                                                </td>
                                                <td class="text-center small">
                                                    @if($item->product_id)
                                                        {{ $item->product->unit }}
                                                    @elseif($item->raw_material_id)
                                                        {{ $item->rawMaterial->unit }}
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                                <td class="text-end text-muted">₱{{ number_format($item->cost, 2) }}</td>
                                                <td class="text-end fw-bold">₱{{ number_format($item->quantity * $item->cost, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold text-uppercase small py-3">Grand Total</td>
                                            <td class="text-end fw-bold fs-5 text-dark py-3">₱{{ number_format($purchaseOrder->total_cost, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    @include('admin.purchase.components.modal-cancel-po')
@endsection
