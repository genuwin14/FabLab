<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems'])->latest();

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
        return view('admin.order.order', compact('orders'));
    }
    public function review(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,cancelled',
            'reason' => 'nullable|string|required_if:status,cancelled'
        ]);

        $order = Order::with(['user', 'orderItems.product.rawMaterials'])->findOrFail($id);
        $oldStatus = $order->status;

        $order->update([
            'status' => $request->status,
            'reason' => $request->reason
        ]);

        // If newly cancelled, return stock (Products AND Raw Materials)
        if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    // Return Product Stock
                    $item->product->increment('stock', $item->quantity);
                    
                    // Return Raw Materials if it was previously approved
                    if ($oldStatus === 'approved') {
                        foreach ($item->product->rawMaterials as $material) {
                            $material->increment('stock_quantity', $material->pivot->quantity_required * $item->quantity);
                        }
                    }
                }
            }
        }

        // If newly approved, deduct Raw Materials
        if ($request->status === 'approved' && $oldStatus !== 'approved') {
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    foreach ($item->product->rawMaterials as $material) {
                        $material->decrement('stock_quantity', $material->pivot->quantity_required * $item->quantity);
                    }
                }
            }

            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\OrderReceipt($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send transaction slip email: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
