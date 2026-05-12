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
</style>
