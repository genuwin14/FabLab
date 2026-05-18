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

                    <!-- Page Header -->
                    <div class="mb-4">
                        <!-- <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}" class="text-decoration-none">Products</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Assign Suppliers</li>
                            </ol>
                        </nav> -->
                        <h4 class="fw-bold text-primary mb-1">Assign Suppliers</h4>
                        <p class="text-muted small mb-0">Configure supplier relationships for: <strong>{{ $product->name }}</strong></p>
                    </div>

                    <!-- Product Info Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-md-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center overflow-hidden"
                                        style="width: 80px; height: 80px; background-size: cover; background-position: center; {{ $product->image ? "background-image: url('{$product->image}');" : '' }}">
                                        @if(!$product->image)
                                            <i class="bi bi-image text-muted opacity-50 fs-2"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
                                    <div class="d-flex flex-wrap gap-2 gap-md-3 text-muted small">
                                        <span><i class="bi bi-upc-scan me-1"></i> {{ $product->sku }}</span>
                                        <span><i class="bi bi-tag me-1"></i> {{ $product->category->name ?? 'N/A' }}</span>
                                        <span><i class="bi bi-currency-peso me-1"></i> ₱{{ number_format($product->price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Assignment Form -->
                    <form action="{{ route('admin.products.suppliers.store', $product->product_id) }}" method="POST">
                        @csrf

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-bottom-0 pt-3 pt-md-4 px-3 px-md-4">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-truck text-primary me-2"></i>
                                    Select Suppliers & Configure Details
                                </h6>
                                <p class="text-muted small mb-0 mt-1">Check the suppliers you want to associate with this product and fill in their details.</p>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                @if($suppliers->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No suppliers available. Please <a href="{{ route('admin.suppliers.index') }}">add suppliers</a> first.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle assign-table">
                                            <thead class="bg-light bg-opacity-50">
                                                <tr>
                                                    <th class="border-0 ps-3" style="width: 50px;">
                                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                                    </th>
                                                    <th class="border-0">Supplier</th>
                                                    <th class="border-0">Cost per Unit (₱)</th>
                                                    <th class="border-0">Min Order Qty</th>
                                                    <th class="border-0">Lead Time (days)</th>
                                                    <th class="border-0 text-center">Default</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($suppliers as $supplier)
                                                    @php
                                                        $existingSupplier = $product->suppliers->firstWhere('supplier_id', $supplier->supplier_id);
                                                        $isChecked = $existingSupplier ? true : false;
                                                        $cost = $existingSupplier ? $existingSupplier->pivot->cost : '';
                                                        $moq = $existingSupplier ? $existingSupplier->pivot->min_order_qty : '';
                                                        $leadTime = $existingSupplier ? $existingSupplier->pivot->lead_time_days : '';
                                                        $isDefault = $existingSupplier && $existingSupplier->pivot->is_default ? true : false;
                                                    @endphp
                                                    <tr class="supplier-row">
                                                        <td class="ps-3">
                                                            <input type="checkbox" 
                                                                name="suppliers[]" 
                                                                value="{{ $supplier->supplier_id }}" 
                                                                class="form-check-input supplier-checkbox"
                                                                {{ $isChecked ? 'checked' : '' }}>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong>{{ $supplier->name }}</strong>
                                                                <div class="text-muted small">{{ $supplier->email }}</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                step="0.01" 
                                                                name="cost[{{ $supplier->supplier_id }}]" 
                                                                class="form-control form-control-sm bg-light border-0" 
                                                                placeholder="0.00"
                                                                value="{{ $cost }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                name="min_order_qty[{{ $supplier->supplier_id }}]" 
                                                                class="form-control form-control-sm bg-light border-0" 
                                                                placeholder="0"
                                                                value="{{ $moq }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                name="lead_time_days[{{ $supplier->supplier_id }}]" 
                                                                class="form-control form-control-sm bg-light border-0" 
                                                                placeholder="0"
                                                                value="{{ $leadTime }}">
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="radio" 
                                                                name="is_default" 
                                                                value="{{ $supplier->supplier_id }}" 
                                                                class="form-check-input default-radio"
                                                                {{ $isDefault ? 'checked' : '' }}>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Tip:</strong> Select at least one supplier and mark one as "Default" for automatic purchase order suggestions.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-2 justify-content-between">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-light rounded-pill px-4">
                                <i class="bi bi-arrow-left me-2"></i>Back to Products
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-check-lg me-2"></i>Save Supplier Assignments
                            </button>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>

    <style>
        /* Mobile ( < lg / 992px ) — see ResponsiveMobileNote.md §5.
           Keep the supplier table from squishing its number inputs: force
           single-line cells + a comfortable min width so it scrolls instead. */
        @media (max-width: 991.98px) {
            .assign-table {
                min-width: 720px;
            }
            .assign-table th,
            .assign-table td {
                white-space: nowrap;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select All functionality
            const selectAllCheckbox = document.getElementById('selectAll');
            const supplierCheckboxes = document.querySelectorAll('.supplier-checkbox');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    supplierCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                });
            }

            // Update Select All state when individual checkboxes change
            supplierCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const allChecked = Array.from(supplierCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(supplierCheckboxes).some(cb => cb.checked);
                    
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });

            // Highlight row when checkbox is checked
            supplierCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const row = this.closest('tr');
                    if (this.checked) {
                        row.classList.add('table-active');
                    } else {
                        row.classList.remove('table-active');
                        // Uncheck default radio if unchecking supplier
                        const radio = row.querySelector('.default-radio');
                        if (radio && radio.checked) {
                            radio.checked = false;
                        }
                    }
                });

                // Trigger on page load for pre-checked items
                if (checkbox.checked) {
                    checkbox.closest('tr').classList.add('table-active');
                }
            });

            // Ensure default radio can only be selected for checked suppliers
            const defaultRadios = document.querySelectorAll('.default-radio');
            defaultRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    const row = this.closest('tr');
                    const checkbox = row.querySelector('.supplier-checkbox');
                    
                    if (!checkbox.checked) {
                        alert('Please select the supplier first before marking it as default.');
                        this.checked = false;
                    }
                });
            });
        });
    </script>
@endsection
