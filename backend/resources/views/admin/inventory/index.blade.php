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

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
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
                            <form id="inventoryFilterForm" method="GET" action="{{ route('admin.inventory.index') }}"
                                class="d-flex flex-nowrap align-items-center gap-2">
                                <div class="input-group flex-grow-1" style="min-width: 0;">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search ?? '' }}"
                                        class="form-control border-start-0 rounded-end-2 ps-0"
                                        placeholder="Search by item name or SKU...">
                                </div>
                                <select name="type" class="form-select rounded-2 flex-shrink-0 w-auto"
                                    onchange="document.getElementById('inventoryFilterForm').submit()">
                                    <option value="">All Types</option>
                                    <option value="Product" {{ ($type ?? '') === 'Product' ? 'selected' : '' }}>Products</option>
                                    <option value="Raw Material" {{ ($type ?? '') === 'Raw Material' ? 'selected' : '' }}>Raw Materials</option>
                                    <option value="Texture" {{ ($type ?? '') === 'Texture' ? 'selected' : '' }}>Textures</option>
                                </select>
                                <select name="stock_status" class="form-select rounded-2 flex-shrink-0 w-auto"
                                    onchange="document.getElementById('inventoryFilterForm').submit()">
                                    <option value="">Stock Status</option>
                                    <option value="low_stock" {{ ($stockStatus ?? '') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                    <option value="out_of_stock" {{ ($stockStatus ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                                <a href="{{ route('admin.inventory.index') }}"
                                    class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>
                                <a href="{{ route('admin.purchase.index') }}"
                                    class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0">
                                    <i class="bi bi-receipt small"></i>
                                    <span class="small fw-bold">View Purchase Orders</span>
                                </a>
                            </form>
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
                                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 48px; height: 48px;">
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
                                                <a href="{{ route('admin.purchase.create', ['supplier_id' => $supplierId]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
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
                                                            <th class="border-0 small text-uppercase text-muted {{ $supplierId === 'no_supplier' ? 'text-center' : 'text-end pe-4' }}">Status</th>
                                                            @if($supplierId === 'no_supplier')
                                                                <th class="border-0 small text-uppercase text-muted text-end pe-4">Action</th>
                                                            @endif
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
                                                                <td class="{{ $supplierId === 'no_supplier' ? 'text-center' : 'text-end pe-4' }}">
                                                                    @if($item->display_stock == 0)
                                                                         <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Out of Stock</span>
                                                                    @else
                                                                         <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Low Stock</span>
                                                                    @endif
                                                                </td>
                                                                @if($supplierId === 'no_supplier')
                                                                    <td class="text-end pe-4">
                                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 set-supplier-btn"
                                                                            data-bs-toggle="modal" data-bs-target="#setSupplierModal"
                                                                            data-item-id="{{ $item->type === 'Product' ? $item->product_id : ($item->type === 'Texture' ? $item->texture_id : $item->raw_material_id) }}"
                                                                            data-item-type="{{ $item->type }}"
                                                                            data-item-name="{{ $item->name }}"
                                                                            data-item-price="{{ $item->type === 'Product' ? $item->price : '' }}">
                                                                            <i class="bi bi-truck me-1"></i>Set Supplier
                                                                        </button>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @if($supplierId === 'no_supplier')
                                            <div class="card-footer bg-white border-top-0 p-3 text-center">
                                                <small class="text-muted">Use <strong>Set Supplier</strong> on any row above to enable automated reordering.</small>
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

    <!-- Set Default Supplier Modal -->
    <div class="modal fade inventory-modal" id="setSupplierModal" tabindex="-1" aria-labelledby="setSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <!-- Themed Dark Header -->
                <div class="inventory-modal-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="inventory-eyebrow">Inventory</span>
                            <span class="inventory-eyebrow-divider">/</span>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="setSupplierModalLabel">Set Default Supplier</h5>
                        </div>
                        <button type="button" class="inventory-close-btn" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.inventory.assignSupplier') }}">
                    @csrf
                    <input type="hidden" name="item_id" id="setSupplierItemId">
                    <input type="hidden" name="item_type" id="setSupplierItemType">

                    <div class="modal-body p-4 bg-white">
                        <p class="text-muted small mb-4">
                            Assign a default supplier for <strong class="text-dark" id="setSupplierItemName">this item</strong> to enable automated reordering.
                        </p>

                        <h6 class="inventory-section-title">
                            <i class="bi bi-truck me-2"></i>Supplier Details
                        </h6>

                        <div class="mb-3">
                            <label for="setSupplierSelect" class="form-label small fw-bold text-muted text-uppercase">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="setSupplierSelect" class="form-select inventory-field-input" required>
                                <option value="" disabled selected>Choose a supplier...</option>
                                @foreach($allSuppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-1 d-none" id="setSupplierCostWrapper">
                            <label for="setSupplierCost" class="form-label small fw-bold text-muted text-uppercase">Cost per Unit <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text inventory-input-addon">₱</span>
                                <input type="number" step="0.01" min="0" name="cost" id="setSupplierCost"
                                    class="form-control inventory-field-input fw-bold text-success" placeholder="0.00">
                            </div>
                            <div class="form-text small">Supplier's acquisition cost. Refine MOQ and lead time later on the product page.</div>
                        </div>

                        @if($allSuppliers->isEmpty())
                            <div class="alert alert-warning mt-3 mb-0 small rounded-3 border-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                No suppliers exist yet. <a href="{{ route('admin.suppliers.index') }}">Add a supplier</a> first.
                            </div>
                        @endif
                    </div>

                    <div class="inventory-modal-footer">
                        <button type="button" class="btn inventory-btn-cancel rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn inventory-btn-save rounded-pill px-4" {{ $allSuppliers->isEmpty() ? 'disabled' : '' }}>
                            <i class="bi bi-check2 me-1"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* ============================================
           Inventory Modal Theme (mirrors product modal)
           ============================================ */
        .inventory-modal .modal-content { border-radius: 18px; }

        .inventory-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .inventory-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .inventory-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .inventory-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .inventory-close-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .inventory-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .inventory-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .inventory-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .inventory-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }
        .inventory-input-addon {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }
        .inventory-modal .input-group > .inventory-field-input {
            border-radius: 0 10px 10px 0 !important;
        }

        .inventory-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .inventory-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .inventory-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .inventory-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .inventory-btn-save:hover:not(:disabled) {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var setSupplierModal = document.getElementById('setSupplierModal');
            if (!setSupplierModal) return;

            setSupplierModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) return;

                var itemType = button.getAttribute('data-item-type');
                var itemPrice = button.getAttribute('data-item-price');

                document.getElementById('setSupplierItemId').value = button.getAttribute('data-item-id');
                document.getElementById('setSupplierItemType').value = itemType;
                document.getElementById('setSupplierItemName').textContent = button.getAttribute('data-item-name');
                document.getElementById('setSupplierSelect').selectedIndex = 0;

                var costWrapper = document.getElementById('setSupplierCostWrapper');
                var costInput = document.getElementById('setSupplierCost');
                if (itemType === 'Product') {
                    costWrapper.classList.remove('d-none');
                    costInput.required = true;
                    costInput.value = itemPrice || '';
                } else {
                    costWrapper.classList.add('d-none');
                    costInput.required = false;
                    costInput.value = '';
                }
            });
        });
    </script>
@endsection
