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
            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    @php
                        $statusMeta = [
                            'draft'     => ['label' => 'Draft',     'icon' => 'bi-pencil-square',    'bg' => 'rgba(108, 117, 125, 0.15)', 'color' => '#5a6268'],
                            'sent'      => ['label' => 'Sent',      'icon' => 'bi-send-fill',        'bg' => 'rgba(13, 110, 253, 0.12)',  'color' => '#0d6efd'],
                            'confirmed' => ['label' => 'Confirmed', 'icon' => 'bi-check2-circle',    'bg' => 'rgba(13, 202, 240, 0.15)',  'color' => '#087990'],
                            'delivered' => ['label' => 'Delivered', 'icon' => 'bi-truck',            'bg' => 'rgba(25, 135, 84, 0.12)',   'color' => '#198754'],
                            'cancelled' => ['label' => 'Cancelled', 'icon' => 'bi-x-circle-fill',    'bg' => 'rgba(220, 53, 69, 0.12)',   'color' => '#dc3545'],
                        ];
                        $currentStatus = $statusMeta[$purchaseOrder->status] ?? $statusMeta['draft'];
                    @endphp

                    <!-- Top Action Row -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <a href="{{ route('staff.purchase.index') }}"
                            class="btn btn-light rounded-2 px-3 shadow-sm border small fw-bold text-muted"
                            title="Back to Purchase Orders">
                            <i class="bi bi-arrow-left me-lg-2"></i><span class="d-none d-lg-inline">Back to Purchase Orders</span>
                        </a>

                        <div class="d-flex flex-wrap gap-2">
                            <form action="{{ route('staff.purchase.updateStatus', $purchaseOrder->purchase_order_id) }}"
                                method="POST" id="statusForm" class="m-0 d-flex flex-wrap gap-2">
                                @csrf
                                @method('PUT')

                                @if($purchaseOrder->status === 'draft')
                                    <button name="status" value="sent"
                                        class="btn btn-primary rounded-2 px-3 shadow-sm fw-bold small"
                                        title="Mark as Sent">
                                        <i class="bi bi-send me-lg-1"></i><span class="d-none d-lg-inline">Mark as Sent</span>
                                    </button>
                                @elseif($purchaseOrder->status === 'sent')
                                    <button name="status" value="confirmed"
                                        class="btn btn-info text-white rounded-2 px-3 shadow-sm fw-bold small"
                                        title="Mark as Confirmed">
                                        <i class="bi bi-check2-circle me-lg-1"></i><span class="d-none d-lg-inline">Mark as Confirmed</span>
                                    </button>
                                @elseif($purchaseOrder->status === 'confirmed')
                                    <button name="status" value="delivered"
                                        class="btn btn-success rounded-2 px-3 shadow-sm fw-bold small"
                                        title="Mark as Delivered">
                                        <i class="bi bi-box-seam me-lg-1"></i><span class="d-none d-lg-inline">Mark as Delivered</span>
                                    </button>
                                @endif
                            </form>

                            @if($purchaseOrder->status !== 'delivered' && $purchaseOrder->status !== 'cancelled')
                                <button type="button"
                                    class="btn btn-outline-danger rounded-2 px-3 shadow-sm fw-bold small"
                                    data-bs-toggle="modal" data-bs-target="#cancelPOModal" title="Cancel Order">
                                    <i class="bi bi-x-lg me-lg-1"></i><span class="d-none d-lg-inline">Cancel Order</span>
                                </button>
                            @endif

                            <button class="btn btn-light rounded-2 px-3 border shadow-sm text-muted fw-bold small"
                                onclick="window.print()" title="Print">
                                <i class="bi bi-printer me-lg-1"></i><span class="d-none d-lg-inline">Print</span>
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 g-md-4">
                        <!-- Main Info Card -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                <div class="card-header bg-white border-0 border-bottom p-3 p-md-4 pb-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <h5 class="fw-bold mb-1 text-dark font-monospace">
                                                {{ $purchaseOrder->po_number }}
                                            </h5>
                                            <div class="text-muted small">
                                                Created on {{ $purchaseOrder->created_at->format('M d, Y') }}
                                            </div>
                                        </div>
                                        <span
                                            class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill small fw-semibold text-uppercase flex-shrink-0"
                                            style="background-color: {{ $currentStatus['bg'] }}; color: {{ $currentStatus['color'] }}; font-size: 0.7rem; letter-spacing: 0.04em;">
                                            <i class="bi {{ $currentStatus['icon'] }}" style="font-size: 0.75rem;"></i>
                                            {{ $currentStatus['label'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="px-4 pt-3 pb-2">
                                        <h6 class="fw-bold text-dark text-uppercase small mb-0"
                                            style="letter-spacing: 0.04em;">
                                            <i class="bi bi-box-seam me-2 text-primary"></i>Order Items
                                        </h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 po-items-table">
                                            <thead>
                                                <tr class="bg-primary bg-opacity-10">
                                                    <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                        Product / Material</th>
                                                    <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-center">
                                                        Unit</th>
                                                    <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-center">
                                                        Qty</th>
                                                    <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">
                                                        Unit Cost</th>
                                                    <th class="pe-4 py-3 text-primary small text-uppercase fw-bold border-0 text-end">
                                                        Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                @foreach($purchaseOrder->items as $item)
                                                    <tr>
                                                        <td class="ps-4 py-3">
                                                            @if($item->product_id)
                                                                <div class="fw-bold text-dark small">
                                                                    {{ $item->product->name }}
                                                                </div>
                                                                <div class="text-muted font-monospace"
                                                                    style="font-size: 0.7rem;">
                                                                    {{ $item->product->sku }}
                                                                </div>
                                                            @elseif($item->raw_material_id)
                                                                <div class="fw-bold text-dark small">
                                                                    {{ $item->rawMaterial->name }}
                                                                </div>
                                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                                    Raw Material</div>
                                                            @elseif($item->texture_id)
                                                                <div class="fw-bold text-dark small">
                                                                    {{ $item->texture->name }}
                                                                </div>
                                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                                    Texture</div>
                                                            @else
                                                                <div class="text-danger small fst-italic">Item deleted
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center text-muted small">
                                                            @if($item->product_id)
                                                                {{ $item->product->unit }}
                                                            @elseif($item->raw_material_id)
                                                                {{ $item->rawMaterial->unit }}
                                                            @elseif($item->texture_id)
                                                                {{ $item->texture->unit }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center fw-bold text-dark">{{ $item->quantity }}
                                                        </td>
                                                        <td class="text-end text-muted small">
                                                            ₱{{ number_format($item->cost, 2) }}
                                                        </td>
                                                        <td class="pe-4 text-end fw-bold text-dark">
                                                            ₱{{ number_format($item->quantity * $item->cost, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: rgba(13, 110, 253, 0.05);">
                                                    <td colspan="4"
                                                        class="ps-4 text-end fw-bold text-uppercase small py-3"
                                                        style="letter-spacing: 0.04em;">
                                                        Grand Total
                                                    </td>
                                                    <td class="pe-4 text-end fw-bold text-primary fs-5 py-3">
                                                        ₱{{ number_format($purchaseOrder->total_cost, 2) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Side Sidebar Info -->
                        <div class="col-lg-4">
                            <!-- Supplier Details -->
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                <div class="card-body p-3 p-md-4">
                                    <h6 class="fw-bold text-dark text-uppercase small mb-3"
                                        style="letter-spacing: 0.04em;">
                                        <i class="bi bi-building me-2 text-primary"></i>Supplier Details
                                    </h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0 me-3"
                                            style="width: 44px; height: 44px; background-color: #0e2e45; color: #ffc508;">
                                            {{ strtoupper(substr($purchaseOrder->supplier->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                {{ $purchaseOrder->supplier->name ?? '—' }}
                                            </div>
                                            <div class="text-muted small">Official Supplier</div>
                                        </div>
                                    </div>
                                    @if($purchaseOrder->supplier->address ?? null)
                                        <div class="text-muted small mb-3">
                                            <i class="bi bi-geo-alt me-2"></i>{{ $purchaseOrder->supplier->address }}
                                        </div>
                                    @endif
                                    <div class="bg-light rounded-3 p-3">
                                        @if($purchaseOrder->supplier->email ?? null)
                                            <div class="text-muted small mb-1">
                                                <i class="bi bi-envelope me-2"></i>{{ $purchaseOrder->supplier->email }}
                                            </div>
                                        @endif
                                        @if($purchaseOrder->supplier->phone ?? null)
                                            <div class="text-muted small">
                                                <i class="bi bi-telephone me-2"></i>{{ $purchaseOrder->supplier->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Order Information -->
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                <div class="card-body p-3 p-md-4">
                                    <h6 class="fw-bold text-dark text-uppercase small mb-3"
                                        style="letter-spacing: 0.04em;">
                                        <i class="bi bi-info-circle me-2 text-primary"></i>Order Information
                                    </h6>

                                    <div class="mb-3">
                                        <div class="text-uppercase fw-bold text-muted mb-1"
                                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Created By</div>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2"
                                                style="width: 24px; height: 24px;">
                                                <i class="bi bi-person text-muted" style="font-size: 0.75rem;"></i>
                                            </div>
                                            <div class="fw-bold text-dark small">
                                                {{ $purchaseOrder->creator->fullname ?? 'System Admin' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <div class="text-uppercase fw-bold text-muted mb-1"
                                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Expected Delivery</div>
                                        <div class="d-flex align-items-center text-primary">
                                            <i class="bi bi-calendar-event me-2"></i>
                                            <div class="fw-bold small">
                                                {{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') : 'Not specified' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Timeline -->
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-body p-3 p-md-4">
                                    <h6 class="fw-bold text-dark text-uppercase small mb-3"
                                        style="letter-spacing: 0.04em;">
                                        <i class="bi bi-clock-history me-2 text-primary"></i>Timeline
                                    </h6>
                                    <div class="timeline-small">
                                        <div class="d-flex gap-3 mb-3">
                                            <div class="timeline-dot bg-success mt-1"></div>
                                            <div>
                                                <div class="small fw-bold">Order Created</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $purchaseOrder->created_at->format('M d, Y h:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                        @if($purchaseOrder->status !== 'draft')
                                            <div class="d-flex gap-3 mb-0">
                                                <div class="timeline-dot mt-1"
                                                    style="background-color: {{ $currentStatus['color'] }};"></div>
                                                <div>
                                                    <div class="small fw-bold">
                                                        Status Updated to {{ $currentStatus['label'] }}
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">
                                                        {{ $purchaseOrder->updated_at->format('M d, Y h:i A') }}
                                                    </div>
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

        /* ============================================
           Cancel PO Modal (mirrors product delete modal)
           ============================================ */
        .purchase-cancel-modal-dialog { max-width: 420px; }
        .purchase-cancel-modal .modal-content { border-radius: 18px; }

        .purchase-cancel-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }
        .purchase-cancel-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
        }
        .purchase-cancel-modal-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 16px;
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8088;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .purchase-cancel-modal-body { background-color: #fff; }

        .purchase-cancel-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .purchase-cancel-keep-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .purchase-cancel-keep-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .purchase-cancel-confirm-btn {
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            transition: all 0.2s ease;
        }
        .purchase-cancel-confirm-btn:hover {
            background-color: #b02a37;
            border-color: #b02a37;
            color: #fff;
        }

        /* ============================================
           Mobile responsiveness ( < lg / 992px )
           See ResponsiveMobileNote.md — detail page (no toolbar/pagination)
           ============================================ */
        @media (max-width: 991.98px) {
            /* Order Items table sits in a card with a header (not a
               table-only card) — no edge-to-edge; just keep the columns
               from squishing so it swipes. */
            .po-items-table {
                min-width: 620px;
            }
            .po-items-table th,
            .po-items-table td {
                white-space: nowrap;
            }
            .po-items-table th:first-child,
            .po-items-table td:first-child {
                min-width: 200px;
            }

            /* Cancel-PO confirm modal: smaller type + spacing on phones. */
            .modal-title { font-size: 1rem; }
            .modal-body { font-size: 0.85rem; }
            .modal .form-label,
            .modal .form-control,
            .modal .form-select,
            .modal .input-group-text,
            .modal .btn,
            .modal small,
            .modal .small { font-size: 0.8rem; }
            .purchase-cancel-modal .modal-dialog {
                margin: 0.5rem;
            }
            .purchase-cancel-modal-header {
                padding: 20px 16px 16px;
            }
            .purchase-cancel-modal-footer {
                padding: 12px 16px 16px;
            }
        }

        @media print {
            aside, header, .btn, .timeline-small {
                display: none !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
        }
    </style>

    @include('staff.purchase.components.modal-cancel-po')
@endsection
