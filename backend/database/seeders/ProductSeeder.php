<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

/**
 * What the FabLab sells.
 *
 * Everything here is finished output. Plywood, filament and acrylic used to be
 * seeded as products in a "Raw Materials" category while also existing in the
 * `raw_materials` table — the same item on two screens, with two stock figures
 * that drifted apart. They now live only in RawMaterialSeeder, and the products
 * that consume them are wired up in BOMSeeder.
 *
 * The scenarios the rest of the app is tested against are preserved: healthy
 * multi-supplier stock, an out-of-stock line, a low-stock line with no supplier
 * attached, an asset, and several customisable items.
 *
 * `is_customizable` is only set on items the studio has a 3D model for — the
 * mugs, the t-shirt and the polos. See Product::customizerShape(); marking
 * anything else customizable opens it in the studio as a t-shirt.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $finishedGoodsId = Category::where('name', 'Finished Goods')->first()->category_id;
        $machineryId = Category::where('name', 'Machinery & Equipment')->first()->category_id;
        $merchandiseId = Category::where('name', 'Merchandise')->first()->category_id;
        // Furniture has no seeded product: the one that lived there was cut
        // from plywood, which the shop doesn't stock.

        $suppliers = Supplier::all();
        $techSupply = $suppliers->firstWhere('name', 'Tech Supply Co.');
        $genMerch = $suppliers->firstWhere('name', 'General Merchandise Inc.');
        $ecoMaterials = $suppliers->firstWhere('name', 'Eco-Materials Ltd.');

        $dcc = \App\Enums\Department::DigitalCustomizationCenter->value;
        $book = \App\Enums\Department::BookProduction->value;
        $wood = \App\Enums\Department::Woodworks->value;

        $products = [
            // The house speciality, and the clearest example of a bill of
            // materials: one lace draws a clip, some strap and a card holder.
            //
            // Not customizable: the studio has no lanyard model, so opening this
            // in it rendered the default t-shirt under the lace's name. Ship a
            // lanyard GLB and a loader for it, and this becomes true again.
            [
                'sku' => 'IDL-LACE-STD',
                'name' => 'Custom ID Lace',
                'description' => 'Sublimated lanyard with swivel clip and clear ID card holder.',
                'brand' => 'FabLab',
                'price' => 150.00,
                'stock' => 240,
                'units_on_display' => 6,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'department' => $dcc,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 50,
                'is_customizable' => false,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 78.00, 'moq' => 50, 'lead' => 5, 'default' => true]
                ]
            ],
            // Healthy stock, multiple suppliers.
            [
                'sku' => 'MG-WHT-11',
                'name' => 'White Ceramic Mug 11oz',
                'description' => 'Blank white mugs for sublimation printing.',
                'brand' => 'Yiwu',
                'price' => 85.00,
                'stock' => 100,
                'units_on_display' => 9,
                'units_sponsored' => 1,
                'units_damaged' => 1,
                'units_consumed' => 0,
                'department' => $dcc,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 24,
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 45.00, 'moq' => 36, 'lead' => 7, 'default' => true],
                    ['id' => $techSupply->supplier_id, 'cost' => 55.00, 'moq' => 12, 'lead' => 2, 'default' => false]
                ]
            ],
            [
                'sku' => 'MG-BLK-11',
                'name' => 'Black Ceramic Mug 11oz',
                'description' => 'Inner colour black sublimation mug.',
                'brand' => 'Yiwu',
                'price' => 95.00,
                'stock' => 150,
                'units_on_display' => 5,
                'units_sponsored' => 12,
                'units_damaged' => 3,
                'units_consumed' => 0,
                'department' => $dcc,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 24,
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 55.00, 'moq' => 36, 'lead' => 7, 'default' => true]
                ]
            ],
            [
                'sku' => 'TS-CTN-WHT',
                'name' => 'White Cotton T-Shirt',
                'description' => 'Premium 100% cotton white t-shirt for DTF or vinyl printing.',
                'brand' => 'BlueCorner',
                'price' => 180.00,
                'stock' => 200,
                'units_on_display' => 4,
                'units_sponsored' => 22,
                'units_damaged' => 6,
                'units_consumed' => 0,
                'department' => $dcc,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 50,
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 110.00, 'moq' => 20, 'lead' => 5, 'default' => true]
                ]
            ],
            // Polo shirts — the newest shape in the studio, and the one most
            // often ordered as uniform: a chest crest on the front, a batch or
            // department name across the back. Both panels are printable, which
            // is why these are seeded as a pair rather than one sample: the
            // white is the sublimation blank, the navy the dyed stock that only
            // takes a light-coloured print.
            [
                'sku' => 'PL-PQE-WHT',
                'name' => 'White Piqué Polo Shirt',
                'description' => 'Classic-fit cotton piqué polo with a ribbed collar, blank for sublimation or DTF printing.',
                'brand' => 'BlueCorner',
                'price' => 320.00,
                'stock' => 120,
                'units_on_display' => 4,
                'units_sponsored' => 8,
                'units_damaged' => 2,
                'units_consumed' => 0,
                'department' => $dcc,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 30,
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 195.00, 'moq' => 24, 'lead' => 7, 'default' => true],
                    ['id' => $techSupply->supplier_id, 'cost' => 210.00, 'moq' => 12, 'lead' => 3, 'default' => false]
                ]
            ],
            [
                'sku' => 'PL-PQE-NVY',
                'name' => 'Navy Piqué Polo Shirt',
                'description' => 'Classic-fit cotton piqué polo in navy, for vinyl or embroidered chest and back prints.',
                'brand' => 'BlueCorner',
                'price' => 340.00,
                'stock' => 90,
                'units_on_display' => 3,
                'units_sponsored' => 0,
                'units_damaged' => 1,
                'units_consumed' => 0,
                'department' => $dcc,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 30,
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 205.00, 'moq' => 24, 'lead' => 7, 'default' => true]
                ]
            ],
            // Book Production output.
            [
                'sku' => 'BK-BKLT-A5',
                'name' => 'A5 Saddle-Stitched Booklet (24pp)',
                'description' => 'Twenty-four page A5 booklet with a vellum board cover.',
                'brand' => 'FabLab',
                'price' => 120.00,
                'stock' => 85,
                'units_on_display' => 3,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'department' => $book,
                'category_id' => $finishedGoodsId,
                'unit' => 'pcs',
                'low_stock_threshold' => 20,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 62.00, 'moq' => 25, 'lead' => 4, 'default' => true]
                ]
            ],
            // Out of stock — needs an urgent reorder. Not customizable for the
            // same reason as the lace: no plaque model in the studio.
            [
                'sku' => 'WD-PLQ-OAK',
                'name' => 'Engraved Oak Plaque',
                'description' => 'Laser-engraved solid oak recognition plaque with gloss finish.',
                'brand' => 'FabLab',
                'price' => 1450.00,
                'stock' => 0,
                'units_on_display' => 2,
                'units_sponsored' => 0,
                'units_damaged' => 1,
                'units_consumed' => 0,
                'department' => $wood,
                'category_id' => $finishedGoodsId,
                'unit' => 'pcs',
                'low_stock_threshold' => 5,
                'is_customizable' => false,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $ecoMaterials->supplier_id, 'cost' => 890.00, 'moq' => 5, 'lead' => 10, 'default' => true]
                ]
            ],
            // The Pine Study Stool used to sit here. It was cut from plywood,
            // which the shop doesn't stock, so both went at once.
            // Asset rather than stock-for-sale.
            [
                'sku' => 'MC-LSR-6090',
                'name' => 'CO2 Laser Cutter 60w',
                'description' => 'Industrial grade laser cutter for engraving and wood cutting.',
                'brand' => 'ThunderLaser',
                'price' => 0.00,
                'stock' => 2,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'department' => $dcc,
                'category_id' => $machineryId,
                'unit' => 'set',
                'low_stock_threshold' => 1,
                'status' => 'functional',
                'suppliers' => [
                    ['id' => $techSupply->supplier_id, 'cost' => 150000.00, 'moq' => 1, 'lead' => 30, 'default' => true]
                ]
            ],
        ];

        foreach ($products as $productData) {
            $suppliersData = $productData['suppliers'];
            unset($productData['suppliers']); // Remove from Main Product Data

            $product = Product::create($productData);

            // Attach Suppliers
            foreach ($suppliersData as $supplier) {
                $product->suppliers()->attach($supplier['id'], [
                    'cost' => $supplier['cost'],
                    'min_order_qty' => $supplier['moq'],
                    'lead_time_days' => $supplier['lead'],
                    'is_default' => $supplier['default']
                ]);
            }
        }
    }
}
