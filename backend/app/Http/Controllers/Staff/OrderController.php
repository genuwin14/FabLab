<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderList;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use \App\Http\Controllers\Concerns\ShowsOrderMaterials;
    use \App\Http\Controllers\Concerns\ExportsOrders;

    public function index(Request $request, OrderList $list)
    {
        $filters = $list->filters($request);
        ['search' => $search, 'status' => $status, 'date' => $date] = $filters;
        $perPage = (int) $request->input('per_page', 10);

        // The detail modal renders each line from this JSON, cell included.
        $orders = $list->query($filters)
            ->with(['user', 'orderItems.product', 'orderItems.customDesign', 'orderItems.productVariant.color'])
            ->paginate($perPage)
            ->withQueryString();

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
        'paid' => 'processing',
        'processing' => 'ready_for_pickup',
        'ready_for_pickup' => 'completed',
    ];

    /**
     * A Purchase Request order runs on paperwork instead. The admin's Notice
     * of Award starts production and the Purchase Order releases it for
     * delivery, so the only step left for staff is handing it over at the end.
     */
    private const PR_TRANSITIONS = [
        'for_delivery' => 'completed',
    ];

    public function updateStatus(Request $request, $id, OrderStockService $stock)
    {
        $request->validate([
            'status' => 'required|in:processing,ready_for_pickup,completed',
        ]);

        $order = Order::with('user')->findOrFail($id);
        $oldStatus = $order->status;

        // The customer pays at PAXS and the admin records the receipt
        // number; only then may staff cut into the materials. Staff never
        // enter the number themselves — see Admin\OrderController::recordPayment.
        if ($request->status === 'processing' && $order->isAwaitingPayment()) {
            return back()->with('error', "Order {$order->order_number} hasn't been paid yet — an admin records the receipt number once the customer has paid at PAXS.");
        }

        $transitions = $order->isPurchaseRequest() ? self::PR_TRANSITIONS : self::TRANSITIONS;
        $next = $transitions[$oldStatus] ?? null;

        if ($request->status !== $next) {
            $readable = str_replace('_', ' ', $oldStatus);

            if ($next === null) {
                return back()->with('error', $order->isPurchaseRequest() && in_array($oldStatus, ['approved', 'processing'], true)
                    ? "Order {$order->order_number} is {$readable} and waiting on procurement paperwork — an admin uploads the next document."
                    : "Order {$order->order_number} is {$readable}; there is no next step for staff to take.");
            }

            return back()->with('error', "Order {$order->order_number} is {$readable} — the next step is " . str_replace('_', ' ', $next) . '.');
        }

        $order->status = $request->status;

        // Starting production is the point the shop actually cuts into its
        // materials. Approval only reserved them — see OrderStockService — so
        // this is where the reservations become consumption and the materials
        // report starts counting them as used.
        //
        // Both halves in one transaction: an order that says it is processing
        // while its materials still read as reserved would have staff at the
        // bench and the report disagreeing about whether the job had started.
        DB::transaction(function () use ($order, $stock) {
            $order->save();

            if ($order->status === 'processing') {
                $stock->startProduction($order);
            }
        });

        if ($oldStatus !== $order->status) {
            \App\Support\Notifier::customer($order->user, new \App\Notifications\OrderStatusChanged($order, $oldStatus, $order->status));
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Stream the same transaction-slip PDF the customer gets. Staff reach any
     * order, not just their own, so this skips the ownership check the
     * customer route applies — the staff middleware is the gate here.
     */
    public function receipt($id)
    {
        $order = Order::with(['orderItems.product', 'user'])->findOrFail($id);

        if (!in_array($order->status, \App\Support\TransactionSlip::PRINTABLE_STATUSES)) {
            abort(404);
        }

        return \App\Support\TransactionSlip::pdf($order)
            ->stream('Transaction-Slip-' . $order->order_number . '.pdf');
    }
}
