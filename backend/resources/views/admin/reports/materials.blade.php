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

            <main class="flex-grow-1 p-4" style="overflow-y: auto;">
                <div class="container-fluid">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-light btn-sm rounded-pill me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h4 class="fw-bold text-dark mb-0">Inventory of Materials</h4>
                            <p class="text-muted small mb-0">As of {{ $asOfDate->format('F j, Y') }}</p>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form method="GET" action="{{ route('admin.reports.materials') }}"
                                class="d-flex flex-wrap align-items-center gap-2">
                                <select name="group" class="form-select rounded-2 w-auto"
                                    onchange="this.form.submit()">
                                    <option value="all" {{ $group === 'all' ? 'selected' : '' }}>All Item Types</option>
                                    <option value="products" {{ $group === 'products' ? 'selected' : '' }}>Products Only</option>
                                    <option value="raw_materials" {{ $group === 'raw_materials' ? 'selected' : '' }}>Raw Materials Only</option>
                                    <option value="textures" {{ $group === 'textures' ? 'selected' : '' }}>Textures Only</option>
                                </select>
                                <div class="ms-auto d-flex gap-2">
                                    <a href="{{ route('admin.reports.materials.pdf', ['group' => $group]) }}"
                                        class="btn btn-danger d-flex align-items-center gap-2 rounded-2 px-3">
                                        <i class="bi bi-file-pdf"></i>
                                        <span class="small fw-bold">Export PDF</span>
                                    </a>
                                    <a href="{{ route('admin.reports.materials.docx', ['group' => $group]) }}"
                                        class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3">
                                        <i class="bi bi-file-word"></i>
                                        <span class="small fw-bold">Export Word</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @foreach($sections as $deptName => $rows)
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <div class="card-header bg-white border-0 py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="fw-bold text-dark mb-0 text-uppercase" style="letter-spacing: 0.04em;">
                                        @if($deptName === 'Uncategorized')
                                            <i class="bi bi-question-circle text-muted me-2"></i>
                                        @else
                                            <i class="bi bi-building text-warning me-2"></i>PEDS
                                        @endif
                                        {{ $deptName }}
                                    </h6>
                                    <span class="badge bg-light text-muted rounded-pill">
                                        {{ count($rows) }} {{ count($rows) === 1 ? 'item' : 'items' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr class="bg-primary bg-opacity-10">
                                                <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">Type</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0">Item</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0">Unit</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">On Display</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">Sponsored</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">Damaged</th>
                                                <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">Consumed</th>
                                                <th class="pe-4 py-3 text-primary small text-uppercase fw-bold border-0 text-end">Available</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            @forelse($rows as $row)
                                                <tr>
                                                    <td class="ps-4 py-2">
                                                        @php
                                                            $typeColor = match($row['type']) {
                                                                'Product' => '#0d6efd',
                                                                'Raw Material' => '#fd7e14',
                                                                'Texture' => '#6f42c1',
                                                                default => '#6c757d',
                                                            };
                                                        @endphp
                                                        <span class="badge rounded-pill px-3"
                                                            style="background-color: {{ $typeColor }}1f; color: {{ $typeColor }};">
                                                            {{ $row['type'] }}
                                                        </span>
                                                    </td>
                                                    <td class="fw-semibold text-dark">{{ $row['name'] }}</td>
                                                    <td class="text-muted small text-uppercase">{{ $row['unit'] }}</td>
                                                    <td class="text-end">{{ $row['on_display'] > 0 ? number_format($row['on_display']) : '—' }}</td>
                                                    <td class="text-end">{{ $row['sponsored'] > 0 ? number_format($row['sponsored']) : '—' }}</td>
                                                    <td class="text-end">{{ $row['damaged'] > 0 ? number_format($row['damaged']) : '—' }}</td>
                                                    <td class="text-end">{{ $row['consumed'] > 0 ? number_format($row['consumed']) : '—' }}</td>
                                                    <td class="pe-4 text-end fw-bold {{ $row['available'] <= 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ $row['available'] > 0 ? number_format($row['available']) : '—' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted small fst-italic">
                                                        No items assigned to this section.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <p class="text-muted small mb-0 fst-italic">
                        Note: A dash (—) indicates the item is out of stock or no data is available.
                    </p>
                </div>
            </main>
        </div>
    </div>
@endsection
