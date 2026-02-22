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

        // Get Suppliers
        $suppliers = Supplier::all();
        $techSupply = $suppliers->firstWhere('name', 'Tech Supply Co.');
        $genMerch = $suppliers->firstWhere('name', 'General Merchandise Inc.');
        $ecoMaterials = $suppliers->firstWhere('name', 'Eco-Materials Ltd.');

        $products = [
            // Scenario 1: Healthy Stock, Single Supplier (Eco-Materials)
            [
                'sku' => 'RW-PLY-001',
                'name' => 'Plywood 4x8 1/4 inch',
                'description' => 'Standard marine plywood for laser cutting projects.',
                'brand' => 'Eco-Wood',
                'price' => 450.00,
                'stock' => 50,
                'category_id' => $rawMaterialsId,
                'unit' => 'pcs',
                'low_stock_threshold' => 10,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $ecoMaterials->supplier_id, 'cost' => 380.00, 'moq' => 10, 'lead' => 3, 'default' => true]
                ]
            ],
            // Scenario 2: Healthy Stock (Customizable), Multiple Suppliers
            [
                'sku' => 'MG-WHT-11',
                'name' => 'White Ceramic Mug 11oz',
                'description' => 'Blank white mugs for sublimation printing.',
                'brand' => 'Yiwu',
                'price' => 85.00,
                'stock' => 100, // Healthy stock for easy testing
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
            // Scenario 3: Asset/Machine, Single Supplier
            [
                'sku' => 'MC-LSR-6090',
                'name' => 'CO2 Laser Cutter 60w',
                'description' => 'Industrial grade laser cutter for engraving and wood cutting.',
                'brand' => 'ThunderLaser',
                'price' => 0.00, // Asset
                'stock' => 2,
                'category_id' => $machineryId,
                'unit' => 'set',
                'low_stock_threshold' => 1,
                'status' => 'functional',
                'suppliers' => [
                    ['id' => $techSupply->supplier_id, 'cost' => 150000.00, 'moq' => 1, 'lead' => 30, 'default' => true]
                ]
            ],
            // Scenario 4: Out of Stock! (Needs urgent reorder)
            [
                'sku' => 'RW-FIL-PLA-RED',
                'name' => 'PLA Filament Red 1.75mm',
                'description' => 'High quality PLA for 3D printing.',
                'brand' => 'eSun',
                'price' => 950.00,
                'stock' => 0, // Out of stock!
                'category_id' => $rawMaterialsId,
                'unit' => 'roll',
                'low_stock_threshold' => 5,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $techSupply->supplier_id, 'cost' => 600.00, 'moq' => 5, 'lead' => 5, 'default' => true]
                ]
            ],
            // Scenario 5: Product with NO Supplier (Test warning in monitoring)
            [
                'sku' => 'RW-ACR-CLR-3MM',
                'name' => 'Acrylic Sheet Clear 3mm',
                'description' => 'Cast acrylic sheet 4x8 ft.',
                'brand' => 'Sunlight',
                'price' => 2500.00,
                'stock' => 3, // Low stock (Threshold 5)
                'category_id' => $rawMaterialsId,
                'unit' => 'pcs',
                'low_stock_threshold' => 5,
                'status' => 'active',
                'suppliers' => [] // No suppliers assigned yet
            ],
            // Scenario 6: Healthy Stock (Customizable)
            [
                'sku' => 'MG-BLK-11',
                'name' => 'Black Ceramic Mug 11oz',
                'description' => 'Inner color black sublimation mug.',
                'brand' => 'Yiwu',
                'price' => 95.00,
                'stock' => 150,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 24,
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 55.00, 'moq' => 36, 'lead' => 7, 'default' => true]
                ]
            ],
            // Scenario 7: Customizable T-Shirt
            [
                'sku' => 'TS-CTN-WHT',
                'name' => 'White Cotton T-Shirt',
                'description' => 'Premium 100% cotton white t-shirt for DTG or vinyl printing.',
                'brand' => 'BlueCorner',
                'price' => 180.00,
                'stock' => 200,
                'category_id' => $merchandiseId,
                'unit' => 'pcs',
                'low_stock_threshold' => 50,
                'is_customizable' => true,
                'status' => 'active',
                'suppliers' => [
                    ['id' => $genMerch->supplier_id, 'cost' => 110.00, 'moq' => 20, 'lead' => 5, 'default' => true]
                ]
            ]
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
