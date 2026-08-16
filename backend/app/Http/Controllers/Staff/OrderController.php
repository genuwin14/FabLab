<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $date = $request->input('date', '');
        $perPage = (int) $request->input('per_page', 10);

        $query = Order::with(['user', 'orderItems.product', 'orderItems.customDesign'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('fullname', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($date !== '') {
            switch ($date) {
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

        $orders = $query->paginate($perPage)->withQueryString();

        $statusCounts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // The design detail popout resolves a recipe's texture out of this.
        $customizerTextures = \App\Models\Texture::customizerPayload();

        return view('staff.order.order', compact('orders', 'search', 'status', 'date', 'perPage', 'statusCounts', 'customizerTextures'));
    }

    /**
     * The production pipeline, in order. Staff advance an order one step at a
     * time — the same single button the list offers — so a crafted request
     * can't send a completed order back to pending or skip a stage.
     */
    private const TRANSITIONS = [
        'approved' => 'processing',
        'processing' => 'ready_for_pickup',
        'ready_for_pickup' => 'completed',
    ];

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,ready_for_pickup,completed',
            'payment_reference' => 'required_if:status,processing|nullable|string'
        ]);

        $order = Order::with('user')->findOrFail($id);
        $oldStatus = $order->status;

        $next = self::TRANSITIONS[$oldStatus] ?? null;

        if ($request->status !== $next) {
            $readable = str_replace('_', ' ', $oldStatus);

            return back()->with('error', $next === null
                ? "Order {$order->order_number} is {$readable}; there is no next step for staff to take."
                : "Order {$order->order_number} is {$readable} — the next step is " . str_replace('_', ' ', $next) . '.');
        }

        $order->status = $request->status;

        if ($request->filled('payment_reference')) {
            $order->payment_reference = $request->payment_reference;
        }

        $order->save();

        if ($oldStatus !== $order->status && $order->user && $order->user->notifications_enabled) {
            $order->user->notify(new \App\Notifications\OrderStatusChanged($order, $oldStatus, $order->status));
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
