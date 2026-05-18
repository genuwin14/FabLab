@php
    use App\Enums\Department;
    use App\Models\Equipment;
    use App\Models\Product;
    use App\Models\RawMaterial;
    use App\Models\Texture;

    $isMaterialsPage = request()->routeIs('admin.reports.materials');
    $isEquipmentPage = request()->routeIs('admin.reports.equipment');

    $reqDateFrom = trim((string) request()->query('date_from', ''));
    $reqDateTo = trim((string) request()->query('date_to', ''));
    $reqSearch = trim((string) request()->query('search', ''));

    $summaryCards = [];

    if ($isMaterialsPage) {
        $reqGroup = request()->query('group', 'all');

        $applyMaterialsFilters = function ($query) use ($reqDateFrom, $reqDateTo, $reqSearch) {
            if ($reqDateFrom !== '') {
                $query->whereDate('updated_at', '>=', $reqDateFrom);
            }
            if ($reqDateTo !== '') {
                $query->whereDate('updated_at', '<=', $reqDateTo);
            }
            if ($reqSearch !== '') {
                $query->where('name', 'like', '%' . $reqSearch . '%');
            }
            return $query;
        };

        $countForDept = function ($dept) use ($applyMaterialsFilters, $reqGroup) {
            $total = 0;
            if ($reqGroup === 'all' || $reqGroup === 'products') {
                $q = Product::query();
                $applyMaterialsFilters($q);
                $total += $dept === null ? $q->whereNull('department')->count() : $q->where('department', $dept)->count();
            }
            if ($reqGroup === 'all' || $reqGroup === 'raw_materials') {
                $q = RawMaterial::query();
                $applyMaterialsFilters($q);
                $total += $dept === null ? $q->whereNull('department')->count() : $q->where('department', $dept)->count();
            }
            if ($reqGroup === 'all' || $reqGroup === 'textures') {
                $q = Texture::query();
                $applyMaterialsFilters($q);
                $total += $dept === null ? $q->whereNull('department')->count() : $q->where('department', $dept)->count();
            }
            return $total;
        };

        $deptMeta = [
            'Digital Customization Center' => ['icon' => 'bi-pc-display', 'color' => '#0d6efd'],
            'Book Production' => ['icon' => 'bi-book', 'color' => '#fd7e14'],
            'Woodworks' => ['icon' => 'bi-tree', 'color' => '#198754'],
            'Uncategorized' => ['icon' => 'bi-question-circle', 'color' => '#6c757d'],
        ];

        foreach (Department::values() as $dept) {
            $summaryCards[] = [
                'label' => $dept,
                'count' => $countForDept($dept),
                'icon' => $deptMeta[$dept]['icon'] ?? 'bi-collection',
                'color' => $deptMeta[$dept]['color'] ?? '#6c757d',
            ];
        }
        $summaryCards[] = [
            'label' => 'Uncategorized',
            'count' => $countForDept(null),
            'icon' => $deptMeta['Uncategorized']['icon'],
            'color' => $deptMeta['Uncategorized']['color'],
        ];
    }

    if ($isEquipmentPage) {
        // Status counts respect date/search filters but ignore the status filter
        // so the summary cards always show the full breakdown.
        $equipmentBaseQuery = Equipment::query();
        if ($reqDateFrom !== '') {
            $equipmentBaseQuery->whereDate('date_acquired', '>=', $reqDateFrom);
        }
        if ($reqDateTo !== '') {
            $equipmentBaseQuery->whereDate('date_acquired', '<=', $reqDateTo);
        }
        if ($reqSearch !== '') {
            $equipmentBaseQuery->where(function ($q) use ($reqSearch) {
                $q->where('name', 'like', '%' . $reqSearch . '%')
                  ->orWhere('brand', 'like', '%' . $reqSearch . '%')
                  ->orWhere('property_no', 'like', '%' . $reqSearch . '%');
            });
        }
        $statusCounts = (clone $equipmentBaseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusMeta = [
            'Serviceable' => ['icon' => 'bi-check-circle', 'color' => '#0c6c3a', 'short' => 'Serviceable'],
            'Functional' => ['icon' => 'bi-gear', 'color' => '#0d6efd', 'short' => 'Functional'],
            'Non-Serviceable' => ['icon' => 'bi-x-circle', 'color' => '#a02633', 'short' => 'Non-Serviceable'],
            'Returned to supplier for repair' => ['icon' => 'bi-arrow-return-left', 'color' => '#7a5b00', 'short' => 'Returned for Repair'],
        ];

        foreach ($statusMeta as $statusKey => $meta) {
            $summaryCards[] = [
                'label' => $meta['short'],
                'count' => $statusCounts[$statusKey] ?? 0,
                'icon' => $meta['icon'],
                'color' => $meta['color'],
            ];
        }
    }
@endphp

<div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">Reports</h4>
        <p class="text-muted small mb-0">As of {{ $asOfDate->format('F j, Y') }}</p>
    </div>

    @if(count($summaryCards) > 0)
        <div class="report-summary-divider align-self-stretch"></div>
        <div class="row g-2 flex-grow-1 report-summary-row">
            @foreach($summaryCards as $card)
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100 report-summary-card"
                         style="border-left: 3px solid {{ $card['color'] }} !important;">
                        <div class="card-body p-2 d-flex align-items-center gap-2">
                            <div class="report-summary-icon flex-shrink-0"
                                 style="background-color: {{ $card['color'] }}1a; color: {{ $card['color'] }};">
                                <i class="bi {{ $card['icon'] }}"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <p class="text-muted mb-0 text-uppercase fw-semibold text-truncate"
                                   style="letter-spacing: 0.04em; font-size: 0.65rem;" title="{{ $card['label'] }}">
                                    {{ $card['label'] }}
                                </p>
                                <h5 class="fw-bold text-dark mb-0">{{ number_format($card['count']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1"
              style="background-color: rgba(255, 197, 8, 0.12); color: #0e2e45; font-size: 0.85rem; font-weight: 600;">
            <i class="bi {{ request()->routeIs('admin.reports.materials') ? 'bi-clipboard-data' : 'bi-tools' }}"></i>
            {{ request()->routeIs('admin.reports.materials') ? 'Materials' : 'Machinery & Equipment' }}
        </span>
    </div>
</div>

@if($isMaterialsPage)
    @php
        $materialsTabs = [
            ['key' => 'digital-customization-center', 'label' => 'PEDS Digital Customization Center', 'icon' => 'bi-pc-display'],
            ['key' => 'book-production',              'label' => 'PEDS Book Production',              'icon' => 'bi-book'],
            ['key' => 'woodworks',                    'label' => 'PEDS Woodworks',                    'icon' => 'bi-tree'],
            ['key' => 'uncategorized',                'label' => 'Uncategorized',                     'icon' => 'bi-question-circle'],
        ];
    @endphp
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3 report-section-tabs-row">
        <ul class="nav nav-tabs report-section-tabs mb-0 flex-grow-1" id="materialsSectionTabs" role="tablist">
            @foreach($materialsTabs as $i => $tab)
                <li class="nav-item" role="presentation">
                    <button type="button"
                        class="nav-link {{ $i === 0 ? 'active' : '' }} d-flex align-items-center gap-2"
                        id="tab-{{ $tab['key'] }}"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-{{ $tab['key'] }}"
                        role="tab"
                        aria-controls="pane-{{ $tab['key'] }}"
                        aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                        <i class="bi {{ $tab['icon'] }}"></i>
                        <span>{{ $tab['label'] }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="d-flex align-items-stretch gap-2 flex-shrink-0 report-section-tabs-actions mb-2">
            <span class="report-section-tabs-divider"></span>
            <a href="{{ route('admin.reports.materials.pdf', request()->query()) }}"
                class="btn btn-danger d-flex align-items-center gap-2 rounded-2 px-3"
                data-bs-toggle="tooltip" data-bs-placement="bottom"
                title="Generate a PDF report of all four department sections combined"
                data-export-trigger
                data-format="PDF"
                data-scope-kind="All Material Sections"
                data-scope="Inventory Materials Report (All Departments)"
                data-preview-url="{{ route('admin.reports.materials.preview', request()->query()) }}">
                <i class="bi bi-file-pdf"></i>
                <span class="small fw-bold d-none d-lg-inline">Export All Sections (PDF)</span>
            </a>
            <a href="{{ route('admin.reports.materials.docx', request()->query()) }}"
                class="btn btn-primary d-flex align-items-center gap-2 rounded-2 px-3"
                data-bs-toggle="tooltip" data-bs-placement="bottom"
                title="Generate a Word document of all four department sections combined"
                data-export-trigger
                data-format="Word"
                data-scope-kind="All Material Sections"
                data-scope="Inventory Materials Report (All Departments)"
                data-preview-url="{{ route('admin.reports.materials.preview', request()->query()) }}">
                <i class="bi bi-file-word"></i>
                <span class="small fw-bold d-none d-lg-inline">Export All Sections (Word)</span>
            </a>
        </div>
    </div>
@endif

<style>
    .report-summary-divider {
        width: 1px;
        background-color: rgba(0, 0, 0, 0.1);
        margin: 0.25rem 0.25rem;
        flex-shrink: 0;
    }

    .report-summary-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .report-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    .report-summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .report-section-tabs {
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        gap: 0.25rem;
        flex-wrap: wrap;
    }
    .report-section-tabs .nav-link {
        border: 0;
        border-radius: 10px 10px 0 0;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 0.55rem 1rem;
        background: transparent;
        transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
    }
    .report-section-tabs .nav-link:hover {
        color: #0e2e45;
        background: rgba(255, 197, 8, 0.08);
    }
    .report-section-tabs .nav-link.active {
        color: #0e2e45;
        background: #fff;
        border-bottom: 3px solid #ffc508;
        box-shadow: 0 -1px 0 rgba(0, 0, 0, 0.04) inset;
    }
    .report-section-tabs .nav-link i { font-size: 0.95rem; }

    .report-section-tabs-divider {
        width: 1px;
        background-color: rgba(0, 0, 0, 0.12);
        align-self: stretch;
        margin: 0 0.25rem;
        flex-shrink: 0;
    }

    /* Desktop: search/filter dropdowns render inline & static (note §4). */
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

    /* ── Mobile / sidebar-squeezed tablet (< lg / 992px) ───────
       Canonical breakpoint (see ResponsiveMobileNote.md §1): the fixed
       280px sidebar appears at md, so tablets need the mobile treatment
       too — hence lg, not md. ──────────────────────────────── */
    @media (max-width: 991.98px) {
        /* In-card report tables have long headers ("No. of Units on
           Display") — keep columns from wrapping into tall rows; let
           .table-responsive scroll instead (§5c, no edge-to-edge). */
        .paginated-section .table {
            min-width: 760px;
        }
        .paginated-section .table th,
        .paginated-section .table td {
            white-space: nowrap;
        }
        /* Header: tighter spacing + smaller title */
        .container-fluid > .border-bottom h4 { font-size: 1.05rem; }
        .container-fluid > .border-bottom p { font-size: 0.72rem; }

        /* Hide vertical divider between title and summary cards on mobile */
        .report-summary-divider { display: none; }

        /* Summary cards: tighter padding, smaller numbers */
        .report-summary-card .card-body { padding: 0.5rem !important; gap: 0.4rem !important; }
        .report-summary-icon { width: 30px; height: 30px; font-size: 0.85rem; }
        .report-summary-card h5 { font-size: 0.95rem; }
        .report-summary-card p { font-size: 0.58rem !important; }

        /* Materials tab nav: horizontal scroll, compact labels */
        .report-section-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        .report-section-tabs::-webkit-scrollbar { height: 4px; }
        .report-section-tabs::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 999px; }
        .report-section-tabs .nav-item { flex-shrink: 0; }
        .report-section-tabs .nav-link {
            font-size: 0.72rem;
            padding: 0.45rem 0.7rem;
            white-space: nowrap;
        }
        .report-section-tabs .nav-link i { font-size: 0.85rem; }

        /* Tabs row: stack tabs above export buttons, full-width buttons */
        .report-section-tabs-row { flex-direction: column; align-items: stretch !important; }
        .report-section-tabs-actions {
            width: 100%;
            margin-bottom: 0 !important;
            margin-top: 0.25rem;
        }
        .report-section-tabs-actions .btn { padding: 0.5rem 0.75rem; }
        .report-section-tabs-actions .btn .small { font-size: 0.7rem; }
        .report-section-tabs-divider { display: none; }

        /* Search / filter collapse into icon dropdowns (note §4). */
        .search-dd .dropdown-menu.search-menu { width: min(86vw, 380px); }
        .filter-dd .dropdown-menu.filter-menu { width: min(92vw, 420px); }
        .filter-dd .filter-menu .form-select,
        .filter-dd .filter-menu .input-group { font-size: 0.85rem; }

        /* Tables: tighter padding + smaller text */
        .table-responsive table th,
        .table-responsive table td { padding-top: 0.55rem; padding-bottom: 0.55rem; font-size: 0.78rem; }
        .table-responsive table th.ps-4,
        .table-responsive table td.ps-4 { padding-left: 0.85rem !important; }
        .table-responsive table th.pe-4,
        .table-responsive table td.pe-4 { padding-right: 0.85rem !important; }

        /* Per-section card header on materials page */
        .paginated-section .card-header h6 { font-size: 0.78rem; }
        .paginated-section .card-header .btn { padding: 0.35rem 0.65rem; }
        .paginated-section .card-header .btn .small { font-size: 0.7rem; }

        /* Pagination footer */
        .pagination-footer .entries-info { font-size: 0.72rem; }
        .pagination-footer label { font-size: 0.72rem; }
    }

    /* ── Extra-small (< sm / 576px) ────────────────────────── */
    @media (max-width: 575.98px) {
        /* Title + badge wrap onto separate rows cleanly */
        .container-fluid > .border-bottom { gap: 0.5rem !important; }

        /* Summary cards keep 2-up but slightly looser */
        .report-summary-card p { font-size: 0.55rem !important; letter-spacing: 0.02em !important; }

        /* Per-section card header: stack title and buttons */
        .paginated-section .card-header > .d-flex { gap: 0.5rem !important; }
    }
</style>
