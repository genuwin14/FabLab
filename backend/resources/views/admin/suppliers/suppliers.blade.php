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

                    <!-- Filters, Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="supplierFilterForm" method="GET" action="{{ route('admin.suppliers.index') }}"
                                class="d-flex flex-nowrap align-items-center gap-2">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">
                                <div class="input-group flex-grow-1" style="min-width: 0;">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}"
                                        class="form-control border-start-0 rounded-end-2 ps-0"
                                        placeholder="Search by company, contact person, email, or phone...">
                                </div>
                                <a href="{{ route('admin.suppliers.index') }}"
                                    class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip"
                                    title="Reset filters">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0"
                                    data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                    <i class="bi bi-plus-lg small"></i>
                                    <span class="small fw-bold">Add Supplier</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Suppliers Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Company</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Contact Person</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Contact Info</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">
                                                Address</th>
                                            <th
                                                class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($suppliers as $supplier)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                            style="width: 40px; height: 40px; background-color: #0e2e45; color: #ffc508;">
                                                            {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0">{{ $supplier->name }}</h6>
                                                            <small class="text-muted">Supplier #{{ $supplier->supplier_id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($supplier->contact_person)
                                                        <span class="text-dark small">{{ $supplier->contact_person }}</span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column small">
                                                        @if($supplier->email)
                                                            <span class="text-dark">
                                                                <i class="bi bi-envelope me-1 text-muted"></i>{{ $supplier->email }}
                                                            </span>
                                                        @endif
                                                        @if($supplier->phone)
                                                            <span class="text-muted">
                                                                <i class="bi bi-telephone me-1 text-muted"></i>{{ $supplier->phone }}
                                                            </span>
                                                        @endif
                                                        @if(!$supplier->email && !$supplier->phone)
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-muted small">
                                                    {{ $supplier->address ? Str::limit($supplier->address, 50) : '—' }}
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button class="btn btn-light btn-sm rounded-circle"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editSupplierModal"
                                                            data-id="{{ $supplier->supplier_id }}"
                                                            data-name="{{ $supplier->name }}"
                                                            data-contact_person="{{ $supplier->contact_person }}"
                                                            data-email="{{ $supplier->email }}"
                                                            data-phone="{{ $supplier->phone }}"
                                                            data-address="{{ $supplier->address }}"
                                                            title="Edit Supplier">
                                                            <i class="bi bi-pencil text-warning"></i>
                                                        </button>
                                                        <button class="btn btn-light btn-sm rounded-circle"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteSupplierModal"
                                                            data-id="{{ $supplier->supplier_id }}"
                                                            data-name="{{ $supplier->name }}"
                                                            title="Delete Supplier">
                                                            <i class="bi bi-trash text-danger"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="bi bi-truck display-6 d-block mb-3 opacity-50"></i>
                                                    No suppliers found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div
                                class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="perPageSelect" class="text-muted small mb-0">Rows per page:</label>
                                    <select id="perPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                                        onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                        @foreach([10, 25, 50, 100] as $size)
                                            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>
                                                {{ $size }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-muted small">
                                        Showing {{ $suppliers->firstItem() ?? 0 }} to
                                        {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} entries
                                    </span>
                                </div>
                                <nav>
                                    {{ $suppliers->links() }}
                                </nav>
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

    <style>
        /* ============================================
           Supplier Modal Theme (mirrors product modal)
           ============================================ */
        .supplier-modal .modal-content { border-radius: 18px; }

        .supplier-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .supplier-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .supplier-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .supplier-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .supplier-close-btn {
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
        .supplier-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .supplier-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .supplier-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .supplier-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }
        .supplier-input-addon {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }
        .supplier-modal .input-group > .supplier-field-input {
            border-radius: 0 10px 10px 0 !important;
        }

        .supplier-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .supplier-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .supplier-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .supplier-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .supplier-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* ============================================
           Delete Supplier Modal (mirrors product delete)
           ============================================ */
        .supplier-delete-modal-dialog { max-width: 400px; }
        .supplier-delete-modal .modal-content { border-radius: 18px; }

        .supplier-delete-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }
        .supplier-delete-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
        }
        .supplier-delete-modal-icon {
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

        .supplier-delete-modal-body { background-color: #fff; }

        .supplier-delete-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .supplier-delete-cancel-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .supplier-delete-cancel-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .supplier-delete-confirm-btn {
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            transition: all 0.2s ease;
        }
        .supplier-delete-confirm-btn:hover {
            background-color: #b02a37;
            border-color: #b02a37;
            color: #fff;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit Modal Logic
            var editSupplierModal = document.getElementById('editSupplierModal');
            if (editSupplierModal) {
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

                    document.getElementById('editSupplierName').value = name || '';
                    document.getElementById('editSupplierPerson').value = contactPerson || '';
                    document.getElementById('editSupplierEmail').value = email || '';
                    document.getElementById('editSupplierPhone').value = phone || '';
                    document.getElementById('editSupplierAddress').value = address || '';
                });
            }

            // Delete Modal Logic
            var deleteSupplierModal = document.getElementById('deleteSupplierModal');
            if (deleteSupplierModal) {
                deleteSupplierModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-id');
                    var name = button.getAttribute('data-name');

                    var form = document.getElementById('deleteSupplierForm');
                    form.action = '/admin/suppliers/' + id;

                    document.getElementById('deleteSupplierName').textContent = name;
                });
            }
        });
    </script>
@endsection
