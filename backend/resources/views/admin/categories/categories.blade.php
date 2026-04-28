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
            <header class="flex-shrink-0 bg-white shadow-sm" style="z-index: 1042;">
                @include('admin.partials.navbar')
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">Categories</h4>
                            <p class="text-muted small mb-0">Manage product categories and groupings.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary d-flex align-items-center gap-2 rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-lg small"></i>
                                <span class="small fw-bold">Add Category</span>
                            </button>
                        </div>
                    </div>



                    <!-- Categories Table -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-4 text-muted small text-uppercase border-0 rounded-start-2"
                                                style="width: 50px;">#</th>
                                            <th class="text-muted small text-uppercase border-0">Name</th>
                                            <th class="text-muted small text-uppercase border-0">Description</th>
                                            <th
                                                class="text-end pe-4 text-muted small text-uppercase border-0 rounded-end-2">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($categories as $category)
                                            <tr>
                                                <td class="ps-4 py-3 fw-bold text-muted">{{ $loop->iteration }}</td>
                                                <td><span class="fw-bold text-dark">{{ $category->name }}</span></td>
                                                <td class="text-muted small">{{ Str::limit($category->description, 50) }}</td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-light btn-sm rounded-circle me-1"
                                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                        data-id="{{ $category->category_id }}" data-name="{{ $category->name }}"
                                                        data-description="{{ $category->description }}" title="Edit">
                                                        <i class="bi bi-pencil text-warning"></i>
                                                    </button>
                                                    <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="modal"
                                                        data-bs-target="#deleteCategoryModal"
                                                        data-id="{{ $category->category_id }}" data-name="{{ $category->name }}"
                                                        title="Delete">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    No categories found.
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

    <!-- Add Category Modal -->
    @include('admin.categories.components.modal-add-category')
    <!-- Edit Category Modal -->
    @include('admin.categories.components.modal-edit-category')
    <!-- Delete Category Modal -->
    @include('admin.categories.components.modal-delete-category')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit Modal Logic
            var editCategoryModal = document.getElementById('editCategoryModal');
            editCategoryModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var description = button.getAttribute('data-description');

                var form = document.getElementById('editCategoryForm');
                form.action = '/admin/categories/' + id;

                document.getElementById('editCategoryId').value = id;
                document.getElementById('editCategoryName').value = name;
                document.getElementById('editCategoryDescription').value = description;
            });

            // Delete Modal Logic
            var deleteCategoryModal = document.getElementById('deleteCategoryModal');
            deleteCategoryModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');

                var form = document.getElementById('deleteCategoryForm');
                form.action = '/admin/categories/' + id;

                document.getElementById('deleteCategoryName').textContent = name;
            });
        });
    </script>

@endsection