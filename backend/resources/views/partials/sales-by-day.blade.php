{{-- The day-by-day figures behind the chart, newest first: the panel asked for
     daily sales. One row per day of the range (per month when the range is
     wide), quiet days included so a gap reads as a gap, today marked.
     Expects the SalesReport keys: $periods, $chartLabels, $orderSeries,
     $revenueSeries, $groupByMonth. --}}
@php
    $today = now()->toDateString();
    $salesRows = collect($periods ?? [])->map(fn ($period, $i) => [
        'label' => $groupByMonth ? ($chartLabels[$i] ?? $period) : \Illuminate\Support\Carbon::parse($period)->format('D, M j, Y'),
        'orders' => (int) ($orderSeries[$i] ?? 0),
        'revenue' => (float) ($revenueSeries[$i] ?? 0),
        'today' => ! $groupByMonth && $period === $today,
    ])->reverse()->values();
@endphp
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="p-3 p-md-4 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold text-dark mb-0">{{ $groupByMonth ? 'Sales by Month' : 'Sales by Day' }}</h6>
            <span class="text-muted small">Completed orders, newest first</span>
        </div>
        <div class="table-responsive sales-by-day-scroll">
            <table class="table table-hover align-middle mb-0 sales-by-day-table">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-2 text-primary small text-uppercase fw-bold border-0">{{ $groupByMonth ? 'Month' : 'Day' }}</th>
                        <th class="text-center py-2 text-primary small text-uppercase fw-bold border-0">Orders</th>
                        <th class="text-end pe-4 py-2 text-primary small text-uppercase fw-bold border-0">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesRows as $row)
                        <tr class="{{ $row['orders'] === 0 ? 'is-quiet' : '' }} {{ $row['today'] ? 'is-today' : '' }}">
                            <td class="ps-4 small fw-semibold text-dark">
                                {{ $row['label'] }}
                                @if($row['today'])
                                    <span class="badge rounded-pill ms-1" style="background-color: rgba(255,197,8,.25); color: #7a5c00; font-size: 0.6rem;">Today</span>
                                @endif
                            </td>
                            <td class="text-center small fw-bold">{{ number_format($row['orders']) }}</td>
                            <td class="text-end pe-4 small fw-bold text-primary">₱{{ number_format($row['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted small py-4">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@once
    <style>
        .sales-by-day-scroll { max-height: 26rem; overflow-y: auto; }
        .sales-by-day-table thead th { position: sticky; top: 0; z-index: 1; background: #f8f9fa; }
        .sales-by-day-table tr.is-quiet td { color: #adb5bd !important; font-weight: 500 !important; }
        .sales-by-day-table tr.is-today td { background: rgba(255, 197, 8, 0.08); }
    </style>
@endonce
