<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Texture;

/**
 * How many items are on the Stock Monitoring watchlist right now: products,
 * raw materials and textures at or below their low-stock line. The sidebar
 * badge shows it and the bell's poll keeps it current.
 */
class StockWatch
{
    public function count(): int
    {
        return Product::needsRestock()->count()
            + RawMaterial::needsRestock()->count()
            + Texture::needsRestock()->count();
    }
}
