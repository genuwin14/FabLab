@extends('layout.app')

{{--
    The review step between reading an old report and writing it.

    Every parsed row is shown, including the ones that will do nothing, because
    the useful question here is not "did it work" but "does this report agree
    with what we hold". A row that matched and changes nothing is evidence; a
    row that matched nothing is a decision for someone to make.
--}}

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

            <main class="flex-grow-1 p-3 p-md-4" style="overflow-y: auto;">
                <div class="container-fluid">

                    @php
                        // The report's column headings, and a short form for the
                        // change chips where the full heading would not fit.
                        $labels = [
                            'on_display' => 'No. of Units on Display',
                            'sponsored' => 'No. of Sponsored Units',
                            'damaged' => 'No. of Damaged Units',
                            'consumed' => 'No. of Units Consumed',
                            'available' => 'Available Units for Production',
                        ];
                        $shortLabels = [
                            'on_display' => 'Display',
                            'sponsored' => 'Sponsored',
                            'damaged' => 'Damaged',
                            'consumed' => 'Consumed',
                            'available' => 'Available',
                        ];

                        // Quantities are held to two decimals but are usually
                        // whole, so trim the zeros rather than print "1,008.00".
                        $fmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
                    @endphp

                    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3 pb-2 border-bottom">
                        <div>
                            <h4 class="fw-bold text-dark mb-0">Review Import</h4>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-file-earmark-word me-1"></i>{{ $filename }}
                            </p>
                        </div>
                        <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1"
                            style="background-color: rgba(255, 197, 8, 0.12); color: #0e2e45; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-clipboard-data"></i>Materials
                        </span>
                    </div>

                    @php
                        $cards = [
                            ['label' => 'Will Update', 'count' => $summary['update'], 'icon' => 'bi-pencil-square', 'color' => '#0d6efd'],
                            ['label' => 'Already Match', 'count' => $summary['unchanged'], 'icon' => 'bi-check-circle', 'color' => '#198754'],
                            ['label' => 'Not Found', 'count' => $summary['unmatched'], 'icon' => 'bi-question-circle', 'color' => '#997404'],
                            ['label' => 'Ambiguous', 'count' => $summary['ambiguous'], 'icon' => 'bi-exclamation-triangle', 'color' => '#dc3545'],
                        ];
                    @endphp
                    <div class="row g-2 mb-4">
                        @foreach($cards as $card)
                            <div class="col-6 col-md-3">
                                <div class="card border-0 shadow-sm rounded-3 h-100"
                                    style="border-left: 3px solid {{ $card['color'] }} !important;">
                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                        <div class="import-summary-icon flex-shrink-0"
                                            style="background-color: {{ $card['color'] }}1a; color: {{ $card['color'] }};">
                                            <i class="bi {{ $card['icon'] }}"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <p class="text-muted mb-0 text-uppercase fw-semibold text-truncate"
                                                style="letter-spacing: 0.04em; font-size: 0.65rem;">{{ $card['label'] }}</p>
                                            <h5 class="fw-bold text-dark mb-0">{{ number_format($card['count']) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($warnings))
                        <div class="alert border-0 shadow-sm rounded-3 d-flex gap-2 mb-4"
                            style="background-color: rgba(255, 193, 7, 0.12); color: #6c5200;">
                            <i class="bi bi-exclamation-triangle flex-shrink-0"></i>
                            <div class="small">
                                <strong>Some rows could not be read in full.</strong>
                                <ul class="mb-0 mt-1 ps-3">
                                    @foreach($warnings as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="fw-bold text-dark mb-0 text-uppercase" style="letter-spacing: 0.04em;">
                                <i class="bi bi-list-check text-warning me-2"></i>Parsed Rows
                            </h6>
                            <span class="badge bg-light text-muted rounded-pill">
                                {{ $summary['total'] }} {{ $summary['total'] === 1 ? 'row' : 'rows' }} read
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 import-preview-table">
                                <thead class="bg-light bg-opacity-50">
                                    <tr>
                                        <th class="ps-4 border-0 small text-uppercase text-muted" style="width: 42px;">
                                            @if($summary['unmatched'] > 0)
                                                <input type="checkbox" class="form-check-input" id="createAll" checked
                                                    title="Create all the items that were not found">
                                            @endif
                                        </th>
                                        <th class="border-0 small text-uppercase text-muted">Item</th>
                                        <th class="border-0 small text-uppercase text-muted">Matched</th>
                                        <th class="border-0 small text-uppercase text-muted">Department</th>
                                        <th class="border-0 small text-uppercase text-muted">Changes</th>
                                        <th class="pe-4 border-0 small text-uppercase text-muted text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        @php
                                            [$badgeBg, $badgeColor, $badgeIcon, $badgeLabel] = match ($item['status']) {
                                                'update' => ['rgba(13, 110, 253, 0.12)', '#0d6efd', 'bi-pencil-square', 'Will update'],
                                                'unchanged' => ['rgba(25, 135, 84, 0.12)', '#198754', 'bi-check-circle', 'Already matches'],
                                                'unmatched' => ['rgba(255, 193, 7, 0.18)', '#997404', 'bi-question-circle', 'Not found'],
                                                default => ['rgba(220, 53, 69, 0.12)', '#dc3545', 'bi-exclamation-triangle', 'Ambiguous'],
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-4 py-3">
                                                @if($item['status'] === 'unmatched')
                                                    {{-- Named for the confirm form, which lives further down the
                                                         page; the form attribute is what lets it sit in this table. --}}
                                                    <input type="checkbox" class="form-check-input create-row"
                                                        form="importConfirmForm" name="create[]"
                                                        value="{{ $item['index'] }}" checked
                                                        aria-label="Create {{ $item['name'] }}">
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                <div class="fw-bold text-dark small">{{ $item['name'] }}</div>
                                                @if($item['unit'])
                                                    <div class="text-muted" style="font-size: 0.7rem;">
                                                        measured in {{ $item['unit'] }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($item['type'])
                                                    <span class="badge rounded-2 px-2 py-1 fw-semibold"
                                                        style="background-color: rgba(14, 46, 69, 0.08); color: #0e2e45; font-size: 0.7rem;">
                                                        {{ $item['type'] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ $item['department'] ?? '—' }}</td>
                                            <td class="small">
                                                @if($item['changes'])
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($item['changes'] as $field => $change)
                                                            <span class="import-change" title="{{ $labels[$field] ?? $field }}">
                                                                <span class="import-change-label">{{ $shortLabels[$field] ?? $field }}</span>
                                                                <span class="import-change-from">{{ $fmt($change['from']) }}</span>
                                                                <i class="bi bi-arrow-right"></i>
                                                                <span class="import-change-to">{{ $fmt($change['to']) }}</span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @elseif($item['status'] === 'update' || $item['status'] === 'unchanged')
                                                    <span class="text-muted">No change</span>
                                                @else
                                                    {{-- Nothing to compare against, so show what the report itself
                                                         said. Without this a row that matched nothing reads as
                                                         "No change" and hides the very figures being asked about. --}}
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($shortLabels as $field => $short)
                                                            <span class="import-change import-change-reported"
                                                                title="{{ $labels[$field] ?? $field }} — as printed in the report">
                                                                <span class="import-change-label">{{ $short }}</span>
                                                                <span class="import-change-reported-value">{{ $fmt($item['values'][$field] ?? 0) }}</span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @foreach($item['notes'] as $note)
                                                    <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                                        <i class="bi bi-info-circle me-1"></i>{{ $note }}
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="pe-4 text-end">
                                                <span class="badge rounded-2 px-2 py-1 d-inline-flex align-items-center gap-1 fw-semibold"
                                                    style="background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; font-size: 0.7rem;">
                                                    <i class="bi {{ $badgeIcon }}"></i>{{ $badgeLabel }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox display-6 d-block mb-3 opacity-50"></i>
                                                No rows were read from that report.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- The row checkboxes above post into this form by id, so the
                         decision and the button that acts on it stay one submit. --}}
                    <form method="POST" action="{{ route('admin.reports.materials.import.confirm') }}"
                        id="importConfirmForm">
                        @csrf

                        @if($summary['unmatched'] > 0)
                            <div class="card border-0 shadow-sm rounded-4 mb-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-dark mb-1">
                                        <i class="bi bi-plus-circle text-warning me-2"></i>Create the items that were not found
                                    </h6>
                                    <p class="text-muted small mb-3">
                                        {{ $summary['unmatched'] }} of these rows name inventory this system has never
                                        held. Ticked rows are created as raw materials, with the units and figures the
                                        report gives them. Untick any you would rather leave out.
                                    </p>

                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-md-5">
                                            <label for="supplierId" class="form-label fw-semibold text-uppercase text-muted"
                                                style="letter-spacing: 0.04em; font-size: 0.7rem;">
                                                File them under supplier
                                            </label>
                                            <select name="supplier_id" id="supplierId" class="form-select rounded-2">
                                                <option value="">Choose a supplier…</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->supplier_id }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <div class="d-flex gap-2 p-3 rounded-3"
                                                style="background-color: rgba(255, 193, 7, 0.10);">
                                                <i class="bi bi-info-circle flex-shrink-0" style="color: #997404;"></i>
                                                <div class="small" style="color: #6c5200;">
                                                    A report carries no price or supplier, so new items start at a cost
                                                    of <strong>0.00</strong> under the supplier chosen here — both
                                                    editable per item afterwards. Anything that is really a sellable
                                                    product needs a SKU, price and category, so add those on the
                                                    Products screen rather than here.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="small text-muted">
                                    @if($summary['update'] > 0 && $summary['unmatched'] > 0)
                                        <i class="bi bi-info-circle me-1"></i>
                                        Applying updates {{ $summary['update'] }}
                                        {{ $summary['update'] === 1 ? 'item' : 'items' }} and creates the ticked ones.
                                    @elseif($summary['update'] > 0)
                                        <i class="bi bi-info-circle me-1"></i>
                                        Applying writes {{ $summary['update'] }}
                                        {{ $summary['update'] === 1 ? 'item' : 'items' }}.
                                    @elseif($summary['unmatched'] > 0)
                                        <i class="bi bi-info-circle me-1"></i>
                                        Nothing here matches an existing item, so applying creates the ticked rows.
                                    @elseif($summary['unchanged'] === $summary['total'])
                                        <i class="bi bi-check-circle me-1 text-success"></i>
                                        Every item in this report already matches what is held. Nothing to apply.
                                    @else
                                        <i class="bi bi-exclamation-triangle me-1" style="color: #997404;"></i>
                                        There is nothing in this report that can be applied.
                                    @endif
                                    <span class="d-block mt-1">
                                        Raw materials are written through the usage ledger, so every figure stays
                                        visible in the Usage Log.
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" form="importDiscardForm"
                                        class="btn btn-light border fw-semibold rounded-2 px-4">
                                        Discard
                                    </button>
                                    <button type="submit" class="btn fw-semibold rounded-2 px-4 import-apply-btn"
                                        {{ $summary['update'] === 0 && $summary['unmatched'] === 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-check2-circle me-2"></i>Apply to Inventory
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Separate, so Discard never carries the create selection. --}}
                    <form method="POST" action="{{ route('admin.reports.materials.import.discard') }}"
                        id="importDiscardForm" class="d-none">
                        @csrf
                    </form>

                </div>
            </main>
        </div>
    </div>

    <style>
        .import-summary-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .import-change {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background-color: #f1f4f8;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 0.15rem 0.45rem;
            font-size: 0.7rem;
            white-space: nowrap;
        }
        .import-change-label {
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 700;
            color: #6c757d;
            font-size: 0.62rem;
        }
        .import-change-from { color: #6c757d; text-decoration: line-through; }
        .import-change-to { color: #0e2e45; font-weight: 700; }
        .import-change i { font-size: 0.6rem; color: #adb5bd; }

        /* A figure read off the report with nothing to compare it to. Dashed,
           so it does not read as a change that is going to be written. */
        .import-change-reported {
            background-color: #fff;
            border-style: dashed;
        }
        .import-change-reported-value { color: #6c757d; font-weight: 700; }

        .import-apply-btn {
            background-color: #0e2e45;
            border: 1px solid #0e2e45;
            color: #fff;
            transition: all 0.2s ease;
        }
        .import-apply-btn:hover:not(:disabled) {
            background-color: #ffc508;
            border-color: #ffc508;
            color: #0e2e45;
        }
        .import-apply-btn:disabled { opacity: 0.5; }

        @media (max-width: 991.98px) {
            .import-preview-table { min-width: 860px; }
            .import-preview-table th,
            .import-preview-table td { white-space: nowrap; }
        }
    </style>

    <script>
        (function () {
            const all = document.getElementById('createAll');
            const rows = Array.from(document.querySelectorAll('.create-row'));

            if (!all || rows.length === 0) return;

            all.addEventListener('change', () => {
                rows.forEach(row => { row.checked = all.checked; });
            });

            // Reflect the rows back into the header box, so it never claims
            // "all" while some are unticked.
            rows.forEach(row => row.addEventListener('change', () => {
                const ticked = rows.filter(r => r.checked).length;
                all.checked = ticked === rows.length;
                all.indeterminate = ticked > 0 && ticked < rows.length;
            }));

            // A supplier is only needed if something is actually being created.
            const form = document.getElementById('importConfirmForm');
            const supplier = document.getElementById('supplierId');

            if (form && supplier) {
                form.addEventListener('submit', event => {
                    const creating = rows.some(r => r.checked);
                    supplier.required = creating;

                    if (creating && !supplier.value) {
                        event.preventDefault();
                        supplier.focus();
                        supplier.reportValidity();
                    }
                });
            }
        })();
    </script>
@endsection
