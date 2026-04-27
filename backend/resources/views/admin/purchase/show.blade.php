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
                <div class="container-fluid" style="max-width: 1100px;">

                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="{{ route('admin.purchase.index') }}" class="btn btn-light rounded-pill px-3 shadow-sm border small fw-bold text-muted hover-accent">
                            <i class="bi bi-arrow-left me-2"></i>Back to Purchase Orders
                        </a>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Purchase Order Details</h4>
                            <p class="text-muted small mb-0">View and manage purchase order lifecycle.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Status Actions -->
                            <form action="{{ route('admin.purchase.updateStatus', $purchaseOrder->purchase_order_id) }}" method="POST" id="statusForm">
                                @csrf
                                @method('PUT')
                                
                                @if($purchaseOrder->status === 'draft')
                                    <button name="status" value="sent" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-send me-2"></i>Mark as Sent
                                    </button>
                                @elseif($purchaseOrder->status === 'sent')
                                    <button name="status" value="confirmed" class="btn btn-info text-white rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-check2-circle me-2"></i>Mark as Confirmed
                                    </button>
                                @elseif($purchaseOrder->status === 'confirmed')
                                    <button name="status" value="delivered" class="btn btn-success rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-box-seam me-2"></i>Mark as Delivered
                                    </button>
                                @endif
                                
                                @if($purchaseOrder->status !== 'delivered' && $purchaseOrder->status !== 'cancelled')
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-4 ms-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#cancelPOModal">
                                        Cancel Order
                                    </button>
                                @endif
                            </form>
                             
                             <button class="btn btn-white rounded-pill px-3 ms-2 text-muted border shadow-sm hover-accent" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Print
                            </button>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Main Info Card -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                <div class="card-header bg-white border-0 p-4 pb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="fw-bold mb-1 text-dark">{{ $purchaseOrder->po_number }}</h5>
                                            <div class="text-muted small">Created on {{ $purchaseOrder->created_at->format('M d, Y') }}</div>
                                        </div>
                                        @php
                                            $statusClass = 'bg-secondary text-secondary';
                                            if($purchaseOrder->status === 'draft') $statusClass = 'bg-secondary text-secondary';
                                            if($purchaseOrder->status === 'sent') $statusClass = 'bg-primary text-white';
                                            if($purchaseOrder->status === 'confirmed') $statusClass = 'bg-info text-info';
                                            if($purchaseOrder->status === 'delivered') $statusClass = 'bg-success text-success';
                                            if($purchaseOrder->status === 'cancelled') $statusClass = 'bg-danger text-danger';
                                        @endphp
                                        <span class="badge {{ $statusClass }} bg-opacity-10 px-3 py-2 rounded-pill text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                            {{ ucfirst($purchaseOrder->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-dark mb-3">Order Items</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light bg-opacity-50">
                                                <tr>
                                                    <th class="border-0 small text-uppercase text-muted py-3">Product / Material</th>
                                                    <th class="border-0 small text-uppercase text-muted text-center py-3">Unit</th>
                                                    <th class="border-0 small text-uppercase text-muted text-center py-3">Qty</th>
                                                    <th class="border-0 small text-uppercase text-muted text-end py-3">Unit Cost</th>
                                                    <th class="border-0 small text-uppercase text-muted text-end py-3">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchaseOrder->items as $item)
                                                    <tr>
                                                        <td class="py-3">
                                                            @if($item->product_id)
                                                                <div class="fw-bold text-dark small">{{ $item->product->name }}</div>
                                                                <div class="text-muted small font-monospace" style="font-size: 0.7rem;">{{ $item->product->sku }}</div>
                                                            @elseif($item->raw_material_id)
                                                                <div class="fw-bold text-dark small">{{ $item->rawMaterial->name }}</div>
                                                                <div class="text-muted small" style="font-size: 0.7rem;">Raw Material</div>
                                                            @else
                                                                <div class="text-danger small italic">Item deleted</div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center text-muted small">
                                                            @if($item->product_id)
                                                                {{ $item->product->unit }}
                                                            @elseif($item->raw_material_id)
                                                                {{ $item->rawMaterial->unit }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center fw-bold text-dark">{{ $item->quantity }}</td>
                                                        <td class="text-end text-muted small">₱{{ number_format($item->cost, 2) }}</td>
                                                        <td class="text-end fw-bold text-dark">₱{{ number_format($item->quantity * $item->cost, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-light bg-opacity-25">
                                                    <td colspan="4" class="text-end fw-bold text-uppercase small py-4">Grand Total</td>
                                                    <td class="text-end fw-bold text-primary fs-5 py-4">₱{{ number_format($purchaseOrder->total_cost, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Side Sidebar Info -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Supplier Details</h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 me-3 text-primary">
                                            <i class="bi bi-building fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $purchaseOrder->supplier->name }}</div>
                                            <div class="text-muted small">Official Supplier</div>
                                        </div>
                                    </div>
                                    <div class="text-muted small mb-3">
                                        <i class="bi bi-geo-alt me-2"></i> {{ $purchaseOrder->supplier->address }}
                                    </div>
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="text-muted small mb-1">
                                            <i class="bi bi-envelope me-2"></i> {{ $purchaseOrder->supplier->email }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-telephone me-2"></i> {{ $purchaseOrder->supplier->phone }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Order Information</h6>
                                    
                                    <div class="mb-3">
                                        <div class="small text-muted text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Created By</div>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                                                <i class="bi bi-person text-muted" style="font-size: 0.75rem;"></i>
                                            </div>
                                            <div class="fw-bold text-dark small">{{ $purchaseOrder->creator->fullname ?? 'System Admin' }}</div>
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <div class="small text-muted text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Expected Delivery</div>
                                        <div class="d-flex align-items-center text-primary">
                                            <i class="bi bi-calendar-event me-2"></i>
                                            <div class="fw-bold small">{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') : 'Not specified' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Timeline (Conceptual) -->
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Timeline</h6>
                                    <div class="timeline-small">
                                        <div class="d-flex gap-3 mb-3">
                                            <div class="timeline-dot bg-success mt-1"></div>
                                            <div>
                                                <div class="small fw-bold">Order Created</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">{{ $purchaseOrder->created_at->format('M d, Y h:i A') }}</div>
                                            </div>
                                        </div>
                                        @if($purchaseOrder->status !== 'draft')
                                            <div class="d-flex gap-3 mb-3">
                                                <div class="timeline-dot bg-primary mt-1"></div>
                                                <div>
                                                    <div class="small fw-bold">Status Updated to {{ ucfirst($purchaseOrder->status) }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $purchaseOrder->updated_at->format('M d, Y h:i A') }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <style>
        .hover-accent:hover {
            background-color: #f8f9fa !important;
            border-color: #dee2e6 !important;
        }
        .btn-white {
            background-color: white;
            color: #6c757d;
        }
        .timeline-small {
            position: relative;
        }
        .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .timeline-small::before {
            content: '';
            position: absolute;
            left: 4.5px;
            top: 5px;
            bottom: 5px;
            width: 1px;
            background-color: #dee2e6;
        }
        @media print {
            aside, header, .btn, .mb-4:first-child {
                display: none !important;
            }
            .main-content, main {
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
        }
    </style>

    @include('admin.purchase.components.modal-cancel-po')
@endsection
