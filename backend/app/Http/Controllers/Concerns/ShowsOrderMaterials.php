<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Order;
use App\Services\OrderStockService;

/**
 * "What will this take off the shelf?", answered for one order.
 *
 * Both order screens ask it at the moment it matters — the admin before
 * approving, staff before starting production — and neither could answer it
 * before: the review modal checked finished-goods stock and said nothing about
 * the materials the order would actually draw.
 *
 * Fetched when the modal opens rather than embedded in the list, because
 * working it out means walking two bills of materials per line and the list
 * shows ten orders at a time. Nine of those answers would be thrown away.
 */
trait ShowsOrderMaterials
{
    public function materials($id, OrderStockService $stock)
    {
        $order = Order::findOrFail($id);

        return response()->json(
            $stock->plannedDraw($order) + ['order_number' => $order->order_number]
        );
    }
}
