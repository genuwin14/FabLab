{{--
    The stock ledger. Caller passes $routePrefix, $movements, $materialOptions.

    Rows are never edited — a mistake is undone with Reverse, which writes an
    opposite entry — so the history reads as what actually happened.
--}}
@php
    use App\Enums\StockMovementReason;

    $canCorrect = $routePrefix === 'admin';

    // The filter lists everything the log can *contain*, which is wider than
    // what a person can pick in the Record Usage form: reservations are written
    // by order approvals and reversals by the Reverse button, but both show up
    // here and are worth being able to narrow to.
    $logReasons = array_merge(
        StockMovementReason::selectable($canCorrect),
        [StockMovementReason::Reserved, StockMovementReason::Reversal],
    );
@endphp

<!-- Log filters -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route($routePrefix . '.raw-materials.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="log">
            <input type="hidden" name="log_per_page" value="{{ request()->query('log_per_page', 10) }}">

            <div class="col-12 col-sm-auto flex-sm-grow-1">
                <select name="log_material" class="form-select form-select-sm rounded-2">
                    <option value="">All materials</option>
                    @foreach($materialOptions as $option)
                        <option value="{{ $option->raw_material_id }}"
                            {{ (string) request()->query('log_material') === (string) $option->raw_material_id ? 'selected' : '' }}>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-sm-auto flex-sm-grow-1">
                <select name="log_reason" class="form-select form-select-sm rounded-2">
                    <option value="">All reasons</option>
                    @foreach($logReasons as $reason)
                        <option value="{{ $reason->value }}"
                            {{ request()->query('log_reason') === $reason->value ? 'selected' : '' }}>
                            {{ $reason->shortLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3">
                    <i class="bi bi-funnel me-1"></i><span class="fw-bold">Filter</span>
                </button>
                <a href="{{ route($routePrefix . '.raw-materials.index', ['tab' => 'log']) }}"
                    class="btn btn-light btn-sm rounded-2" data-bs-toggle="tooltip" title="Clear log filters">
                    <i class="bi bi-arrow-clockwise text-primary"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Ledger -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden data-table-card usage-log-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="bg-primary bg-opacity-10">
                        <th class="ps-4 py-3 text-primary small text-uppercase fw-bold border-0">When</th>
                        <th class="py-3 text-primary small text-uppercase fw-bold border-0">Material</th>
                        <th class="py-3 text-primary small text-uppercase fw-bold border-0">Reason</th>
                        <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">Quantity</th>
                        <th class="py-3 text-primary small text-uppercase fw-bold border-0 text-end">Stock After</th>
                        <th class="py-3 text-primary small text-uppercase fw-bold border-0">Recorded By</th>
                        <th class="text-end pe-4 py-3 text-primary small text-uppercase fw-bold border-0">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($movements as $movement)
                        @php
                            $reason = $movement->reason;
                            $delta = (float) $movement->stock_delta;
                            $isReversed = $movement->reversal !== null;
                        @endphp
                        <tr class="{{ $isReversed ? 'usage-row-reversed' : '' }}">
                            <td class="ps-4 py-3">
                                <span class="fw-semibold text-dark d-block" style="font-size: 0.82rem;">
                                    {{ $movement->created_at->format('M j, Y') }}
                                </span>
                                <span class="text-muted" style="font-size: 0.72rem;">
                                    {{ $movement->created_at->format('g:i A') }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $movement->rawMaterial?->name ?? '—' }}</span>
                                @if($movement->note)
                                    <span class="d-block text-muted text-truncate" style="font-size: 0.72rem; max-width: 260px;"
                                        title="{{ $movement->note }}">{{ $movement->note }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="usage-reason-badge"
                                    style="background-color: {{ $reason->color() }}1a; color: {{ $reason->color() }};">
                                    <i class="bi {{ $reason->icon() }}"></i>{{ $reason->shortLabel() }}
                                </span>
                                @if($isReversed)
                                    <span class="badge bg-light text-muted border rounded-pill ms-1"
                                        style="font-size: 0.62rem;">Reversed</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ $delta < 0 ? 'text-danger' : ($delta > 0 ? 'text-success' : 'text-muted') }}">
                                @if($delta == 0)
                                    {{-- Display units never leave stock, so there is no delta to sign. --}}
                                    <span title="Tagged as on display; stock unchanged">
                                        {{ rtrim(rtrim(number_format((float) $movement->quantity, 2), '0'), '.') }}
                                        <i class="bi bi-dash-circle ms-1 opacity-50"></i>
                                    </span>
                                @else
                                    {{ $delta < 0 ? '−' : '+' }}{{ rtrim(rtrim(number_format(abs($delta), 2), '0'), '.') }}
                                @endif
                                <span class="text-muted fw-normal text-lowercase" style="font-size: 0.72rem;">
                                    {{ $movement->rawMaterial?->unit }}
                                </span>
                            </td>
                            <td class="text-end text-dark fw-semibold">
                                {{ rtrim(rtrim(number_format((float) $movement->stock_after, 2), '0'), '.') }}
                            </td>
                            <td class="text-muted small">
                                {{ $movement->actorName() }}
                                @if($movement->order_id)
                                    <i class="bi bi-receipt ms-1 text-primary opacity-75"
                                        data-bs-toggle="tooltip" title="Automatic — from an order approval"></i>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($movement->isReversible())
                                    <button class="btn btn-light btn-sm rounded-circle"
                                        data-bs-toggle="modal" data-bs-target="#reverseMovementModal"
                                        data-id="{{ $movement->movement_id }}"
                                        data-material="{{ $movement->rawMaterial?->name }}"
                                        data-reason="{{ $movement->reason->shortLabel() }}"
                                        data-quantity="{{ rtrim(rtrim(number_format((float) $movement->quantity, 2), '0'), '.') }}"
                                        data-unit="{{ $movement->rawMaterial?->unit }}"
                                        title="Reverse this entry">
                                        <i class="bi bi-arrow-counterclockwise text-success"></i>
                                    </button>
                                @else
                                    <span class="text-muted opacity-50" style="font-size: 0.75rem;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="bi bi-clock-history fs-1 opacity-25"></i>
                                </div>
                                No stock movements recorded yet.
                                <div class="small mt-1 opacity-75">
                                    Use <span class="fw-semibold">Record Usage</span> on the Materials tab to log the first one.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination rides the table's horizontal scroll, as elsewhere. -->
            <div class="pagination-bar border-top d-flex justify-content-between align-items-center gap-2 p-3">
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <label for="logPerPageSelect" class="text-muted small mb-0">Rows per page:</label>
                    <select id="logPerPageSelect" class="form-select form-select-sm rounded-pill w-auto"
                        onchange="(function(v){const u=new URL(window.location.href);u.searchParams.set('log_per_page',v);u.searchParams.set('tab','log');u.searchParams.delete('log_page');window.location.href=u.toString();})(this.value)">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (int) request()->query('log_per_page', 10) === $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-muted small text-nowrap">
                        Showing {{ $movements->firstItem() ?? 0 }} to {{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }} entries
                    </span>
                </div>
                <nav class="flex-shrink-0">
                    {{ $movements->appends(['tab' => 'log'])->links() }}
                </nav>
            </div>
        </div>
    </div>
</div>
