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
    public function run(): void
    {
        // quantity_required is per single unit of the product.
        $recipes = [
            'IDL-LACE-STD' => [
                'Lanyard Metal Clip' => 1,
                'Woven Polyester Strap (16mm)' => 0.9,
                'PVC ID Card Holder' => 1,
                'Sublimation Transfer Paper (A4)' => 1,
            ],
            'MG-WHT-11' => [
                'Sublimation Transfer Paper (A4)' => 1,
            ],
            'MG-BLK-11' => [
                'Sublimation Transfer Paper (A4)' => 1,
            ],
            'TS-CTN-WHT' => [
                'Sublimation Transfer Paper (A4)' => 2,
            ],
            'BK-BKLT-A5' => [
                'Bond Paper (A4, 80gsm)' => 6,   // 24 pages, 4 up per sheet
                'Vellum Board (220gsm)' => 1,
            ],
            'WD-PLQ-OAK' => [
                'Solid Oak Wood Planks' => 0.5,
                'Wood Varnish (Gloss)' => 0.05,
            ],
            'WD-STL-PINE' => [
                'Plywood 4x8 1/4 inch' => 1.5,
                'Wood Varnish (Gloss)' => 0.1,
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
