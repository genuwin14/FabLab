<?php

namespace App\Models\Concerns;

/**
 * Shared vocabulary for the three things the shop keeps stock of. They spell
 * their columns differently — products count `stock`, raw materials and
 * textures count `stock_quantity` — so the stock observer and the alert
 * notifications talk to them through this instead.
 *
 * A model using this trait declares `$stockColumn` and `$stockItemType`.
 */
trait TracksStockLevel
{
    public function stockColumn(): string
    {
        return $this->stockColumn ?? 'stock';
    }

    public function stockItemType(): string
    {
        return $this->stockItemType ?? 'Item';
    }

    public function currentStock(): float
    {
        return (float) $this->{$this->stockColumn()};
    }

    public function stockThreshold(): ?float
    {
        $threshold = $this->low_stock_threshold;

        return $threshold === null ? null : (float) $threshold;
    }

    public function stockUnit(): string
    {
        return $this->unit ?: 'pcs';
    }
}
