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
                            <form id="equipmentFilterForm" method="GET" action="{{ route('admin.reports.equipment') }}"
                                class="d-flex flex-nowrap align-items-center gap-2">
                                <div class="input-group flex-grow-1" style="min-width: 0;">
                                    <span class="input-group-text bg-white border-end-0 rounded-start-2 ps-3">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" value="{{ $search }}"
                                        class="form-control border-start-0 rounded-end-2 ps-0"
                                        placeholder="Search by name, brand, or property no...">
                                </div>

                                <div class="input-group rounded-2 flex-shrink-0" style="width: auto;">
                                    <span class="input-group-text bg-white rounded-start-2"
                                        title="Filter by date acquired">
                                        <i class="bi bi-calendar-event text-muted"></i>
                                    </span>
                                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                                        class="form-control rounded-0"
                                        onchange="document.getElementById('equipmentFilterForm').submit()">
                                    <span class="input-group-text bg-white">to</span>
                                    <input type="date" name="date_to" value="{{ $dateTo }}"
                                        class="form-control rounded-end-2"
                                        onchange="document.getElementById('equipmentFilterForm').submit()">
                                </div>

                                <select name="status" class="form-select rounded-2 flex-shrink-0 w-auto"
                                    onchange="document.getElementById('equipmentFilterForm').submit()">
                                    <option value="">All Statuses</option>
                                    <option value="Serviceable" {{ $status === 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                                    <option value="Non-Serviceable" {{ $status === 'Non-Serviceable' ? 'selected' : '' }}>Non-Serviceable</option>
                                    <option value="Functional" {{ $status === 'Functional' ? 'selected' : '' }}>Functional</option>
                                    <option value="Returned to supplier for repair" {{ $status === 'Returned to supplier for repair' ? 'selected' : '' }}>Returned for Repair</option>
                                </select>

                                <a href="{{ route('admin.reports.equipment') }}"
                                    class="btn btn-light rounded-2 flex-shrink-0" data-bs-toggle="tooltip" title="Reset filters">
                                    <i class="bi bi-arrow-clockwise text-primary"></i>
                                </a>

                                <a href="{{ route('admin.reports.equipment.pdf', request()->query()) }}"
                                    class="btn btn-danger d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0"
                                    data-export-trigger
                                    data-format="PDF"
                                    data-scope-kind="Equipment Report"
                                    data-scope="Machinery & Equipment Report"
                                    data-preview-url="{{ route('admin.reports.equipment.preview', request()->query()) }}">
                                    <i class="bi bi-file-pdf"></i>
                                    <span class="small fw-bold">Export PDF</span>
                                </a>
                                <a href="{{ route('admin.reports.equipment.docx', request()->query()) }}"
                                    class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3 flex-shrink-0"
                                    data-export-trigger
                                    data-format="Word"
                                    data-scope-kind="Equipment Report"
                                    data-scope="Machinery & Equipment Report"
                                    data-preview-url="{{ route('admin.reports.equipment.preview', request()->query()) }}">
                                    <i class="bi bi-file-word"></i>
                                    <span class="small fw-bold">Export Word</span>
                                </a>
                            </form>

                            @if($dateFrom || $dateTo)
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Date range filters by date acquired.
                                    @if($dateFrom)<span class="ms-1">From <strong>{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('M j, Y') }}</strong></span>@endif
                                    @if($dateTo)<span class="ms-1">To <strong>{{ \Illuminate\Support\Carbon::parse($dateTo)->format('M j, Y') }}</strong></span>@endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden paginated-section" data-page-size="10">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th class="ps-4 py-3 small fw-bold border-0">Equipment</th>
                                            <th class="py-3 small fw-bold border-0">Brand</th>
                                            <th class="py-3 small fw-bold border-0">Property No.</th>
                                            <th class="py-3 small fw-bold border-0">Date Acquired</th>
                                            <th class="py-3 small fw-bold border-0">Cost</th>
                                            <th class="pe-4 py-3 small fw-bold border-0">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($rows as $row)
                                            <tr>
                                                <td class="ps-4 py-3 fw-semibold text-dark">{{ $row['name'] }}</td>
                                                <td class="text-muted small">{{ $row['brand'] ?: '—' }}</td>
                                                <td class="font-monospace small">{{ $row['property_no'] ?: '—' }}</td>
                                                <td class="text-muted small">{{ $row['date_acquired'] ? $row['date_acquired']->format('M j, Y') : '—' }}</td>
                                                <td class="fw-bold text-dark">₱{{ number_format($row['cost'], 2) }}</td>
                                                <td class="pe-4">
                                                    @php
                                                        $statusColors = [
                                                            'Serviceable' => ['bg' => '#d1f3df', 'fg' => '#0c6c3a'],
                                                            'Functional' => ['bg' => '#d1f3df', 'fg' => '#0c6c3a'],
                                                            'Non-Serviceable' => ['bg' => '#fbe1e3', 'fg' => '#a02633'],
                                                            'Returned to supplier for repair' => ['bg' => '#fff3cd', 'fg' => '#7a5b00'],
                                                        ];
                                                        $colors = $statusColors[$row['status']] ?? ['bg' => '#e9ecef', 'fg' => '#495057'];
                                                    @endphp
                                                    <span class="badge rounded-pill px-3 py-2"
                                                        style="background-color: {{ $colors['bg'] }}; color: {{ $colors['fg'] }};">
                                                        {{ $row['status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="empty-row">
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="bi bi-tools fs-1 opacity-25 d-block mb-2"></i>
                                                    No equipment found.
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
