<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\Texture;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfToday = $now->copy()->startOfDay();

        // ---------------- Order Pipeline KPIs ----------------
        $pendingCount = Order::where('status', 'pending')->count();
        $processingCount = Order::where('status', 'processing')->count();
        $readyCount = Order::where('status', 'ready_for_pickup')->count();
        $completedTodayCount = Order::where('status', 'completed')
            ->where('updated_at', '>=', $startOfToday)
            ->count();

        // ---------------- Stock Alerts ----------------
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')->count();
        $lowStockMaterials = RawMaterial::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $lowStockTextures = Texture::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();

        $incomingPOCount = PurchaseOrder::whereIn('status', ['sent', 'confirmed'])->count();

        // ---------------- Charts ----------------
        // Order status breakdown (live pipeline)
        $orderStatusBreakdown = Order::selectRaw('status, COUNT(*) as count')
            ->whereIn('status', ['pending', 'approved', 'processing', 'ready_for_pickup'])
            ->groupBy('status')
            ->get();

        // Daily orders (last 7 days) — operational activity trend
        $dailyOrders = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->startOfDay();
            $count = Order::whereBetween('created_at', [$day, $day->copy()->endOfDay()])->count();
            $dailyOrders->push([
                'label' => $day->format('D'),
                'date' => $day->format('M j'),
                'count' => $count,
            ]);
        }

        // ---------------- Action Lists ----------------
        // Orders needing staff action (pending + approved, oldest first)
        $orderQueue = Order::with('user')
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->orderBy('created_at')
            ->take(6)
            ->get();

        // Critical stock — products closest to or below threshold
        $criticalStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderByRaw('(stock / NULLIF(low_stock_threshold, 0)) ASC')
            ->take(5)
            ->get();

        return view('staff.dashboard.dashboard', compact(
            'pendingCount',
            'processingCount',
            'readyCount',
            'completedTodayCount',
            'lowStockProducts',
            'lowStockMaterials',
            'lowStockTextures',
            'incomingPOCount',
            'orderStatusBreakdown',
            'dailyOrders',
            'orderQueue',
            'criticalStockProducts'
        ));
    }
}
