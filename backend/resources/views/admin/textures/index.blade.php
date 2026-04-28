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

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Textures</h4>
                            <p class="text-muted small mb-0">Manage visual options for product customization.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary d-flex align-items-center gap-2 rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#addTextureModal">
                                <i class="bi bi-plus-lg small"></i>
                                <span class="small fw-bold">Add Texture</span>
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
                                            <div class="dropdown">
                                                <button class="btn btn-white btn-sm rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" 
                                                           data-bs-toggle="modal" data-bs-target="#editTextureModal"
                                                           data-id="{{ $texture->texture_id }}"
                                                           data-name="{{ $texture->name }}"
                                                           data-description="{{ $texture->description }}"
                                                           data-image="{{ $texture->image_path }}">
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
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold text-dark mb-1">{{ $texture->name }}</h6>
                                        <p class="text-muted small mb-0">{{ Str::limit($texture->description, 60) ?? 'No description' }}</p>
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

    <!-- Add Texture Modal -->
    @include('admin.textures.components.modal-add')
    <!-- Edit Texture Modal -->
    @include('admin.textures.components.modal-edit')
    <!-- Delete Texture Modal -->
    @include('admin.textures.components.modal-delete')

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
