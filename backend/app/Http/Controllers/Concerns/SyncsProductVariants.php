<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Product;

/**
 * The stock grid half of saving a product, shared by the admin and staff
 * product screens.
 *
 * A product stocked per size and colour posts one figure per cell as
 * `variants[<variant_key>][stock]` (and an optional threshold), and its
 * single `stock` field is the read-only total. Keyed by the cell rather
 * than by position, so a cell appearing or disappearing between render
 * and submit can't shift a figure onto the wrong one; cells not posted
 * keep what they had.
 */
trait SyncsProductVariants
{
    /**
     * Drop the posted total for a product that is stocked per cell — the
     * cells decide it — unless the product is only just gaining cells, in
     * which case the total is what its first cell inherits.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withoutTotalWhenPerCell(Product $product, array $data): array
    {
        $willTrack = ! empty($data['has_sizes']) || $product->hasColorVariants();

        if ($willTrack && $product->variants()->exists()) {
            unset($data['stock']);
        }

        return $data;
    }

    /**
     * Make the grid match the product's sizes and colours, then apply the
     * posted figures.
     *
     * @param  mixed  $posted  `variant_key => ['stock' => int, 'low_stock_threshold' => ?int]`
     */
    protected function syncVariants(Product $product, $posted): void
    {
        $product->unsetRelation('colors');
        $product->ensureVariants();

        if (! is_array($posted) || ! $product->tracksVariants()) {
            return;
        }

        $variants = $product->variants()->get()->keyBy('variant_key');

        foreach ($posted as $key => $cell) {
            $variant = $variants->get((string) $key);
            if (! $variant || ! is_array($cell)) {
                continue;
            }

            $changes = [];
            if (isset($cell['stock']) && is_numeric($cell['stock'])) {
                $changes['stock'] = max(0, (int) $cell['stock']);
            }
            if (array_key_exists('low_stock_threshold', $cell)) {
                $changes['low_stock_threshold'] = is_numeric($cell['low_stock_threshold']) ? max(0, (int) $cell['low_stock_threshold']) : null;
            }

            if ($changes !== []) {
                $variant->update($changes);
            }
        }

        $product->syncStockFromVariants();
    }
}
