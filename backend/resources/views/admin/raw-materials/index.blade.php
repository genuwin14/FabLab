@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end shadow-sm position-fixed top-0 start-0 h-100"
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
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1060;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Raw Materials</h4>
                            <p class="text-muted small mb-0">Manage stock components and material sourcing.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary d-flex align-items-center gap-2 rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#addRawMaterialModal">
                                <i class="bi bi-plus-lg small"></i>
                                <span class="small fw-bold">Add Material</span>
                            </button>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Raw Materials Table -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-4 text-muted small text-uppercase border-0 rounded-start-2">
                                                Material Name</th>
                                            <th class="text-muted small text-uppercase border-0">Supplier</th>
                                            <th class="text-muted small text-uppercase border-0">Cost</th>
                                            <th class="text-muted small text-uppercase border-0">Stock</th>
                                            <th class="text-muted small text-uppercase border-0">Threshold</th>
                                            <th class="text-muted small text-uppercase border-0">Unit</th>
                                            <th
                                                class="text-end pe-4 text-muted small text-uppercase border-0 rounded-end-2">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($rawMaterials as $material)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="bi bi-box"></i>
                                                        </div>
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
                                                        data-description="{{ $material->description }}" title="Edit">
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
                                                <td colspan="6" class="text-center py-5 text-muted">
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
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <span class="text-muted small">
                                    Showing {{ $rawMaterials->firstItem() }} to {{ $rawMaterials->lastItem() }} of {{ $rawMaterials->total() }} entries
                                </span>
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
