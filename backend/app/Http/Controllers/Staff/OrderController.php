<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->latest()
            ->paginate(10);

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
