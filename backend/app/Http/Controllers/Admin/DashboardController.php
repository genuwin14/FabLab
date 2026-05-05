<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfToday = $now->copy()->startOfDay();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // ---------------- Primary KPIs ----------------
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        $todayRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfToday)
            ->sum('total_amount');
        $pendingOrdersCount = Order::where('status', 'pending')->count();

        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')->count();
        $lowStockMaterials = RawMaterial::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $lowStockTextures = Texture::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $lowStockCount = $lowStockProducts + $lowStockMaterials + $lowStockTextures;

        // Month-over-month growth indicators
        $revenueThisMonth = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');
        $revenueLastMonth = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');
        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : ($revenueThisMonth > 0 ? 100 : 0);

        $ordersThisMonth = Order::where('created_at', '>=', $startOfMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $orderGrowth = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : ($ordersThisMonth > 0 ? 100 : 0);

        // ---------------- Secondary KPIs ----------------
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalSuppliers = Supplier::count();
        $pendingPOCount = PurchaseOrder::whereIn('status', ['draft', 'sent', 'confirmed'])->count();

        $newCustomersThisMonth = User::where('role', 'customer')
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // ---------------- Charts ----------------
        // Stock trend — top 5 most-active products' inventory levels over the last 30 days.
        // Reverse-calculated from current stock + cumulative completed-order quantities.
        $days = 30;
        $stockTrendStart = $now->copy()->subDays($days)->startOfDay();

        $topActiveProducts = Product::whereHas('orderItems.order', function ($q) use ($stockTrendStart) {
            $q->where('status', 'completed')->where('updated_at', '>=', $stockTrendStart);
        })
            ->withSum(['orderItems as recent_sold' => function ($q) use ($stockTrendStart) {
                $q->whereHas('order', function ($qq) use ($stockTrendStart) {
                    $qq->where('status', 'completed')->where('updated_at', '>=', $stockTrendStart);
                });
            }], 'quantity')
            ->orderByDesc('recent_sold')
            ->take(5)
            ->get();

        // Single batched query for daily sales of the chosen products
        $productIds = $topActiveProducts->pluck('product_id');
        $salesByProduct = collect();
        if ($productIds->isNotEmpty()) {
            $salesByProduct = DB::table('order_items')
                ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
                ->whereIn('order_items.product_id', $productIds)
                ->where('orders.status', 'completed')
                ->where('orders.updated_at', '>=', $stockTrendStart)
                ->selectRaw('order_items.product_id, DATE(orders.updated_at) as day, SUM(order_items.quantity) as qty')
                ->groupBy('order_items.product_id', 'day')
                ->get()
                ->groupBy('product_id')
                ->map(fn ($rows) => $rows->pluck('qty', 'day'));
        }

        // Build labels (chronological) and per-product reverse-calculated stock levels
        $stockLabels = [];
        for ($i = $days; $i >= 0; $i--) {
            $stockLabels[] = $now->copy()->subDays($i)->format('M j');
        }

        $stockSeries = $topActiveProducts->map(function ($product) use ($now, $days, $salesByProduct) {
            $dailySales = $salesByProduct[$product->product_id] ?? collect();
            $points = [];
            $cumulativeSold = 0;

            // Walk from today backward — stock at end of day i = current stock + items sold AFTER day i
            for ($i = 0; $i <= $days; $i++) {
                $date = $now->copy()->subDays($i);
                $points[] = max(0, (int) ($product->stock + $cumulativeSold));
                $cumulativeSold += (int) ($dailySales[$date->format('Y-m-d')] ?? 0);
            }

            return [
                'name' => $product->name,
                'data' => array_reverse($points),
            ];
        })->values();

        // ---------------- Activity Lists ----------------
        $topProducts = Product::with('category')
            ->withSum(['orderItems as total_sold' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'completed');
                });
            }], 'quantity')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return view('admin.dashboard.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'pendingOrdersCount',
            'lowStockCount',
            'revenueGrowth',
            'orderGrowth',
            'totalProducts',
            'totalCustomers',
            'totalSuppliers',
            'pendingPOCount',
            'newCustomersThisMonth',
            'stockLabels',
            'stockSeries',
            'topProducts'
        ));
    }
}
