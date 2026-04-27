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
                <div class="container-fluid" style="max-width: 1200px;">

                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="{{ route('admin.purchase.index') }}" class="btn btn-light rounded-pill px-3 shadow-sm border small fw-bold text-muted hover-accent">
                            <i class="bi bi-arrow-left me-2"></i>Back to Purchase Orders
                        </a>
                    </div>

                    <form action="{{ route('admin.purchase.store') }}" method="POST" id="poForm">
                        @csrf

                        <div class="row g-4">
                            <!-- PO Settings -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                        <h6 class="fw-bold text-dark mb-0">Order Details</h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Supplier <span
                                                    class="text-danger">*</span></label>
                                            <select name="supplier_id" id="supplierSelect"
                                                class="form-select rounded-3 bg-light border-0 px-3" required {{ $selectedSupplierId ? 'readonly style="pointer-events: none;"' : '' }}>
                                                <option value="" disabled {{ !$selectedSupplierId ? 'selected' : '' }}>
                                                    Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->supplier_id }}" {{ $selectedSupplierId == $supplier->supplier_id ? 'selected' : '' }}>
                                                        {{ $supplier->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($selectedSupplierId)
                                                <!-- Hidden input to ensure value is sent if select is disabled/readonly pointer-events -->
                                                <!-- Actually select readonly doesn't prevent change by script, but visually pointer-events none does.
                                                             However, best practice is to have a hidden input if disabled. But here we just use pointer-events.
                                                             If I use strict disabled, form won't submit it.
                                                         -->
                                                <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}">
                                                <div class="form-text text-primary"><i class="bi bi-lock-fill"></i> Supplier
                                                    locked from Inventory Suggestions</div>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Expected
                                                Delivery</label>
                                            <input type="date" name="expected_delivery_date"
                                                class="form-control rounded-3 bg-light border-0 px-3">
                                        </div>

                                        <div class="alert alert-info border-0 d-flex align-items- gap-2 p-3 mt-4">
                                            <i class="bi bi-info-circle-fill fs-5"></i>
                                            <div class="small">
                                                This PO will be saved as <strong>Draft</strong>. You can review and send it
                                                later.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                    <div
                                        class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0">Order Items</h6>
                                        <button type="button" class="btn btn-light btn-sm rounded-pill border"
                                            id="addItemBtn">
                                            <i class="bi bi-plus-lg me-1"></i> Add Item
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0" id="itemsTable">
                                                <thead class="bg-light bg-opacity-50">
                                                    <tr>
                                                        <th class="ps-4 border-0 small text-uppercase text-muted"
                                                            style="width: 40%">Product</th>
                                                        <th class="border-0 small text-uppercase text-muted"
                                                            style="width: 15%">Qty</th>
                                                        <th class="border-0 small text-uppercase text-muted"
                                                            style="width: 20%">Cost (₱)</th>
                                                        <th class="border-0 small text-uppercase text-muted text-end"
                                                            style="width: 20%">Total</th>
                                                        <th class="border-0" style="width: 5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="itemsBody">
                                                    <!-- Items will be injected here -->
                                                </tbody>
                                                <tfoot class="border-top-0">
                                                    <tr>
                                                        <td colspan="3"
                                                            class="text-end pe-4 py-3 fw-bold text-muted text-uppercase small">
                                                            Grand Total</td>
                                                        <td class="text-end py-3 fw-bold text-primary fs-5">₱<span
                                                                id="grandTotal">0.00</span></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        @if(empty($prefillItems) && empty($selectedSupplierId))
                                            <div id="emptyState" class="text-center p-5 text-muted">
                                                <i class="bi bi-basket3 fs-1 opacity-25"></i>
                                                <p class="mt-2 text-small">No items added yet. Select a supplier and add
                                                    products.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-2">
                            <a href="{{ route('admin.purchase.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveBtn">
                                <i class="bi bi-check-lg me-2"></i> Create Purchase Order
                            </button>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>

    <!-- Template for JS -->
    @php
        $jsProducts = $products->map(function ($p) {
            return [
                'id' => $p->product_id,
                'name' => $p->name,
                'sku' => $p->sku,
                'type' => 'product'
            ];
        });

        $jsMaterials = $rawMaterials->map(function ($m) {
            return [
                'id' => $m->raw_material_id,
                'name' => $m->name,
                'sku' => 'MAT-' . $m->raw_material_id,
                'type' => 'material'
            ];
        });
    @endphp

    <script>
        const supplierItems = @json($supplierItems);
        const prefillData = @json($prefillItems);
        let rowCount = 0;

        document.addEventListener('DOMContentLoaded', function () {
            const supplierSelect = document.getElementById('supplierSelect');
            const itemsBody = document.getElementById('itemsBody');
            const addItemBtn = document.getElementById('addItemBtn');
            const emptyState = document.getElementById('emptyState');

            // Handle Supplier Change
            supplierSelect.addEventListener('change', function() {
                if (itemsBody.children.length > 0) {
                    if (confirm('Changing the supplier will clear all current items. Proceed?')) {
                        itemsBody.innerHTML = '';
                        calculateGrandTotal();
                        if (emptyState) emptyState.style.display = 'block';
                    } else {
                        // Revert to previous value (simulated)
                        // This is tricky without storing old value. Let's just clear for now as it's safer.
                    }
                }
            });

            if (prefillData && prefillData.length > 0) {
                if (emptyState) emptyState.style.display = 'none';
                prefillData.forEach(item => {
                    addRow(item);
                });
            }

            addItemBtn.addEventListener('click', function () {
                if (!supplierSelect.value) {
                    alert('Please select a supplier first.');
                    return;
                }
                if (emptyState) emptyState.style.display = 'none';
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
                
                // Group items by type
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

                // If pre-filled data is NOT in the supplier list (safety), still show it
                if (data && !itemsForSupplier.find(i => i.type === data.type && i.id == data.id)) {
                    optionsHtml += `<option value="${selectedId}" selected>${data.name} (Override)</option>`;
                }

                tr.innerHTML = `
                        <td class="ps-4">
                            <select class="form-select form-select-sm bg-light border-0 item-select" required>
                                ${optionsHtml}
                            </select>
                            <input type="hidden" name="items[${rowCount}][product_id]" class="product-id-input" value="${data && data.type === 'product' ? data.id : ''}">
                            <input type="hidden" name="items[${rowCount}][raw_material_id]" class="material-id-input" value="${data && data.type === 'material' ? data.id : ''}">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="items[${rowCount}][quantity]" class="form-control form-control-sm bg-light border-0 quantity-input" value="${qty}" min="0.01" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" name="items[${rowCount}][cost]" class="form-control form-control-sm bg-light border-0 cost-input" value="${cost}" min="0" required>
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
                    
                    // Find cost in mapping
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
                        emptyState.style.display = 'block';
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