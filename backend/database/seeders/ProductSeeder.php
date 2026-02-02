<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $rawMaterialsId = Category::where('name', 'Raw Materials')->first()->category_id;
        $machineryId = Category::where('name', 'Machinery & Equipment')->first()->category_id;
        $merchandiseId = Category::where('name', 'Merchandise')->first()->category_id;

        // Get supplier IDs
        $techSupplyId = Supplier::where('name', 'Tech Supply Co.')->first()->supplier_id;
        $genMerchId = Supplier::where('name', 'General Merchandise Inc.')->first()->supplier_id;

        $products = [
            [
                'sku' => 'RW-PLY-001',
                'name' => 'Plywood 4x8 1/4 inch',
                'description' => 'Standard marine plywood for laser cutting projects.',
                'brand' => 'Eco-Wood',
                'price' => 450.00,
                'cost' => 380.00,
                'stock' => 50,
                'category_id' => $rawMaterialsId,
                'supplier_id' => $genMerchId,
                'unit' => 'pcs',
                'low_stock_threshold' => 10,
                'status' => 'active'
            ],
            [
                'sku' => 'MC-LSR-6090',
                'name' => 'CO2 Laser Cutter 60w',
                'description' => 'Industrial grade laser cutter for engraving and wood cutting.',
                'brand' => 'ThunderLaser',
                'price' => 0.00, // Asset
                'cost' => 125000.00,
                'stock' => 2,
                'category_id' => $machineryId,
                'supplier_id' => $techSupplyId,
                'unit' => 'set',
                'low_stock_threshold' => 1,
                'status' => 'functional'
            ],
            [
                'sku' => 'MG-WHT-11',
                'name' => 'White Ceramic Mug 11oz',
                'description' => 'Blank white mugs for sublimation printing.',
                'brand' => 'Yiwu',
                'price' => 85.00,
                'cost' => 45.00,
                'stock' => 100,
                'category_id' => $merchandiseId,
                'supplier_id' => $genMerchId,
                'unit' => 'pcs',
                'low_stock_threshold' => 24,
                'is_customizable' => true,
                'status' => 'active'
            ],
            [
                'sku' => 'RW-FIL-PLA-RED',
                'name' => 'PLA Filament Red 1.75mm',
                'description' => 'High quality PLA for 3D printing.',
                'brand' => 'eSun',
                'price' => 950.00,
                'cost' => 750.00,
                'stock' => 15,
                'category_id' => $rawMaterialsId,
                'supplier_id' => $techSupplyId,
                'unit' => 'roll',
                'low_stock_threshold' => 5,
                'status' => 'active'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
