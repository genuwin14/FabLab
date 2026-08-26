<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['orderItems.product']) // Eager load items and products
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.order.order', compact('orders'));
    }

    /**
     * Cancel an order.
     */
    public function cancel($id)
    {
        $order = Order::where('order_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->status === 'pending') {
            $order->status = 'cancelled';
            $order->save();

            // Return Stock
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            return redirect()->back()->with('success', 'Order cancelled successfully.');
        }

        return redirect()->back()->with('error', 'Order cannot be cancelled.');
    }

    /**
     * Stream the transaction-slip PDF for an approved order owned by the user.
     */
    public function receipt($id)
    {
        $order = Order::where('order_id', $id)
            ->where('user_id', Auth::id())
            ->with(['orderItems.product', 'user'])
            ->firstOrFail();

        if (!in_array($order->status, \App\Support\TransactionSlip::PRINTABLE_STATUSES)) {
            abort(404);
        }

        $pdf = \App\Support\TransactionSlip::pdf($order);

        return $pdf->stream('Transaction-Slip-' . $order->order_number . '.pdf');
    }
}
