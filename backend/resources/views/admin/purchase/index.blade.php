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
                            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createPOModal">
                                <i class="bi bi-plus-lg me-2"></i>Create New Order
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3 text-primary">
                                        <i class="bi bi-cart-check fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small text-uppercase mb-1">Total POs</h6>
                                        <h4 class="fw-bold mb-0">{{ $purchaseOrders->total() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3 text-warning">
                                        <i class="bi bi-clock-history fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted small text-uppercase mb-1">Pending</h6>
                                        <h4 class="fw-bold mb-0">{{ $purchaseOrders->where('status', 'draft')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PO Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
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
                                                <td class="ps-4 py-3">
                                                    <span class="font-monospace fw-bold text-primary">{{ $po->po_number }}</span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $po->supplier->name }}</div>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        {{ $po->items->count() }} Items
                                                    </div>
                                                </td>
                                                <td class="small text-muted">{{ $po->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = 'bg-secondary text-secondary';
                                                        if ($po->status === 'draft') $statusClass = 'bg-secondary text-secondary';
                                                        if ($po->status === 'sent') $statusClass = 'bg-primary text-white';
                                                        if ($po->status === 'confirmed') $statusClass = 'bg-info text-info';
                                                        if ($po->status === 'delivered') $statusClass = 'bg-success text-success';
                                                        if ($po->status === 'cancelled') $statusClass = 'bg-danger text-danger';
                                                    @endphp
                                                    <span class="badge {{ $statusClass }} bg-opacity-10 rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                                        {{ ucfirst($po->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold text-dark">₱{{ number_format($po->total_cost, 2) }}</td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('admin.purchase.show', $po->purchase_order_id) }}"
                                                        class="btn btn-light btn-sm rounded-pill px-3 border shadow-sm text-primary hover-accent">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <div class="py-4">
                                                        <i class="bi bi-inbox fs-1 opacity-25 d-block mb-3"></i>
                                                        <h5 class="fw-light">No purchase orders found</h5>
                                                        <p class="small">Create a new order to get started.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center p-4 border-top bg-light bg-opacity-25">
                                <span class="text-muted small">
                                    Showing <span class="fw-bold text-dark">{{ $purchaseOrders->firstItem() }}</span> to <span class="fw-bold text-dark">{{ $purchaseOrders->lastItem() }}</span> of <span class="fw-bold text-dark">{{ $purchaseOrders->total() }}</span> entries
                                </span>
                                <nav>
                                    {{ $purchaseOrders->links() }}
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Purchase Order Modal -->
    <div class="modal fade" id="createPOModal" tabindex="-1" aria-labelledby="createPOModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="createPOModalLabel">Create Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('admin.purchase.store') }}" method="POST" id="poForm">
                        @csrf
                        <div class="row g-4">
                            <!-- PO Settings -->
                            <div class="col-lg-4">
                                <div class="bg-light rounded-4 p-4 h-100">
                                    <h6 class="fw-bold text-dark mb-4">Order Details</h6>
                                    
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Supplier <span class="text-danger">*</span></label>
                                        <select name="supplier_id" id="supplierSelect" class="form-select rounded-3 border-0 shadow-sm px-3 py-2" required {{ $selectedSupplierId ? 'readonly style="pointer-events: none;"' : '' }}>
                                            <option value="" disabled {{ !$selectedSupplierId ? 'selected' : '' }}>Select Supplier</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->supplier_id }}" {{ $selectedSupplierId == $supplier->supplier_id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($selectedSupplierId)
                                            <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">
                                            <div class="form-text text-primary mt-2 small"><i class="bi bi-lock-fill me-1"></i> Supplier locked from suggestions</div>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Expected Delivery</label>
                                        <input type="date" name="expected_delivery_date" class="form-control rounded-3 border-0 shadow-sm px-3 py-2">
                                    </div>

                                    <div class="alert alert-primary border-0 bg-primary bg-opacity-10 d-flex align-items-start gap-3 p-3 rounded-4">
                                        <i class="bi bi-info-circle-fill text-primary fs-5 mt-1"></i>
                                        <div class="small text-primary">
                                            This PO will be saved as <strong>Draft</strong>. You can review and send it later.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0">Order Items</h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="addItemBtn">
                                            <i class="bi bi-plus-lg me-1"></i> Add Item
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-hover align-middle mb-0" id="itemsTable">
                                                <thead class="bg-light sticky-top" style="z-index: 1;">
                                                    <tr>
                                                        <th class="ps-4 border-0 small text-uppercase text-muted py-3" style="width: 40%">Product</th>
                                                        <th class="border-0 small text-uppercase text-muted py-3" style="width: 15%">Qty</th>
                                                        <th class="border-0 small text-uppercase text-muted py-3" style="width: 20%">Cost (₱)</th>
                                                        <th class="border-0 small text-uppercase text-muted text-end py-3" style="width: 20%">Total</th>
                                                        <th class="border-0" style="width: 5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="itemsBody">
                                                    <!-- Items will be injected here -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <div id="emptyState" class="text-center p-5 text-muted {{ !empty($prefillItems) ? 'd-none' : '' }}">
                                            <div class="mb-3">
                                                <i class="bi bi-basket3 fs-1 opacity-25"></i>
                                            </div>
                                            <p class="mb-0 small">No items added yet. Select a supplier and add products.</p>
                                        </div>

                                        <div class="p-4 border-top bg-light bg-opacity-50">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-muted text-uppercase small">Grand Total</span>
                                                <h4 class="fw-bold text-primary mb-0">₱<span id="grandTotal">0.00</span></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="saveBtn">
                                <i class="bi bi-check-lg me-2"></i> Create Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-accent:hover {
            background-color: #e9ecef !important;
            border-color: #dee2e6 !important;
        }
        .form-select, .form-control {
            transition: all 0.2s ease-in-out;
        }
        .form-select:focus, .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1) !important;
            border-color: rgba(13, 110, 253, 0.2) !important;
        }
        .modal-xl {
            max-width: 1140px;
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
            supplierSelect.addEventListener('change', function() {
                if (itemsBody.children.length > 0) {
                    if (confirm('Changing the supplier will clear all current items. Proceed?')) {
                        itemsBody.innerHTML = '';
                        calculateGrandTotal();
                        if (emptyState) emptyState.classList.remove('d-none');
                    } else {
                        // This is tricky without storing old value.
                    }
                }
            });

            addItemBtn.addEventListener('click', function () {
                if (!supplierSelect.value) {
                    alert('Please select a supplier first.');
                    return;
                }
                if (emptyState) emptyState.classList.add('d-none');
                addRow();
            });

            function addRow(data = null) {
                const supplierId = supplierSelect.value;
                const itemsForSupplier = supplierItems[supplierId] || [];

                if (itemsForSupplier.length === 0 && !data) {
                    alert('This supplier has no assigned products or raw materials.');
                    return;
                }

                rowCount++;
                const tr = document.createElement('tr');
                tr.className = 'item-row';

                const selectedId = data ? (data.type === 'product' ? `product_${data.id}` : `material_${data.id}`) : '';
                const qty = data ? data.quantity : 1;
                const cost = data ? data.cost : 0;

                let optionsHtml = '<option value="" disabled selected>Select Item</option>';
                
                const prods = itemsForSupplier.filter(i => i.type === 'product');
                const mats = itemsForSupplier.filter(i => i.type === 'material');

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

                if (data && !itemsForSupplier.find(i => i.type === data.type && i.id == data.id)) {
                    optionsHtml += `<option value="${selectedId}" selected>${data.name} (Override)</option>`;
                }

                tr.innerHTML = `
                    <td class="ps-4">
                        <select class="form-select form-select-sm border-0 bg-light shadow-sm item-select" required>
                            ${optionsHtml}
                        </select>
                        <input type="hidden" name="items[${rowCount}][product_id]" class="product-id-input" value="${data && data.type === 'product' ? data.id : ''}">
                        <input type="hidden" name="items[${rowCount}][raw_material_id]" class="material-id-input" value="${data && data.type === 'material' ? data.id : ''}">
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

                itemSelect.addEventListener('change', function() {
                    const val = this.value;
                    const [type, id] = val.split('_');
                    const itemData = itemsForSupplier.find(i => i.type === type && i.id == id);
                    if (itemData) {
                        costInput.value = itemData.cost;
                    }
                    if (type === 'product') {
                        tr.querySelector('.product-id-input').value = id;
                        tr.querySelector('.material-id-input').value = '';
                    } else {
                        tr.querySelector('.product-id-input').value = '';
                        tr.querySelector('.material-id-input').value = id;
                    }
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