<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $date = $request->input('date', '');
        $perPage = (int) $request->input('per_page', 10);

        $query = Order::with(['user', 'orderItems'])->latest();

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

        return view('admin.order.order', compact('orders', 'search', 'status', 'date', 'perPage', 'statusCounts'));
    }
    /**
     * Approve or reject an order awaiting review.
     *
     * Only a pending order can be reviewed: re-running this on an approved
     * order would deduct its materials a second time.
     */
    public function review(Request $request, $id, OrderStockService $stock)
    {
        $request->validate([
            'status' => 'required|in:approved,cancelled',
            'reason' => 'nullable|string|required_if:status,cancelled'
        ]);

        $order = Order::with(['user', 'orderItems.product.rawMaterials', 'orderItems.customDesign'])->findOrFail($id);

        if ($order->status !== 'pending') {
            return back()->with('error', "Order {$order->order_number} is already {$this->label($order->status)} and can no longer be reviewed.");
        }

        // Approving commits the shop to producing this, so refuse to do it on
        // materials that aren't there — otherwise stock silently goes negative.
        if ($request->status === 'approved') {
            $shortages = $stock->shortages($order);

            if ($shortages !== []) {
                return back()->with('error', 'Not enough stock to approve this order: ' . implode('; ', $shortages) . '.');
            }
        }

        DB::transaction(function () use ($order, $request, $stock) {
            $order->update([
                'status' => $request->status,
                'reason' => $request->reason,
            ]);

            if ($request->status === 'approved') {
                $stock->consume($order);
            } else {
                // Checkout took the product stock; a rejected order never got
                // as far as consuming materials.
                $stock->returnProducts($order);
            }
        });

        if ($request->status === 'approved') {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\OrderReceipt($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send transaction slip email: ' . $e->getMessage());
            }
        }

        $this->notifyCustomer($order, 'pending', $request->status);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Cancel an order that is already past review, returning everything it
     * took: finished stock, raw materials and textures.
     */
    public function cancel(Request $request, $id, OrderStockService $stock)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $order = Order::with(['user', 'orderItems.product.rawMaterials', 'orderItems.customDesign'])->findOrFail($id);

        $cancellable = ['approved', 'processing', 'ready_for_pickup'];

        if (! in_array($order->status, $cancellable, true)) {
            $message = $order->status === 'pending'
                ? "Order {$order->order_number} hasn't been reviewed yet — reject it from the review screen instead."
                : "Order {$order->order_number} is {$this->label($order->status)} and can no longer be cancelled.";

            return back()->with('error', $message);
        }

        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $request, $stock) {
            $order->update([
                'status' => 'cancelled',
                'reason' => $request->reason,
            ]);

            $stock->returnProducts($order);
            $stock->restore($order);
        });

        $this->notifyCustomer($order, $oldStatus, 'cancelled');

        return back()->with('success', "Order {$order->order_number} cancelled and stock returned.");
    }

    private function notifyCustomer(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus !== $newStatus && $order->user && $order->user->notifications_enabled) {
            $order->user->notify(new \App\Notifications\OrderStatusChanged($order, $oldStatus, $newStatus));
        }
    }

    private function label(string $status): string
    {
        return str_replace('_', ' ', $status);
    }
}
