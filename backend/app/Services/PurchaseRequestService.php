<?php

namespace App\Services;

use App\Models\Order;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\DB;

/**
 * Closing out orders that were placed on a Purchase Request but never came
 * back with a PR number.
 *
 * Both ways of closing one — an admin doing it by hand and the nightly sweep
 * doing it on the deadline — land here, so the stock arithmetic can't drift
 * apart between them.
 */
class PurchaseRequestService
{
    public function __construct(private OrderStockService $stock)
    {
    }

    /**
     * Release an order that is still waiting on its PR number.
     *
     * Only finished-goods stock comes back. An order this early was never
     * approved, so it never consumed raw materials or textures — running the
     * full restore here would hand back stock the order never took.
     */
    public function close(Order $order, string $reason): bool
    {
        if (! $order->isAwaitingPr()) {
            return false;
        }

        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => 'cancelled',
                'reason' => $reason,
            ]);

            $this->stock->returnProducts($order);
        });

        $order->loadMissing('user');

        \App\Support\Notifier::customer($order->user, new OrderStatusChanged($order, 'awaiting_pr', 'cancelled'));

        return true;
    }

    /**
     * The wording used when the window runs out rather than someone deciding.
     */
    public function lapsedReason(Order $order): string
    {
        return sprintf(
            'No PR number was submitted before the Purchase Request window closed on %s.',
            $order->pr_deadline?->format('j M Y') ?? 'the deadline'
        );
    }

    /**
     * Every waiting order whose window has run out.
     */
    public function lapsed()
    {
        return Order::with('user', 'orderItems.product')
            ->where('status', 'awaiting_pr')
            ->whereNotNull('pr_deadline')
            ->where('pr_deadline', '<', now())
            ->get();
    }
}
