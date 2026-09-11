<?php

namespace App\Services;

use App\Models\CustomDesign;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Every move of finished-goods stock, in one place, variant-aware.
 *
 * Product stock used to be one column, decremented at checkout and put
 * back on cancellation from six different places. Now a product may be a
 * grid of variants — a size, a colour, a cell each — and a move has to land
 * in the right cell, or a Navy 5XL sold would come off the White Medium.
 *
 * Callers say what they know — a design, or a size and a colour the shopper
 * picked — and this resolves the cell. A product with no variants is moved
 * as before, on its own column.
 */
class ProductStockService
{
    /**
     * The cell a design takes: its recipe's size and colour, resolved
     * against what the product actually tracks.
     */
    public function variantForDesign(Product $product, ?CustomDesign $design): ?ProductVariant
    {
        if (! $design) {
            return $product->variantFor(null, null);
        }

        return $product->variantFor($design->recipe['size'] ?? null, $design->recipe['color_id'] ?? null);
    }

    /**
     * Whether the shopper has to say more before this product can go in the
     * cart: a size for a sized product, a colour for a coloured one. The
     * missing dimension, or null when nothing is missing.
     */
    public function missingChoice(Product $product, ?string $size, int|string|null $colorId): ?string
    {
        if (! $product->tracksVariants()) {
            return null;
        }

        if ($product->has_sizes && ! \App\Models\CustomizationRate::keyForSize($size)) {
            return 'size';
        }

        if ($product->hasColorVariants() && ! $colorId) {
            return 'colour';
        }

        return null;
    }

    /** How many of this cell — or of the product, when it has no cells — are on the shelf. */
    public function available(Product $product, ?ProductVariant $variant): int
    {
        return (int) ($variant ? $variant->stock : $product->stock);
    }

    /** Take stock off the shelf: at checkout. */
    public function take(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $this->move($product, $variant, -$quantity);
    }

    /** Put stock back: cancellation, rejection, a delivery. */
    public function give(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $this->move($product, $variant, $quantity);
    }

    private function move(Product $product, ?ProductVariant $variant, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        // A product that tracks variants but was handed none — an order item
        // from before variants existed, a purchase order line with no cell
        // picked — lands in its first cell rather than on a total that would
        // then disagree with its cells.
        $variant ??= $product->tracksVariants() ? $product->firstVariant() : null;

        $target = $variant ?? $product;
        $method = $delta > 0 ? 'increment' : 'decrement';
        $target->{$method}('stock', abs($delta));
    }
}
