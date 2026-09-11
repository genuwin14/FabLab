<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\CustomizationRate;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

/**
 * Stock the garments per size and colour, the way the shop keeps them.
 *
 * ProductSeeder gives each product one figure. For a shirt that figure is
 * really a shelf of blanks in several colours and eight sizes, and the shop
 * needs to see that the Navy 5XL is down to one while the White Medium is
 * fine. So the garments get their house colours assigned, and their stock
 * is spread across the grid — most of it in the middle sizes, a trickle in
 * the largest, and one cell left deliberately low so the demo has a
 * per-cell alert to show.
 *
 * The total per product is exactly what ProductSeeder set, so nothing
 * elsewhere in the demo data changes. Mugs, the umbrella and the tote come
 * in one size with no colour assigned, so they keep a single figure.
 *
 * Runs after ColorSeeder and ProductSeeder.
 */
class ProductVariantSeeder extends Seeder
{
    /** Which house colours each garment is stocked in. */
    private const COLOURS = [
        'TS-CTN-WHT' => ['Classic White', 'Jet Black', 'Heather Grey', 'Navy Blue'],
        'PL-PQE-WHT' => ['Classic White', 'Heather Grey'],
        'PL-PQE-NVY' => ['Navy Blue', 'Jet Black'],
    ];

    /** How a garment's stock splits across sizes: a shop sells mostly M and L. */
    private const SIZE_SHARE = [
        'small' => 12, 'medium' => 22, 'large' => 22, 'xl' => 18,
        '2xl' => 12, '3xl' => 7, '4xl' => 4, '5xl' => 3,
    ];

    /** A cell left almost empty so the demo can show a per-cell alert. */
    private const LOW_CELL = ['sku' => 'TS-CTN-WHT', 'colour' => 'Navy Blue', 'size' => '5xl', 'stock' => 1];

    public function run(): void
    {
        $colours = Color::all()->keyBy('name');

        foreach (self::COLOURS as $sku => $names) {
            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                continue;
            }

            $ids = collect($names)->map(fn ($name) => $colours->get($name)?->color_id)->filter()->values()->all();
            $product->colors()->sync($ids);
            $product->update(['has_sizes' => true]);
            $product->unsetRelation('colors');

            // The grid, with the single figure sitting in its first cell.
            $product->ensureVariants();

            $this->spread($product->fresh(['variants', 'colors']), (int) $product->stock);
        }
    }

    /**
     * Split a total across the grid by the size shares, evenly per colour,
     * with the rounding remainder in Medium so the sum is exact.
     */
    private function spread(Product $product, int $total): void
    {
        $variants = $product->variants;
        $colourCount = max(1, $product->colors->count());
        $shareTotal = array_sum(self::SIZE_SHARE);

        $stocks = [];
        foreach ($variants as $variant) {
            $share = self::SIZE_SHARE[$variant->size] ?? 0;
            $stocks[$variant->variant_key] = (int) floor($total * $share / $shareTotal / $colourCount);
        }

        // Whatever rounding left over goes to the first Medium cell.
        $remainder = $total - array_sum($stocks);
        $medium = $variants->firstWhere('size', 'medium') ?? $variants->first();
        if ($medium) {
            $stocks[$medium->variant_key] += $remainder;
        }

        // One cell nearly empty, its surplus back into Medium, so the total
        // still matches.
        if ($product->sku === self::LOW_CELL['sku']) {
            $lowColour = Color::where('name', self::LOW_CELL['colour'])->value('color_id');
            $lowKey = ProductVariant::keyFor(self::LOW_CELL['size'], $lowColour);
            if (isset($stocks[$lowKey]) && $medium) {
                $surplus = $stocks[$lowKey] - self::LOW_CELL['stock'];
                $stocks[$lowKey] = self::LOW_CELL['stock'];
                $stocks[$medium->variant_key] += $surplus;
            }
        }

        ProductVariant::withoutEvents(function () use ($variants, $stocks) {
            foreach ($variants as $variant) {
                $variant->update(['stock' => $stocks[$variant->variant_key] ?? 0]);
            }
        });

        $product->syncStockFromVariants();
    }
}
