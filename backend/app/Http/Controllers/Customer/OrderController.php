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
     * Record the PR number procurement issued, which releases the order for
     * review. Holding a number means the request was approved, and that is the
     * only thing FabLab is allowed to take as permission to accept the order.
     */
    public function submitPrNumber(Request $request, $id)
    {
        $request->validate([
            'pr_number' => 'required|string|max:100',
        ]);

        $order = Order::where('order_id', $id)
            ->where('user_id', Auth::id())
            ->with('user')
            ->firstOrFail();

        if (! $order->isAwaitingPr()) {
            return back()->with('error', "Order {$order->order_number} isn't waiting for a PR number.");
        }

        // The window is checked here as well as in the nightly sweep: an order
        // that lapsed hours ago shouldn't squeeze through before the sweep runs.
        if ($order->prWindowHasClosed()) {
            return back()->with('error', "The Purchase Request window for order {$order->order_number} closed on {$order->pr_deadline->format('j M Y')}. Please place the order again.");
        }

        $order->update([
            'pr_number' => $request->pr_number,
            'status' => 'pending',
        ]);

        // Now it is reviewable, so this is the point staff and admins hear
        // about it — checkout stayed quiet.
        \App\Support\Notifier::staffAndAdmins(new \App\Notifications\NewOrderPlaced($order));

        return back()->with('success', "PR number recorded. Order {$order->order_number} is now awaiting review.");
    }

    /**
     * Cancel an order.
     *
     * A PR order can also be dropped while it waits — the customer may simply
     * decide not to pursue the request.
     */
    public function cancel($id)
    {
        $order = Order::where('order_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (in_array($order->status, ['pending', 'awaiting_pr'], true)) {
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
