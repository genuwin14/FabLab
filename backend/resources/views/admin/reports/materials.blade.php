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
                    @include('admin.reports.components.tabs')

                    {{-- Filters & Actions --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3">
                            <form id="materialsFilterForm" method="GET" action="{{ route('admin.reports.materials') }}"
                                class="d-flex flex-nowrap align-items-center gap-2">
                                <div class="input-group flex-grow-1" style="min-width: 0;">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}"
                                        class="form-control border-start-0 rounded-end-2 ps-0"
                                        placeholder="Search by item name...">
                                </div>

                                <div class="input-group rounded-2 flex-shrink-0" style="width: auto;">
                                    <span class="input-group-text bg-white rounded-start-2"
                                        title="Filter by last update">
                                        <i class="bi bi-calendar-event text-muted"></i>
                                    </span>
                                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                                        class="form-control rounded-0" placeholder="From"
                                        onchange="document.getElementById('materialsFilterForm').submit()">
                                    <span class="input-group-text bg-white">to</span>
                                    <input type="date" name="date_to" value="{{ $dateTo }}"
                                        class="form-control rounded-end-2" placeholder="To"
                                        onchange="document.getElementById('materialsFilterForm').submit()">
                                </div>

                                <select name="group" class="form-select rounded-2 flex-shrink-0 w-auto"
                                    onchange="document.getElementById('materialsFilterForm').submit()">
                                    <option value="all" {{ $group === 'all' ? 'selected' : '' }}>All Item Types</option>
                                    <option value="products" {{ $group === 'products' ? 'selected' : '' }}>Products Only</option>
                                    <option value="raw_materials" {{ $group === 'raw_materials' ? 'selected' : '' }}>Raw Materials Only</option>
                                    <option value="textures" {{ $group === 'textures' ? 'selected' : '' }}>Textures Only</option>
                                </select>

                                <a href="{{ route('admin.reports.materials') }}"
                                    class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>

                                <a href="{{ route('admin.reports.materials.pdf', request()->query()) }}"
                                    class="btn btn-danger d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0"
                                    data-export-trigger
                                    data-format="PDF"
                                    data-scope-kind="All Material Sections"
                                    data-scope="Inventory Materials Report (All Departments)">
                                    <i class="bi bi-file-pdf"></i>
                                    <span class="small fw-bold">Export PDF</span>
                                </a>
                                <a href="{{ route('admin.reports.materials.docx', request()->query()) }}"
                                    class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0"
                                    data-export-trigger
                                    data-format="Word"
                                    data-scope-kind="All Material Sections"
                                    data-scope="Inventory Materials Report (All Departments)">
                                    <i class="bi bi-file-word"></i>
                                    <span class="small fw-bold">Export Word</span>
                                </a>
                            </form>

                            @if($dateFrom || $dateTo)
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Date range filters by item's last activity (updated_at).
                                    @if($dateFrom)<span class="ms-1">From <strong>{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('M j, Y') }}</strong></span>@endif
                                    @if($dateTo)<span class="ms-1">To <strong>{{ \Illuminate\Support\Carbon::parse($dateTo)->format('M j, Y') }}</strong></span>@endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @foreach($sections as $deptName => $rows)
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 paginated-section"
                            data-page-size="10">
                            <div class="card-header bg-white border-0 py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
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
                                    <div class="d-flex gap-2">
                                        @php
                                            $sectionParams = array_merge(request()->query(), ['department' => $deptName]);
                                        @endphp
                                        <a href="{{ route('admin.reports.materials.pdf', $sectionParams) }}"
                                            class="btn btn-sm btn-outline-danger d-flex align-items-center gap-2 rounded-2 px-3"
                                            title="Export this section to PDF"
                                            data-export-trigger
                                            data-format="PDF"
                                            data-scope-kind="Single Section"
                                            data-scope="{{ $deptName }} — Materials Report">
                                            <i class="bi bi-file-pdf"></i>
                                            <span class="small fw-bold">PDF</span>
                                        </a>
                                        <a href="{{ route('admin.reports.materials.docx', $sectionParams) }}"
                                            class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2 rounded-2 px-3"
                                            title="Export this section to Word"
                                            data-export-trigger
                                            data-format="Word"
                                            data-scope-kind="Single Section"
                                            data-scope="{{ $deptName }} — Materials Report">
                                            <i class="bi bi-file-word"></i>
                                            <span class="small fw-bold">Word</span>
                                        </a>
                                    </div>
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
                                                    <td class="ps-4 py-3">
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
                                                <tr class="empty-row">
                                                    <td colspan="8" class="text-center py-4 text-muted small fst-italic">
                                                        No items assigned to this section.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if(count($rows) > 0)
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top pagination-footer">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="text-muted small mb-0">Rows per page:</label>
                                            <select class="form-select form-select-sm rounded-pill w-auto page-size-select">
                                                @foreach([10, 25, 50, 100] as $size)
                                                    <option value="{{ $size }}" {{ $size === 10 ? 'selected' : '' }}>{{ $size }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-muted small entries-info">Showing 0 to 0 of 0 entries</span>
                                        </div>
                                        <nav>
                                            <ul class="pagination pagination-sm mb-0 page-controls"></ul>
                                        </nav>
                                    </div>
                                @endif
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

    @include('admin.reports.components.export-confirm-modal')

    <script>
        (function () {
            function paginate(section) {
                const tbody = section.querySelector('tbody');
                if (!tbody) return;
                const allRows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.classList.contains('empty-row'));
                if (allRows.length === 0) return;

                const footer = section.querySelector('.pagination-footer');
                if (!footer) return;

                const pageSizeSelect = footer.querySelector('.page-size-select');
                const entriesInfo = footer.querySelector('.entries-info');
                const pageControls = footer.querySelector('.page-controls');

                let currentPage = 1;
                let pageSize = parseInt(pageSizeSelect.value, 10) || 10;

                function render() {
                    const total = allRows.length;
                    const totalPages = Math.max(1, Math.ceil(total / pageSize));
                    if (currentPage > totalPages) currentPage = totalPages;

                    const start = (currentPage - 1) * pageSize;
                    const end = Math.min(start + pageSize, total);

                    allRows.forEach((row, idx) => {
                        row.style.display = (idx >= start && idx < end) ? '' : 'none';
                    });

                    entriesInfo.textContent = `Showing ${total === 0 ? 0 : start + 1} to ${end} of ${total} entries`;

                    pageControls.innerHTML = '';

                    const addItem = (label, page, opts = {}) => {
                        const li = document.createElement('li');
                        li.className = 'page-item' + (opts.disabled ? ' disabled' : '') + (opts.active ? ' active' : '');
                        const a = document.createElement('a');
                        a.className = 'page-link';
                        a.href = '#';
                        a.innerHTML = label;
                        a.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (opts.disabled || opts.active) return;
                            currentPage = page;
                            render();
                        });
                        li.appendChild(a);
                        pageControls.appendChild(li);
                    };

                    addItem('&laquo;', currentPage - 1, { disabled: currentPage === 1 });

                    const windowSize = 2;
                    const pages = new Set([1, totalPages]);
                    for (let p = currentPage - windowSize; p <= currentPage + windowSize; p++) {
                        if (p >= 1 && p <= totalPages) pages.add(p);
                    }
                    const sortedPages = Array.from(pages).sort((a, b) => a - b);
                    let prev = 0;
                    for (const p of sortedPages) {
                        if (p - prev > 1) {
                            const li = document.createElement('li');
                            li.className = 'page-item disabled';
                            li.innerHTML = '<span class="page-link">…</span>';
                            pageControls.appendChild(li);
                        }
                        addItem(String(p), p, { active: p === currentPage });
                        prev = p;
                    }

                    addItem('&raquo;', currentPage + 1, { disabled: currentPage === totalPages });
                }

                pageSizeSelect.addEventListener('change', () => {
                    pageSize = parseInt(pageSizeSelect.value, 10) || 10;
                    currentPage = 1;
                    render();
                });

                render();
            }

            document.querySelectorAll('.paginated-section').forEach(paginate);
        })();
    </script>
@endsection
