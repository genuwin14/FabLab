<?php

namespace App\Observers;

use App\Notifications\LowStockAlert;
use App\Notifications\OutOfStockAlert;
use App\Support\Notifier;
use Illuminate\Database\Eloquent\Model;

/**
 * Watches every stock-bearing model — products, raw materials, textures — and
 * raises an alert the moment its level crosses a line, not every time it
 * moves while already below.
 */
class StockLevelObserver
{
    public function updated(Model $item): void
    {
        $column = $item->stockColumn();

        if (! $item->wasChanged($column)) {
            return;
        }

        $old = (float) $item->getOriginal($column);
        $new = $item->currentStock();
        $threshold = $item->stockThreshold();

        // Out of stock: crossed from >0 to <=0
        if ($new <= 0 && $old > 0) {
            Notifier::staffAndAdmins(new OutOfStockAlert($item));

            return;
        }

        // Low stock: crossed at or below the threshold (and not already there)
        if ($threshold !== null && $new > 0 && $new <= $threshold && $old > $threshold) {
            Notifier::staffAndAdmins(new LowStockAlert($item));
        }
    }
}
