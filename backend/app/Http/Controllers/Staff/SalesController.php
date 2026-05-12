<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();

        // ---------------- Resolve the date range ----------------
        $presets = ['7days', '30days', '90days', '12months', 'all'];
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
            if (! in_array($range, $presets, true)) {
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
        if ($groupByMonth) {
            $revByPeriod = $completedInRange()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as period, SUM(total_amount) as revenue")
                ->groupBy('period')->pluck('revenue', 'period');
            $ordByPeriod = $completedInRange()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-01') as period, COUNT(*) as orders")
                ->groupBy('period')->pluck('orders', 'period');

            $labels = [];
            $revenueSeries = [];
            $orderSeries = [];
            $cursor = $start->copy()->startOfMonth();
            $stop = $end->copy()->startOfMonth();
            while ($cursor->lte($stop)) {
                $key = $cursor->format('Y-m-01');
                $labels[] = $cursor->format('M Y');
                $revenueSeries[] = round((float) ($revByPeriod[$key] ?? 0), 2);
                $orderSeries[] = (int) ($ordByPeriod[$key] ?? 0);
                $cursor->addMonth();
            }
        } else {
            $revByPeriod = $completedInRange()
                ->selectRaw('DATE(created_at) as period, SUM(total_amount) as revenue')
                ->groupBy('period')->pluck('revenue', 'period');
            $ordByPeriod = $completedInRange()
                ->selectRaw('DATE(created_at) as period, COUNT(*) as orders')
                ->groupBy('period')->pluck('orders', 'period');

            $labels = [];
            $revenueSeries = [];
            $orderSeries = [];
            $cursor = $start->copy()->startOfDay();
            $stop = $end->copy()->startOfDay();
            while ($cursor->lte($stop)) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('M j');
                $revenueSeries[] = round((float) ($revByPeriod[$key] ?? 0), 2);
                $orderSeries[] = (int) ($ordByPeriod[$key] ?? 0);
                $cursor->addDay();
            }
        }

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

        return view('staff.sales.sales', [
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
        ]);
    }
}
