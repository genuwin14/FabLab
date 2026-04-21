<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product', 'orderItems.customDesign'])->latest();

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('fullname', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->whereIn('status', $request->status);
        }

        // Date Filter
        if ($request->filled('date')) {
            switch ($request->date) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                    break;
            }
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('staff.order.order', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,ready_for_pickup,completed,cancelled',
            'payment_reference' => 'required_if:status,processing|nullable|string'
        ]);

        $order = Order::findOrFail($id);

        $order->status = $request->status;

        if ($request->filled('payment_reference')) {
            $order->payment_reference = $request->payment_reference;
        }

        $order->save();

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
