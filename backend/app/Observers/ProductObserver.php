<?php

namespace App\Observers;

use App\Models\Product;
use App\Notifications\LowStockAlert;
use App\Notifications\OutOfStockAlert;
use App\Support\Notifier;

class ProductObserver
{
    public function updated(Product $product): void
    {
        if (! $product->wasChanged('stock')) {
            return;
        }

        $old = (int) $product->getOriginal('stock');
        $new = (int) $product->stock;
        $threshold = $product->low_stock_threshold;

        // Out of stock: crossed from >0 to <=0
        if ($new <= 0 && $old > 0) {
            Notifier::staffAndAdmins(new OutOfStockAlert($product));
            return;
        }

        // Low stock: crossed at or below the threshold (and not already there)
        if ($threshold !== null && $new > 0 && $new <= $threshold && $old > $threshold) {
            Notifier::staffAndAdmins(new LowStockAlert($product));
        }
    }
}
