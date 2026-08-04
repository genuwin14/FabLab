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
                            <form id="textureFilterForm" method="GET" action="{{ route('admin.textures.index') }}"
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
                                                placeholder="Search by name, supplier, or unit...">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto d-flex gap-2">
                                    <a href="{{ route('admin.textures.index') }}"
                                        class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                        <i class="bi bi-arrow-clockwise text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#addTextureModal" title="Add Texture">
                                        <i class="bi bi-plus-lg small"></i>
                                        <span class="small fw-bold d-none d-lg-inline">Add Texture</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Textures Grid -->
                    <div class="row g-3 g-md-4">
                        @forelse($textures as $texture)
                            <div class="col-12 col-md-4 col-xl-3">
                                <div class="card texture-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="position-relative texture-card-image">
                                        @if($texture->image_url)
                                            <img src="{{ $texture->image_url }}" class="h-100 w-100 object-fit-cover" alt="{{ $texture->name }}">
                                        @else
                                            <div class="h-100 w-100 d-flex align-items-center justify-content-center text-muted">
                                                <i class="bi bi-image fs-1 opacity-25"></i>
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <div class="dropdown">
                                                <button class="btn texture-card-menu-btn btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                                                           data-bs-toggle="modal" data-bs-target="#editTextureModal"
                                                           data-id="{{ $texture->texture_id }}"
                                                           data-name="{{ $texture->name }}"
                                                           data-description="{{ $texture->description }}"
                                                           data-image="{{ $texture->image_url }}"
                                                           data-supplier_id="{{ $texture->supplier_id }}"
                                                           data-cost="{{ $texture->cost_per_unit }}"
                                                           data-stock="{{ $texture->stock_quantity }}"
                                                           data-threshold="{{ $texture->low_stock_threshold }}"
                                                           data-unit="{{ $texture->unit }}"
                                                           data-price_modifier="{{ $texture->price_modifier }}"
                                                           data-units_on_display="{{ $texture->units_on_display }}"
                                                           data-units_sponsored="{{ $texture->units_sponsored }}"
                                                           data-units_damaged="{{ $texture->units_damaged }}"
                                                           data-units_consumed="{{ $texture->units_consumed }}"
                                                           data-department="{{ $texture->department }}">
                                                            <i class="bi bi-pencil text-warning"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="#"
                                                           data-bs-toggle="modal" data-bs-target="#deleteTextureModal"
                                                           data-id="{{ $texture->texture_id }}"
                                                           data-name="{{ $texture->name }}">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body texture-card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                                            <h6 class="fw-bold text-white mb-0 text-truncate">{{ $texture->name }}</h6>
                                            @if($texture->price_modifier > 0)
                                                <span class="texture-price-badge">
                                                    +₱{{ number_format($texture->price_modifier, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="texture-card-desc small mb-2">
                                            {{ $texture->description ? Str::limit($texture->description, 60) : 'No description' }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center pt-2 texture-card-meta">
                                            <div class="texture-supplier text-truncate" style="font-size: 0.7rem;">
                                                <i class="bi bi-truck me-1"></i>{{ $texture->supplier->name ?? 'No supplier' }}
                                            </div>
                                            <div class="text-end">
                                                <span class="fw-bold small {{ $texture->stock_quantity <= $texture->low_stock_threshold ? 'texture-stock-low' : 'texture-stock-ok' }}">
                                                    {{ number_format($texture->stock_quantity, 0) }} {{ $texture->unit }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body text-center py-5 text-muted">
                                        <div class="mb-3">
                                            <i class="bi bi-layers fs-1 opacity-25"></i>
                                        </div>
                                        No textures found.
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination — grid page, so it's a standalone card (no
                         table to share a scroll with); just wraps + shrinks on mobile. -->
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
                                    Showing {{ $textures->firstItem() ?? 0 }} to {{ $textures->lastItem() ?? 0 }} of {{ $textures->total() }} textures
                                </span>
                            </div>
                            <nav class="flex-shrink-0">
                                {{ $textures->links() }}
                            </nav>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Add Texture Modal -->
    @include('admin.textures.components.modal-add')
    <!-- Edit Texture Modal -->
    @include('admin.textures.components.modal-edit')
    <!-- Delete Texture Modal -->
    @include('admin.textures.components.modal-delete')

    <style>
        .object-fit-cover { object-fit: cover; }

        /* ============================================
           Texture Card — dark themed body
           ============================================ */
        .texture-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            background-color: #0e2e45;
        }
        .texture-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.65rem 1.25rem rgba(5, 17, 26, 0.25) !important;
        }
        .texture-card-image {
            height: 180px;
            background-color: #fff;
        }
        .texture-card-image img { display: block; }

        .texture-card-menu-btn {
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
        .texture-card-menu-btn:hover,
        .texture-card-menu-btn[aria-expanded="true"] {
            background: rgba(255, 197, 8, 0.15);
            border-color: rgba(255, 197, 8, 0.4);
            color: #ffc508;
        }

        .texture-card-body {
            background: linear-gradient(180deg, #0e2e45 0%, #05111a 100%);
            color: rgba(255, 255, 255, 0.85);
            border-top: 1px solid rgba(255, 197, 8, 0.15);
        }
        .texture-card-desc {
            color: rgba(255, 255, 255, 0.55);
        }
        .texture-card-meta {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .texture-supplier {
            color: rgba(255, 255, 255, 0.55);
        }
        .texture-stock-ok { color: #4ade80; }
        .texture-stock-low { color: #ff8088; }

        .texture-price-badge {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(255, 197, 8, 0.15);
            color: #ffc508;
            border: 1px solid rgba(255, 197, 8, 0.25);
            white-space: nowrap;
        }

        /* ============================================
           Shared Texture Modal Theme (Add / Edit)
           ============================================ */
        .texture-modal .modal-content { border-radius: 18px; }

        .texture-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 18px 24px;
            position: relative;
        }
        .texture-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 197, 8, 0.3), transparent);
        }
        .texture-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 197, 8, 0.85);
        }
        .texture-eyebrow-divider { color: rgba(255, 255, 255, 0.2); font-weight: 300; }

        .texture-close-btn {
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
        .texture-close-btn:hover {
            background: rgba(255, 197, 8, 0.12);
            color: #ffc508;
            border-color: rgba(255, 197, 8, 0.3);
        }

        .texture-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .texture-field-input {
            background-color: #f8f9fa !important;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            transition: all 0.2s ease;
            padding: 0.6rem 0.85rem;
        }
        .texture-field-input:focus {
            background-color: #fff !important;
            border-color: #ffc508 !important;
            box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12) !important;
        }
        .texture-input-addon {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }
        .texture-modal .input-group > .texture-field-input {
            border-radius: 0 10px 10px 0 !important;
        }

        .texture-modal-footer {
            background-color: #fff;
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .texture-btn-cancel {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }
        .texture-btn-cancel:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }
        .texture-btn-save {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .texture-btn-save:hover {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }

        /* ============================================
           Delete Texture Modal (mirrors logout)
           ============================================ */
        .texture-delete-modal-dialog { max-width: 400px; }
        .texture-delete-modal .modal-content { border-radius: 18px; }

        .texture-delete-modal-header {
            background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
            padding: 28px 24px 20px;
            text-align: center;
            position: relative;
        }
        .texture-delete-modal-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(220, 53, 69, 0.4), transparent);
        }
        .texture-delete-modal-icon {
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

        .texture-delete-modal-body { background-color: #fff; }

        .texture-delete-modal-footer {
            background-color: #fff;
            padding: 16px 24px 24px;
            display: flex;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        .texture-delete-modal-footer .btn { white-space: nowrap; }

        .texture-delete-cancel-btn {
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .texture-delete-cancel-btn:hover {
            background-color: #e9ecef;
            color: #0e2e45;
        }

        .texture-delete-confirm-btn {
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: #fff;
            transition: all 0.2s ease;
        }
        .texture-delete-confirm-btn:hover {
            background-color: #b02a37;
            border-color: #b02a37;
            color: #fff;
        }

        /* ============================================
           Mobile responsiveness ( < lg / 992px )
           See ResponsiveMobileNote.md — grid page (no table)
           ============================================ */
        @media (min-width: 992px) {
            .search-dd .dropdown-menu.search-menu {
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
        }
        @media (max-width: 991.98px) {
            .search-dd .dropdown-menu.search-menu {
                width: min(82vw, 360px);
            }

            /* Standalone pagination card: shrink Bootstrap's pager so it
               wraps neatly instead of overflowing. */
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
            .modal .texture-section-title { font-size: 0.62rem; }
            .texture-modal .modal-dialog,
            .texture-delete-modal .modal-dialog {
                margin: 0.5rem;
            }
            .texture-modal-header,
            .texture-delete-modal-header {
                padding: 14px 16px;
            }
            .texture-modal-footer,
            .texture-delete-modal-footer {
                padding: 12px 16px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit Modal Logic
            var editModal = document.getElementById('editTextureModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var description = button.getAttribute('data-description');
                var image = button.getAttribute('data-image');

                var form = document.getElementById('editTextureForm');
                form.action = '/admin/textures/' + id;

                document.getElementById('editTextureName').value = name;
                document.getElementById('editTextureDescription').value = description;
                document.getElementById('editTextureSupplier').value = button.getAttribute('data-supplier_id') || '';
                document.getElementById('editTextureCost').value = button.getAttribute('data-cost') || 0;
                document.getElementById('editTextureStock').value = button.getAttribute('data-stock') || 0;
                document.getElementById('editTextureThreshold').value = button.getAttribute('data-threshold') || 0;
                document.getElementById('editTextureUnit').value = button.getAttribute('data-unit') || 'pcs';
                document.getElementById('editTexturePriceModifier').value = button.getAttribute('data-price_modifier') || 0;
                document.getElementById('editTextureDepartment').value = button.getAttribute('data-department') || '';
                document.getElementById('editTextureDisplay').value = button.getAttribute('data-units_on_display') || 0;
                document.getElementById('editTextureSponsored').value = button.getAttribute('data-units_sponsored') || 0;
                document.getElementById('editTextureDamaged').value = button.getAttribute('data-units_damaged') || 0;
                document.getElementById('editTextureConsumed').value = button.getAttribute('data-units_consumed') || 0;

                var preview = document.getElementById('editTexturePreview');
                if (image) {
                    preview.src = image;
                    preview.classList.remove('d-none');
                } else {
                    preview.classList.add('d-none');
                }
            });

            // Delete Modal Logic
            var deleteModal = document.getElementById('deleteTextureModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');

                var form = document.getElementById('deleteTextureForm');
                form.action = '/admin/textures/' + id;

                document.getElementById('deleteTextureName').textContent = name;
            });
        });
    </script>
@endsection
