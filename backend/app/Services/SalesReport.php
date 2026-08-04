<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The sales figures behind the admin and staff sales pages and the PDF/DOCX
 * exports. One source, so an exported document can never disagree with the
 * screen it was exported from.
 *
 * Only `completed` orders count as sales.
 */
class SalesReport
{
    /** Ranges the UI offers, besides an explicit from/to. */
    private const PRESETS = ['7days', '30days', '90days', '12months', 'all'];

    public function build(Request $request): array
    {
        $now = Carbon::now();

        // ---------------- Resolve the date range ----------------
        $range = $request->input('range', '30days');
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        if ($dateFrom !== '' && $dateTo !== '') {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end = Carbon::parse($dateTo)->endOfDay();
            if ($start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }
            $range = 'custom';
        } else {
            if (! in_array($range, self::PRESETS, true)) {
                $range = '30days';
            }
            $end = $now->copy()->endOfDay();
            $start = match ($range) {
                '7days'    => $now->copy()->subDays(6)->startOfDay(),
                '90days'   => $now->copy()->subDays(89)->startOfDay(),
                '12months' => $now->copy()->subMonths(11)->startOfMonth(),
                'all'      => Order::min('created_at')
                    ? Carbon::parse(Order::min('created_at'))->startOfDay()
                    : $now->copy()->subDays(29)->startOfDay(),
                default    => $now->copy()->subDays(29)->startOfDay(),
            };
        }

        // Aggregate by month when the window is wide; otherwise by day.
        $groupByMonth = $start->diffInDays($end) > 70;

        // ---------------- Completed-sales base query ----------------
        $completedInRange = fn () => Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end]);

        $totalRevenue = (float) $completedInRange()->sum('total_amount');
        $orderCount = (int) $completedInRange()->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0.0;

        $itemsSold = (int) DB::table('order_items')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$start, $end])
            ->sum('order_items.quantity');

        $allTimeRevenue = (float) Order::where('status', 'completed')->sum('total_amount');

        // ---------------- Revenue / orders time series ----------------
        [$labels, $revenueSeries, $orderSeries] = $groupByMonth
            ? $this->monthlySeries($completedInRange, $start, $end)
            : $this->dailySeries($completedInRange, $start, $end);

        // ---------------- Best-selling products in range ----------------
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.product_id', '=', 'order_items.product_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('order_items.product_id,
                         COALESCE(products.name, "Deleted product") as name,
                         products.sku as sku,
                         products.image as image,
                         SUM(order_items.quantity) as qty,
                         SUM(order_items.quantity * order_items.price) as revenue')
            ->groupBy('order_items.product_id', 'products.name', 'products.sku', 'products.image')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        // ---------------- Recent completed sales ----------------
        $recentSales = $completedInRange()
            ->with('user')
            ->withCount('orderItems')
            ->latest()
            ->limit(10)
            ->get();

        // ---------------- All-status breakdown within range ----------------
        $statusBreakdown = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('status, COUNT(*) as total, SUM(total_amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'range'           => $range,
            'dateFrom'        => $dateFrom,
            'dateTo'          => $dateTo,
            'rangeStart'      => $start,
            'rangeEnd'        => $end,
            'totalRevenue'    => $totalRevenue,
            'orderCount'      => $orderCount,
            'avgOrderValue'   => $avgOrderValue,
            'itemsSold'       => $itemsSold,
            'allTimeRevenue'  => $allTimeRevenue,
            'chartLabels'     => $labels,
            'revenueSeries'   => $revenueSeries,
            'orderSeries'     => $orderSeries,
            'groupByMonth'    => $groupByMonth,
            'topProducts'     => $topProducts,
            'recentSales'     => $recentSales,
            'statusBreakdown' => $statusBreakdown,
        ];
    }

    /**
     * A human label for the window, used as the report's subtitle.
     */
    public function rangeLabel(array $report): string
    {
        return match ($report['range']) {
            '7days' => 'Last 7 days',
            '30days' => 'Last 30 days',
            '90days' => 'Last 90 days',
            '12months' => 'Last 12 months',
            'all' => 'All time',
            default => $report['rangeStart']->format('M j, Y') . ' – ' . $report['rangeEnd']->format('M j, Y'),
        };
    }

    /**
     * Daily buckets, rolled up in PHP.
     *
     * The monthly grouping used to be a DATE_FORMAT() call, which is MySQL-only
     * — on the SQLite setup the guide recommends for local work, any range wider
     * than ~10 weeks blew up. DATE() is understood by both, so the query stays
     * the same shape whichever way the figures are grouped.
     */
    private function monthlySeries(callable $completedInRange, Carbon $start, Carbon $end): array
    {
        $daily = $this->dailyTotals($completedInRange);

        $labels = [];
        $revenueSeries = [];
        $orderSeries = [];

        $cursor = $start->copy()->startOfMonth();
        $stop = $end->copy()->startOfMonth();

        while ($cursor->lte($stop)) {
            $prefix = $cursor->format('Y-m-');
            $revenue = 0.0;
            $orders = 0;

            foreach ($daily as $day => $totals) {
                if (str_starts_with($day, $prefix)) {
                    $revenue += $totals['revenue'];
                    $orders += $totals['orders'];
                }
            }

            $labels[] = $cursor->format('M Y');
            $revenueSeries[] = round($revenue, 2);
            $orderSeries[] = $orders;
            $cursor->addMonth();
        }

        return [$labels, $revenueSeries, $orderSeries];
    }

    private function dailySeries(callable $completedInRange, Carbon $start, Carbon $end): array
    {
        $daily = $this->dailyTotals($completedInRange);

        $labels = [];
        $revenueSeries = [];
        $orderSeries = [];

        $cursor = $start->copy()->startOfDay();
        $stop = $end->copy()->startOfDay();

        while ($cursor->lte($stop)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('M j');
            $revenueSeries[] = round((float) ($daily[$key]['revenue'] ?? 0), 2);
            $orderSeries[] = (int) ($daily[$key]['orders'] ?? 0);
            $cursor->addDay();
        }

        return [$labels, $revenueSeries, $orderSeries];
    }

    /** @return array<string, array{revenue: float, orders: int}> keyed Y-m-d */
    private function dailyTotals(callable $completedInRange): array
    {
        return $completedInRange()
            ->selectRaw('DATE(created_at) as period, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('period')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (string) $row->period => [
                    'revenue' => (float) $row->revenue,
                    'orders' => (int) $row->orders,
                ],
            ])
            ->all();
    }
}
