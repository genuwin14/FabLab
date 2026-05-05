<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Support\Carbon;

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
        // Sales trend (last 6 months)
        $salesTrend = Order::where('status', 'completed')
            ->selectRaw('SUM(total_amount) as total, MONTHNAME(created_at) as month, MONTH(created_at) as month_num, YEAR(created_at) as year_num')
            ->where('created_at', '>=', $now->copy()->subMonths(6))
            ->groupBy('month', 'month_num', 'year_num')
            ->orderBy('year_num')
            ->orderBy('month_num')
            ->get();

        // Order status breakdown
        $orderStatusBreakdown = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Inventory by category
        $categoryDistribution = Category::withCount('products')
            ->get()
            ->map(function ($cat) {
                return [
                    'name' => $cat->name,
                    'count' => $cat->products_count,
                ];
            });

        // ---------------- Inventory Health ----------------
        $productStockValue = Product::selectRaw('SUM(stock * price) as value')->value('value') ?? 0;
        $materialStockValue = RawMaterial::selectRaw('SUM(stock_quantity * cost_per_unit) as value')->value('value') ?? 0;
        $textureStockValue = Texture::selectRaw('SUM(stock_quantity * cost_per_unit) as value')->value('value') ?? 0;

        $inventoryHealth = [
            'products' => [
                'total' => $totalProducts,
                'low_stock' => $lowStockProducts,
                'value' => $productStockValue,
            ],
            'materials' => [
                'total' => RawMaterial::count(),
                'low_stock' => $lowStockMaterials,
                'value' => $materialStockValue,
            ],
            'textures' => [
                'total' => Texture::count(),
                'low_stock' => $lowStockTextures,
                'value' => $textureStockValue,
            ],
        ];

        // ---------------- Activity Lists ----------------
        $recentOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        $criticalStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderByRaw('(stock / NULLIF(low_stock_threshold, 0)) ASC')
            ->take(5)
            ->get();

        $topProducts = Product::with('category')
            ->withSum(['orderItems as total_sold' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'completed');
                });
            }], 'quantity')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $recentPurchaseOrders = PurchaseOrder::with('supplier')
            ->latest()
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
            'salesTrend',
            'orderStatusBreakdown',
            'categoryDistribution',
            'inventoryHealth',
            'recentOrders',
            'criticalStockProducts',
            'topProducts',
            'recentPurchaseOrders'
        ));
    }
}
