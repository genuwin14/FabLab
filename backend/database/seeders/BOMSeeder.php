<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\RawMaterial;

/**
 * Bills of materials — what each product is made of, and how much of it.
 *
 * This is the link that makes a raw material mean something: approving an
 * order for 40 ID laces walks this table and draws 40 clips, 36 metres of
 * strap and 40 card holders off the shelf, each through the usage ledger.
 */
class BOMSeeder extends Seeder
{
    /**
     * Split a print's total ink across the four bottles, in millilitres.
     *
     * Not an even quarter each: the shop's artwork leans on cyan and magenta,
     * uses a little less yellow and least black. That's what makes one colour
     * reach its reorder point well before the others — which is the whole
     * reason the inks are stocked separately rather than as a set.
     */
    private static function ink(float $millilitres): array
    {
        return [
            'Sublimation Ink (Cyan)' => round($millilitres * 0.30, 2),
            'Sublimation Ink (Magenta)' => round($millilitres * 0.30, 2),
            'Sublimation Ink (Yellow)' => round($millilitres * 0.25, 2),
            'Sublimation Ink (Black)' => round($millilitres * 0.15, 2),
        ];
    }

    public function run(): void
    {
        // quantity_required is per single unit of the product.
        $recipes = [
            'IDL-LACE-STD' => [
                'Lanyard Metal Clip' => 1,
                'Woven Polyester Strap (16mm)' => 0.9,
                'PVC ID Card Holder' => 1,
                'Sublimation Transfer Paper (A4)' => 1,
                ...self::ink(2),
            ],
            'MG-WHT-11' => [
                'Sublimation Transfer Paper (A4)' => 1,
                ...self::ink(4),
            ],
            'MG-BLK-11' => [
                'Sublimation Transfer Paper (A4)' => 1,
                ...self::ink(4),
            ],
            'TS-CTN-WHT' => [
                'Sublimation Transfer Paper (A4)' => 2,
                ...self::ink(8),
            ],
            // Three sheets rather than the tee's two: the polo is printed front
            // and back, and the collar takes a sheet of its own.
            'PL-PQE-WHT' => [
                'Sublimation Transfer Paper (A4)' => 3,
                ...self::ink(12),
            ],
            'PL-PQE-NVY' => [
                'Sublimation Transfer Paper (A4)' => 3,
                ...self::ink(12),
            ],
            'BK-BKLT-A5' => [
                'Bond Paper (A4, 80gsm)' => 6,   // 24 pages, 4 up per sheet
                'Vellum Board (220gsm)' => 1,
            ],
            'WD-PLQ-OAK' => [
                'Solid Oak Wood Planks' => 0.5,
                'Wood Varnish (Gloss)' => 0.05,
            ],
        ];

        $materials = RawMaterial::all()->keyBy('name');

        foreach ($recipes as $sku => $components) {
            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                continue;
            }

            $attach = [];
            foreach ($components as $name => $quantity) {
                if ($material = $materials->get($name)) {
                    $attach[$material->raw_material_id] = ['quantity_required' => $quantity];
                }
            }

            if ($attach !== []) {
                $product->rawMaterials()->sync($attach);
            }
        }
    }
}
