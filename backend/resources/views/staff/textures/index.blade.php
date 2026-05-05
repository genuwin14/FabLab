@extends('layout.app')

@section('content')
    <div class="d-flex vh-100" style="background-color: #f8f9fa; overflow: hidden;">
        <!-- Desktop Sidebar -->
        <aside class="d-none d-md-block border-end border-white border-opacity-10 shadow-sm position-fixed top-0 start-0 h-100"
            style="width: 280px; z-index: 1040; background-color: #05111a;">
            @include('staff.partials.sidebar')
        </aside>

        <!-- Spacer for fixed sidebar -->
        <div class="d-none d-md-block sidebar-spacer flex-shrink-0" style="width: 280px;"></div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start border-0" tabindex="-1" id="staffSidebarOffcanvas"
            aria-labelledby="staffSidebarOffcanvasLabel" style="width: 280px; background-color: #0e2e45;">
            <div class="offcanvas-body p-0 overflow-hidden">
                @include('staff.partials.sidebar')
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="background-color: #f1f4f8; overflow: hidden;">
            <!-- Top Navbar -->
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('staff.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Textures</h4>
                            <p class="text-muted small mb-0">View texture catalog and update stock or details.</p>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Textures Grid -->
                    <div class="row g-4">
                        @forelse($textures as $texture)
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-shadow transition">
                                    <div class="position-relative" style="height: 180px;">
                                        @if($texture->image_path)
                                            <img src="{{ $texture->image_path }}" class="card-img-top h-100 w-100 object-fit-cover" alt="{{ $texture->name }}">
                                        @else
                                            <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-light text-muted">
                                                <i class="bi bi-image fs-1"></i>
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <button type="button" class="btn btn-white btn-sm rounded-circle shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#editTextureModal"
                                                data-id="{{ $texture->texture_id }}"
                                                data-name="{{ $texture->name }}"
                                                data-description="{{ $texture->description }}"
                                                data-image="{{ $texture->image_path }}"
                                                data-supplier_id="{{ $texture->supplier_id }}"
                                                data-cost="{{ $texture->cost_per_unit }}"
                                                data-stock="{{ $texture->stock_quantity }}"
                                                data-threshold="{{ $texture->low_stock_threshold }}"
                                                data-unit="{{ $texture->unit }}"
                                                data-price_modifier="{{ $texture->price_modifier }}"
                                                title="Edit">
                                                <i class="bi bi-pencil text-warning"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="fw-bold text-dark mb-0">{{ $texture->name }}</h6>
                                            @if($texture->price_modifier > 0)
                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill" style="font-size: 0.65rem;">
                                                    +₱{{ number_format($texture->price_modifier, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-muted small mb-2">{{ Str::limit($texture->description, 60) ?? 'No description' }}</p>
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                            <div>
                                                <div class="text-muted small" style="font-size: 0.7rem;">
                                                    <i class="bi bi-truck me-1"></i>{{ $texture->supplier->name ?? 'No supplier' }}
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="fw-bold small {{ $texture->stock_quantity <= $texture->low_stock_threshold ? 'text-danger' : 'text-success' }}">
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

                    <!-- Pagination -->
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <p class="text-muted small mb-0">
                            Showing {{ $textures->firstItem() ?? 0 }} to {{ $textures->lastItem() ?? 0 }} of {{ $textures->total() }} textures
                        </p>
                        <div>
                            {{ $textures->links() }}
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Edit Texture Modal -->
    @include('staff.textures.components.modal-edit')

    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        }
        .transition {
            transition: all 0.3s ease;
        }
        .object-fit-cover {
            object-fit: cover;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editTextureModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var description = button.getAttribute('data-description');
                var image = button.getAttribute('data-image');

                var form = document.getElementById('editTextureForm');
                form.action = '/staff/textures/' + id;

                document.getElementById('editTextureName').value = name;
                document.getElementById('editTextureDescription').value = description;
                document.getElementById('editTextureSupplier').value = button.getAttribute('data-supplier_id') || '';
                document.getElementById('editTextureCost').value = button.getAttribute('data-cost') || 0;
                document.getElementById('editTextureStock').value = button.getAttribute('data-stock') || 0;
                document.getElementById('editTextureThreshold').value = button.getAttribute('data-threshold') || 0;
                document.getElementById('editTextureUnit').value = button.getAttribute('data-unit') || 'pcs';
                document.getElementById('editTexturePriceModifier').value = button.getAttribute('data-price_modifier') || 0;

                var preview = document.getElementById('editTexturePreview');
                if (image) {
                    preview.src = image;
                    preview.classList.remove('d-none');
                } else {
                    preview.classList.add('d-none');
                }
            });
        });
    </script>
@endsection
