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
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="rawMaterialFilterForm" method="GET" action="{{ route('admin.raw-materials.index') }}"
                                class="d-flex flex-nowrap align-items-center gap-2">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">
                                <div class="input-group flex-grow-1" style="min-width: 0;">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}"
                                        class="form-control border-start-0 rounded-end-2 ps-0"
                                        placeholder="Search by name, supplier, or unit...">
                                </div>
                                <a href="{{ route('admin.raw-materials.index') }}"
                                    class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0"
                                    data-bs-toggle="modal" data-bs-target="#addRawMaterialModal">
                                    <i class="bi bi-plus-lg small"></i>
                                    <span class="small fw-bold">Add Material</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Raw Materials Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Material Name</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Supplier</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Cost</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Stock</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Threshold</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Unit</th>
                                            <th class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($rawMaterials as $material)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if($material->image_path)
                                                            <img src="{{ $material->image_path }}" alt="{{ $material->name }}"
                                                                class="rounded-3 object-fit-cover border"
                                                                style="width: 48px; height: 48px;">
                                                        @else
                                                            <div class="rounded-3 d-flex align-items-center justify-content-center bg-light text-muted border"
                                                                style="width: 48px; height: 48px;">
                                                                <i class="bi bi-box"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0">{{ $material->name }}</h6>
                                                            @if($material->description)
                                                                <p class="text-muted small mb-0">{{ Str::limit($material->description, 30) }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-primary border rounded-pill px-3">
                                                        {{ $material->supplier->name }}
                                                    </span>
                                                </td>
                                                <td class="fw-bold text-dark">₱{{ number_format($material->cost_per_unit, 2) }}</td>
                                                <td>
                                                    <span class="fw-bold {{ $material->stock_quantity <= $material->low_stock_threshold ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($material->stock_quantity, 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-muted small">{{ number_format($material->low_stock_threshold, 2) }}</td>
                                                <td class="text-muted small text-uppercase">{{ $material->unit }}</td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-light btn-sm rounded-circle me-1"
                                                        data-bs-toggle="modal" data-bs-target="#editRawMaterialModal"
                                                        data-id="{{ $material->raw_material_id }}"
                                                        data-name="{{ $material->name }}"
                                                        data-supplier_id="{{ $material->supplier_id }}"
                                                        data-cost="{{ $material->cost_per_unit }}"
                                                        data-stock="{{ $material->stock_quantity }}"
                                                        data-threshold="{{ $material->low_stock_threshold }}"
                                                        data-unit="{{ $material->unit }}"
                                                        data-description="{{ $material->description }}"
                                                        data-image="{{ $material->image_path }}" title="Edit">
                                                        <i class="bi bi-pencil text-warning"></i>
                                                    </button>
                                                    <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="modal"
                                                        data-bs-target="#deleteRawMaterialModal"
                                                        data-id="{{ $material->raw_material_id }}" 
                                                        data-name="{{ $material->name }}"
                                                        title="Delete">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <div class="mb-3">
                                                        <i class="bi bi-inboxes fs-1 opacity-25"></i>
                                                    </div>
                                                    No raw materials found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="perPageSelect" class="text-muted small mb-0">Rows per page:</label>
                                    <select id="perPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                                        onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                        @foreach([10, 25, 50, 100] as $size)
                                            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-muted small">
                                        Showing {{ $rawMaterials->firstItem() ?? 0 }} to {{ $rawMaterials->lastItem() ?? 0 }} of {{ $rawMaterials->total() }} entries
                                    </span>
                                </div>
                                <nav>
                                    {{ $rawMaterials->links() }}
                                </nav>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Add Raw Material Modal -->
    @include('admin.raw-materials.components.modal-add')
    <!-- Edit Raw Material Modal -->
    @include('admin.raw-materials.components.modal-edit')
    <!-- Delete Raw Material Modal -->
    @include('admin.raw-materials.components.modal-delete')

    <style>
        /* ============================================
           Shared Material Modal Theme (Add / Edit)
           ============================================ */
        .material-modal .modal-content { border-radius: 18px; }

        .material-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .material-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .material-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .material-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .material-close-btn {
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
        .material-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        /* Section titles */
        .material-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        /* Form inputs */
        .material-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .material-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }
        .material-input-addon {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }
        .material-modal .input-group > .material-field-input {
            border-radius: 0 10px 10px 0 !important;
        }

        /* Footer */
        .material-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .material-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .material-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .material-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .material-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* ============================================
           Delete Raw Material Modal (mirrors logout)
           ============================================ */
        .material-delete-modal-dialog { max-width: 400px; }
        .material-delete-modal .modal-content { border-radius: 18px; }

        .material-delete-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }
        .material-delete-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
        }
        .material-delete-modal-icon {
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

        .material-delete-modal-body { background-color: #fff; }

        .material-delete-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        .material-delete-modal-footer .btn { white-space: nowrap; }

        .material-delete-cancel-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .material-delete-cancel-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .material-delete-confirm-btn {
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            transition: all 0.2s ease;
        }
        .material-delete-confirm-btn:hover {
            background-color: #b02a37;
            border-color: #b02a37;
            color: #fff;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit Modal Logic
            var editModal = document.getElementById('editRawMaterialModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var supplierId = button.getAttribute('data-supplier_id');
                var cost = button.getAttribute('data-cost');
                var stock = button.getAttribute('data-stock');
                var threshold = button.getAttribute('data-threshold');
                var unit = button.getAttribute('data-unit');
                var description = button.getAttribute('data-description');

                var form = document.getElementById('editRawMaterialForm');
                form.action = '/admin/raw-materials/' + id;

                document.getElementById('editMaterialName').value = name;
                document.getElementById('editMaterialSupplier').value = supplierId;
                document.getElementById('editMaterialCost').value = cost;
                document.getElementById('editMaterialStock').value = stock;
                document.getElementById('editMaterialThreshold').value = threshold;
                document.getElementById('editMaterialUnit').value = unit;
                document.getElementById('editMaterialDescription').value = description;

                var image = button.getAttribute('data-image');
                var preview = document.getElementById('editMaterialPreview');
                if (image) {
                    preview.src = image;
                    preview.classList.remove('d-none');
                } else {
                    preview.src = '#';
                    preview.classList.add('d-none');
                }
            });

            // Delete Modal Logic
            var deleteModal = document.getElementById('deleteRawMaterialModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');

                var form = document.getElementById('deleteRawMaterialForm');
                form.action = '/admin/raw-materials/' + id;

                document.getElementById('deleteMaterialName').textContent = name;
            });
        });
    </script>
@endsection
