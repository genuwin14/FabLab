@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040;">
            @include('admin.partials.sidebar')
        </aside>
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="adminSidebarOffcanvas"
            aria-labelledby="adminSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('admin.partials.sidebar')
            </div>
        </div>

        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Filters & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="equipmentFilterForm" method="GET" action="{{ route('admin.equipment.index') }}"
                                class="row g-2 align-items-center">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">

                                <!-- Search: inline on desktop; icon-triggered dropdown on mobile. -->
                                <div class="col-auto col-lg dropdown search-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none search-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Search">
                                        <i class="bi bi-search text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu search-menu border-0 shadow p-2 p-lg-0">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                                <i class="bi bi-search text-muted"></i>
                                            </span>
                                            <input type="text" name="search" value="{{ $search }}"
                                                class="form-control border-start-0 rounded-end-2 ps-0"
                                                placeholder="Search by name, brand, or property number...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Status filter: inline on desktop; behind a filter icon on mobile. -->
                                <div class="col-auto dropdown filter-dd">
                                    <button type="button"
                                        class="btn btn-light rounded-2 d-lg-none filter-toggle"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false" title="Filters">
                                        <i class="bi bi-funnel text-primary"></i>
                                    </button>
                                    <div class="dropdown-menu filter-menu border-0 shadow p-2 p-lg-0">
                                        <div class="d-flex flex-column flex-lg-row gap-2">
                                            <select name="status" class="form-select rounded-2 w-100"
                                                onchange="document.getElementById('equipmentFilterForm').submit()">
                                                <option value="">All Statuses</option>
                                                <option value="Serviceable" {{ $status === 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                                                <option value="Non-Serviceable" {{ $status === 'Non-Serviceable' ? 'selected' : '' }}>Non-Serviceable</option>
                                                <option value="Functional" {{ $status === 'Functional' ? 'selected' : '' }}>Functional</option>
                                                <option value="Returned to supplier for repair" {{ $status === 'Returned to supplier for repair' ? 'selected' : '' }}>Returned for Repair</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <a href="{{ route('admin.equipment.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#addEquipmentModal" title="Add Equipment">
                                        <i class="bi bi-plus-lg small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Add Equipment</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Equipment Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden data-table-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-primary bg-opacity-10">
                                            <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">Equipment</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Brand / Model</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Property No.</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Date Acquired</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Cost</th>
                                            <th class="py-3 text-primary small text-uppercase fw-bold border-0">Status</th>
                                            <th class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($equipment as $item)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <h6 class="fw-bold text-dark mb-0">{{ $item->name }}</h6>
                                                    @if($item->notes)
                                                        <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($item->notes, 40) }}</p>
                                                    @endif
                                                </td>
                                                <td class="text-muted small">{{ $item->brand ?: '—' }}</td>
                                                <td class="font-monospace small">{{ $item->property_no ?: '—' }}</td>
                                                <td class="text-muted small">
                                                    {{ $item->date_acquired ? $item->date_acquired->format('M j, Y') : '—' }}
                                                </td>
                                                <td class="fw-bold text-dark">₱{{ number_format($item->cost, 2) }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'Serviceable' => ['bg' => '#d1f3df', 'fg' => '#0c6c3a'],
                                                            'Functional' => ['bg' => '#d1f3df', 'fg' => '#0c6c3a'],
                                                            'Non-Serviceable' => ['bg' => '#fbe1e3', 'fg' => '#a02633'],
                                                            'Returned to supplier for repair' => ['bg' => '#fff3cd', 'fg' => '#7a5b00'],
                                                        ];
                                                        $colors = $statusColors[$item->status] ?? ['bg' => '#e9ecef', 'fg' => '#495057'];
                                                    @endphp
                                                    <span class="badge rounded-pill px-3 py-2"
                                                        style="background-color: {{ $colors['bg'] }}; color: {{ $colors['fg'] }};">
                                                        {{ $item->status }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-light btn-sm table-action-btn me-1"
                                                        data-bs-toggle="modal" data-bs-target="#editEquipmentModal"
                                                        data-id="{{ $item->equipment_id }}"
                                                        data-name="{{ $item->name }}"
                                                        data-brand="{{ $item->brand }}"
                                                        data-property_no="{{ $item->property_no }}"
                                                        data-date_acquired="{{ $item->date_acquired ? $item->date_acquired->format('Y-m-d') : '' }}"
                                                        data-cost="{{ $item->cost }}"
                                                        data-status="{{ $item->status }}"
                                                        data-notes="{{ $item->notes }}" title="Edit">
                                                        <i class="bi bi-pencil text-warning"></i>
                                                    </button>
                                                    <button class="btn btn-light btn-sm table-action-btn"
                                                        data-bs-toggle="modal" data-bs-target="#deleteEquipmentModal"
                                                        data-id="{{ $item->equipment_id }}"
                                                        data-name="{{ $item->name }}" title="Delete">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <div class="mb-3">
                                                        <i class="bi bi-tools fs-1 opacity-25"></i>
                                                    </div>
                                                    No equipment found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <!-- Pagination sits INSIDE .table-responsive, so it
                                     rides the table's single horizontal scrollbar. -->
                                <div class="pagination-bar border-top d-flex justify-content-between align-items-center gap-2 p-3">
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <label class="text-muted small mb-0">Rows per page:</label>
                                        <select class="form-select form-select-sm rounded-pill w-auto"
                                            onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                            @foreach([10, 25, 50, 100] as $size)
                                                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-muted small text-nowrap">
                                            Showing {{ $equipment->firstItem() ?? 0 }} to {{ $equipment->lastItem() ?? 0 }} of {{ $equipment->total() }} entries
                                        </span>
                                    </div>
                                    <nav class="flex-shrink-0">
                                        {{ $equipment->links() }}
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    @include('admin.equipment.components.modal-add')
    @include('admin.equipment.components.modal-edit')
    @include('admin.equipment.components.modal-delete')

    <style>
        .equipment-modal .modal-content { border-radius: 18px; }
        .equipment-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .equipment-modal-header::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .equipment-eyebrow {
            font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.08em; color: rgba(255, 197, 8, 0.85);
        }
        .equipment-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }
        .equipment-close-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
            width: 32px; height: 32px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.85rem; transition: all 0.2s ease;
        }
        .equipment-close-btn:hover {
            background: rgba(255, 197, 8, 0.12); color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }
        .equipment-section-title {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em; color: #6c757d;
            padding-bottom: 10px; margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        .equipment-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            padding: 0.6rem 0.85rem !important;
            transition: all 0.2s ease;
        }
        .equipment-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 197, 8, 0.15) !important;
        }
        .equipment-input-addon {
            background-color: #f8f9fa; border: 1px solid transparent;
            border-right: none; border-radius: 10px 0 0 10px;
            color: #495057; font-weight: 600;
        }
        .equipment-modal-footer {
            display: flex; justify-content: flex-end; gap: 0.5rem;
            padding: 16px 24px;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            border-top: 1px solid rgba(0,0,0,0.06);
        }
        .equipment-btn-cancel {
            background-color: transparent; color: #6c757d;
            border: 1px solid rgba(0,0,0,0.1); font-weight: 600;
        }
        .equipment-btn-cancel:hover { background-color: #f8f9fa; color: #212529; }
        .equipment-btn-save {
            background: linear-gradient(135deg, #ffc508 0%, #ffb700 100%);
            color: #0e2e45; border: none; font-weight: 700;
            box-shadow: 0 2px 6px rgba(255, 197, 8, 0.3);
        }
        .equipment-btn-save:hover {
            background: linear-gradient(135deg, #ffb700 0%, #ffa500 100%);
            color: #0e2e45;
        }

        /* ============================================
           Mobile responsiveness ( < lg / 992px )
           See ResponsiveMobileNote.md
           ============================================ */
        @media (min-width: 992px) {
            .search-dd .dropdown-menu.search-menu,
            .filter-dd .dropdown-menu.filter-menu {
                position: static !important;
                display: block !important;
                float: none;
                width: 100%;
                margin: 0;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                background: transparent;
            }
            .filter-dd .filter-menu .form-select { width: auto !important; }
        }
        @media (max-width: 991.98px) {
            .search-dd .dropdown-menu.search-menu {
                width: min(82vw, 360px);
            }
            .filter-dd .dropdown-menu.filter-menu {
                width: min(82vw, 320px);
            }

            /* Edge-to-edge scrollable table; pagination shares its scroll. */
            .data-table-card {
                margin-left: -0.75rem;
                margin-right: -0.75rem;
                border-radius: 0 !important;
            }
            .data-table-card .table {
                min-width: 860px;
            }
            .data-table-card .table th,
            .data-table-card .table td {
                white-space: nowrap;
            }
            .data-table-card .table th:first-child,
            .data-table-card .table td:first-child {
                min-width: 240px;
            }
            .pagination-bar {
                flex-wrap: nowrap;
                min-width: 860px;
            }
            .pagination-bar .pagination {
                --bs-pagination-padding-x: 0.5rem;
                --bs-pagination-padding-y: 0.25rem;
                --bs-pagination-font-size: 0.8rem;
                margin-bottom: 0;
            }

            /* Smaller modal type + spacing on phones. */
            .modal-title { font-size: 1rem; }
            .modal-body { font-size: 0.85rem; }
            .modal .form-label,
            .modal .form-control,
            .modal .form-select,
            .modal .input-group-text,
            .modal .btn,
            .modal small,
            .modal .small { font-size: 0.8rem; }
            .modal .equipment-section-title { font-size: 0.62rem; }
            .modal-dialog { margin: 0.5rem; }
            .equipment-modal-header { padding: 14px 16px; }
            .equipment-modal-footer { padding: 12px 16px; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editEquipmentModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var form = document.getElementById('editEquipmentForm');
                form.action = '/admin/equipment/' + id;

                document.getElementById('editEquipmentName').value = button.getAttribute('data-name') || '';
                document.getElementById('editEquipmentBrand').value = button.getAttribute('data-brand') || '';
                document.getElementById('editEquipmentPropertyNo').value = button.getAttribute('data-property_no') || '';
                document.getElementById('editEquipmentDate').value = button.getAttribute('data-date_acquired') || '';
                document.getElementById('editEquipmentCost').value = button.getAttribute('data-cost') || 0;
                document.getElementById('editEquipmentStatus').value = button.getAttribute('data-status') || 'Serviceable';
                document.getElementById('editEquipmentNotes').value = button.getAttribute('data-notes') || '';
            });

            var deleteModal = document.getElementById('deleteEquipmentModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                document.getElementById('deleteEquipmentForm').action = '/admin/equipment/' + id;
                document.getElementById('deleteEquipmentName').textContent = name;
            });
        });
    </script>
@endsection
