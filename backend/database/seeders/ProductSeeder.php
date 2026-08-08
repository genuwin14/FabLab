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
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $finishedGoodsId = Category::where('name', 'Finished Goods')->first()->category_id;
        $machineryId = Category::where('name', 'Machinery & Equipment')->first()->category_id;
        $merchandiseId = Category::where('name', 'Merchandise')->first()->category_id;
        $furnitureId = Category::where('name', 'Furniture')->first()->category_id;

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
                'is_customizable' => true,
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
            // Out of stock — needs an urgent reorder.
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
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $ecoMaterials->supplier_id, 'cost' => 890.00, 'moq' => 5, 'lead' => 10, 'default' => true]
                ]
            ],
            // Low stock AND no supplier attached — exercises the monitoring warning.
            [
                'sku' => 'WD-STL-PINE',
                'name' => 'Pine Study Stool',
                'description' => 'Flat-pack stool cut on the laser bed and hand-finished.',
                'brand' => 'FabLab',
                'price' => 1650.00,
                'stock' => 3,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                // No department assigned — demonstrates "Uncategorized" in the report.
                'category_id' => $furnitureId,
                'unit' => 'pcs',
                'low_stock_threshold' => 5,
                'status' => 'active',
                'suppliers' => [] // No suppliers assigned yet
            ],
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
