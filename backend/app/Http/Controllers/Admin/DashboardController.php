<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // KPI Metrics
        $totalRevenue = \App\Models\Order::where('status', 'completed')->sum('total_amount');
        $activeUsersCount = \App\Models\User::count();
        $lowStockCount = \App\Models\Product::whereColumn('stock', '<=', 'low_stock_threshold')->count();
        $pendingOrdersCount = \App\Models\Order::where('status', 'pending')->count();

        // Sales Trend (Last 6 months)
        $salesTrend = \App\Models\Order::where('status', 'completed')
            ->selectRaw('SUM(total_amount) as total, MONTHNAME(created_at) as month, MONTH(created_at) as month_num')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month', 'month_num')
            ->orderBy('month_num')
            ->get();

        // Inventory Status (Categories)
        $categoryDistribution = \App\Models\Category::withCount('products')
            ->get()
            ->map(function($cat) {
                return [
                    'name' => $cat->name,
                    'count' => $cat->products_count
                ];
            });

        // Recent Orders
        $recentOrders = \App\Models\Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Top Selling Products
        $topProducts = \App\Models\Product::with('category')
            ->withSum(['orderItems as total_sold' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                });
            }], 'quantity')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard.dashboard', compact(
            'totalRevenue',
            'activeUsersCount',
            'lowStockCount',
            'pendingOrdersCount',
            'salesTrend',
            'categoryDistribution',
            'recentOrders',
            'topProducts'
        ));
    }
}
