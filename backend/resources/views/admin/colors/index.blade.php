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
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <div class="mb-4">
                        <h4 class="fw-bold text-dark mb-1">Colors</h4>
                        <p class="text-muted small mb-0">
                            Plain finishes customers can pick in the 3D customizer instead of an image texture.
                            Assign which ones a product offers from
                            <a href="{{ route('admin.products.index') }}" class="fw-semibold">Products</a>.
                        </p>
                    </div>

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <ul class="mb-0 ps-3 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Search & Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form method="GET" action="{{ route('admin.colors.index') }}" class="row g-2 align-items-center">
                                <input type="hidden" name="per_page" value="{{ $perPage }}">

                                <div class="col-auto col-lg dropdown search-dd">
                                    <button type="button" class="btn btn-light rounded-2 d-lg-none search-toggle"
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
                                                placeholder="Search by name, hex code, or description...">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <a href="{{ route('admin.colors.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#addColorModal" title="Add Color">
                                        <i class="bi bi-plus-lg small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Add Color</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Colors Grid -->
                    <div class="row g-3 g-md-4">
                        @forelse($colors as $color)
                            <div class="col-12 col-md-4 col-xl-3">
                                <div class="card color-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="position-relative color-card-swatch"
                                        style="background-color: {{ $color->hex_code }};">
                                        <span class="color-card-hex" style="color: {{ $color->contrast_color }};">
                                            {{ strtoupper($color->hex_code) }}
                                        </span>
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <div class="dropdown">
                                                <button class="btn color-card-menu-btn btn-sm rounded-circle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                                                            data-bs-toggle="modal" data-bs-target="#editColorModal"
                                                            data-id="{{ $color->color_id }}"
                                                            data-name="{{ $color->name }}"
                                                            data-hex="{{ $color->hex_code }}"
                                                            data-description="{{ $color->description }}"
                                                            data-price_modifier="{{ $color->price_modifier }}"
                                                            data-raw_material_id="{{ $color->raw_material_id }}"
                                                            data-material_quantity="{{ $color->material_quantity > 0 ? rtrim(rtrim(number_format($color->material_quantity, 4, '.', ''), '0'), '.') : '' }}">
                                                            <i class="bi bi-pencil text-warning"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="#"
                                                            data-bs-toggle="modal" data-bs-target="#deleteColorModal"
                                                            data-id="{{ $color->color_id }}"
                                                            data-name="{{ $color->name }}">
                                                            <i class="bi bi-trash"></i> Retire
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body color-card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                                            <h6 class="fw-bold text-white mb-0 text-truncate">{{ $color->name }}</h6>
                                            @if($color->price_modifier > 0)
                                                <span class="color-price-badge">+₱{{ number_format($color->price_modifier, 2) }}</span>
                                            @endif
                                        </div>
                                        <p class="color-card-desc small mb-0">
                                            {{ $color->description ? Str::limit($color->description, 60) : 'No description' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body text-center py-5 text-muted">
                                        <div class="mb-3"><i class="bi bi-palette fs-1 opacity-25"></i></div>
                                        No colors yet. Add one so customers can order a plain finish.
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-body pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2 p-3">
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <label for="perPageSelect" class="text-muted small mb-0">Per page:</label>
                                <select id="perPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                                    onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('per_page',v);u.searchParams.delete('page');window.location.href=u.toString();})(this.value)">
                                    @foreach([12, 24, 48, 96] as $size)
                                        <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                                <span class="text-muted small text-nowrap d-none d-lg-inline">
                                    Showing {{ $colors->firstItem() ?? 0 }} to {{ $colors->lastItem() ?? 0 }} of {{ $colors->total() }} colors
                                </span>
                            </div>
                            <nav class="flex-shrink-0">
                                {{ $colors->links() }}
                            </nav>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    @include('admin.colors.components.modal-add')
    @include('admin.colors.components.modal-edit')
    @include('admin.colors.components.modal-delete')

    <style>
        .color-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            background-color: #0e2e45;
        }
        .color-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.65rem 1.25rem rgba(5, 17, 26, 0.25) !important;
        }

        .color-card-swatch {
            height: 180px;
            display: flex;
            align-items: flex-end;
            padding: 0.75rem;
        }
        .color-card-hex {
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            opacity: 0.85;
        }

        .color-card-menu-btn {
            background: rgba(5, 17, 26, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .color-card-menu-btn:hover,
        .color-card-menu-btn[aria-expanded="true"] {
            background: rgba(255, 197, 8, 0.15);
            border-color: rgba(255, 197, 8, 0.4);
            color: #ffc508;
        }

        .color-card-body {
            background: linear-gradient(180deg, #0e2e45 0%, #05111a 100%);
            color: rgba(255, 255, 255, 0.85);
        }
        .color-card-desc { color: rgba(255, 255, 255, 0.55); }

        .color-price-badge {
            background: rgba(255, 197, 8, 0.15);
            color: #ffc508;
            border-radius: 999px;
            padding: 0.1rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Keep the hex field and the picker reading as one control. */
        .hex-pair .form-control-color {
            width: 48px;
            padding: 0.25rem;
            flex: 0 0 auto;
        }

        /* ============================================
           Add / Edit Color Modal — mirrors the texture modals
           ============================================ */
        .color-modal .modal-content { border-radius: 18px; }

        .color-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .color-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .color-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .color-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .color-close-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
            width: 32px; height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .color-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .color-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .color-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .color-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }
        .color-input-addon {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }
        .color-modal .input-group > .color-field-input {
            border-radius: 0 10px 10px 0 !important;
        }

        .color-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .color-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .color-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .color-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .color-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* ── Retire Color Modal ── */
        .color-delete-modal-dialog { max-width: 400px; }
        .color-delete-modal .modal-content { border-radius: 18px; }

        .color-delete-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }
        .color-delete-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
        }
        .color-delete-modal-icon {
            width: 56px; height: 56px;
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

        .color-delete-modal-body { background-color: #fff; }

        .color-delete-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        .color-delete-modal-footer .btn { white-space: nowrap; }

        .color-delete-cancel-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .color-delete-cancel-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .color-delete-confirm-btn {
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            transition: all 0.2s ease;
        }
        .color-delete-confirm-btn:hover {
            background-color: #b02a37;
            border-color: #b02a37;
            color: #fff;
        }

        /* ── Search dropdown behaves as an inline field from lg up ── */
        @media (min-width: 992px) {
            .search-dd .dropdown-menu.search-menu {
                position: static !important;
                display: block !important;
                width: 100%;
                background: transparent;
                box-shadow: none !important;
            }
        }
    </style>

    <script>
        // Wire the native colour picker to the hex text field in both directions,
        // so an admin can either eyedrop a shade or paste a brand hex.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-hex-pair]').forEach(function (pair) {
                const picker = pair.querySelector('input[type="color"]');
                const text = pair.querySelector('input[type="text"]');
                if (!picker || !text) return;

                picker.addEventListener('input', () => { text.value = picker.value; });
                text.addEventListener('input', function () {
                    if (/^#[0-9A-Fa-f]{6}$/.test(text.value)) picker.value = text.value;
                });
            });

            // Edit modal: fill from the card's data attributes.
            const editModal = document.getElementById('editColorModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const hex = trigger.getAttribute('data-hex') || '#000000';
                    editModal.querySelector('form').action = '/admin/colors/' + trigger.getAttribute('data-id');
                    editModal.querySelector('#editColorName').value = trigger.getAttribute('data-name') || '';
                    editModal.querySelector('#editColorHexText').value = hex;
                    editModal.querySelector('#editColorHexPicker').value = hex;
                    editModal.querySelector('#editColorDescription').value = trigger.getAttribute('data-description') || '';
                    editModal.querySelector('#editColorPrice').value = trigger.getAttribute('data-price_modifier') || '0';
                    // Empty string rather than 0 for the quantity: the field is
                    // optional, and a 0 sitting in it reads as "deducts zero"
                    // when the honest answer is "nothing linked".
                    editModal.querySelector('#editColorMaterial').value = trigger.getAttribute('data-raw_material_id') || '';
                    editModal.querySelector('#editColorMaterialQty').value = trigger.getAttribute('data-material_quantity') || '';
                });
            }

            const deleteModal = document.getElementById('deleteColorModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    deleteModal.querySelector('form').action = '/admin/colors/' + trigger.getAttribute('data-id');
                    deleteModal.querySelector('#deleteColorName').textContent = trigger.getAttribute('data-name') || 'this color';
                });
            }
        });
    </script>
@endsection
