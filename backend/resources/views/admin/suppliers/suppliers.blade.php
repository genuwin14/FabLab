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
                            <h4 class="fw-bold text-primary mb-1">Suppliers</h4>
                            <p class="text-muted small mb-0">Manage vendors and supply usage sources.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary d-flex align-items-center gap-2 rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                <i class="bi bi-plus-lg small"></i>
                                <span class="small fw-bold">Add Supplier</span>
                            </button>
                        </div>
                    </div>

                    <!-- Suppliers Table -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-4 text-muted small text-uppercase border-0 rounded-start-2">
                                                Company</th>
                                            <th class="text-muted small text-uppercase border-0">Contact Person</th>
                                            <th class="text-muted small text-uppercase border-0">Contact Info</th>
                                            <th class="text-muted small text-uppercase border-0">Address</th>
                                            <th
                                                class="text-end pe-4 text-muted small text-uppercase border-0 rounded-end-2">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($suppliers as $supplier)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold"
                                                            style="width: 40px; height: 40px;">
                                                            {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0">{{ $supplier->name }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex flex-column small">
                                                        @if($supplier->email)
                                                            <span class="text-dark"><i class="bi bi-envelope me-1 text-muted"></i>
                                                                {{ $supplier->email }}</span>
                                                        @endif
                                                        @if($supplier->phone)
                                                            <span class="text-muted"><i class="bi bi-telephone me-1 text-muted"></i>
                                                                {{ $supplier->phone }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-muted small">{{ Str::limit($supplier->address, 50) ?? 'N/A' }}
                                                </td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-light btn-sm rounded-circle me-1"
                                                        data-bs-toggle="modal" data-bs-target="#editSupplierModal"
                                                        data-id="{{ $supplier->supplier_id }}" data-name="{{ $supplier->name }}"
                                                        data-contact_person="{{ $supplier->contact_person }}"
                                                        data-email="{{ $supplier->email }}" data-phone="{{ $supplier->phone }}"
                                                        data-address="{{ $supplier->address }}" title="Edit">
                                                        <i class="bi bi-pencil text-warning"></i>
                                                    </button>
                                                    <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="modal"
                                                        data-bs-target="#deleteSupplierModal"
                                                        data-id="{{ $supplier->supplier_id }}" data-name="{{ $supplier->name }}"
                                                        title="Delete">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    No suppliers found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    @include('admin.suppliers.components.modal-add-supplier')
    <!-- Edit Supplier Modal -->
    @include('admin.suppliers.components.modal-edit-supplier')
    <!-- Delete Supplier Modal -->
    @include('admin.suppliers.components.modal-delete-supplier')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit Modal Logic
            var editSupplierModal = document.getElementById('editSupplierModal');
            editSupplierModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var contactPerson = button.getAttribute('data-contact_person');
                var email = button.getAttribute('data-email');
                var phone = button.getAttribute('data-phone');
                var address = button.getAttribute('data-address');

                var form = document.getElementById('editSupplierForm');
                form.action = '/admin/suppliers/' + id;

                document.getElementById('editSupplierName').value = name;
                document.getElementById('editSupplierPerson').value = contactPerson;
                document.getElementById('editSupplierEmail').value = email;
                document.getElementById('editSupplierPhone').value = phone;
                document.getElementById('editSupplierAddress').value = address;
            });

            // Delete Modal Logic
            var deleteSupplierModal = document.getElementById('deleteSupplierModal');
            deleteSupplierModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');

                var form = document.getElementById('deleteSupplierForm');
                form.action = '/admin/suppliers/' + id;

                document.getElementById('deleteSupplierName').textContent = name;
            });
        });
    </script>
@endsection