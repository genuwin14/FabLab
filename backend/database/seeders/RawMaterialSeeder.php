<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RawMaterial;
use App\Models\Supplier;

class RawMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $techSupplier = Supplier::where('name', 'Tech Supply Co.')->first();
        $genSupplier = Supplier::where('name', 'General Merchandise Inc.')->first();
        $ecoSupplier = Supplier::where('name', 'Eco-Materials Ltd.')->first();

        $materials = [
            [
                'name' => 'Premium Leather (Black)',
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 450.00,
                'stock_quantity' => 100,
                'low_stock_threshold' => 20,
                'unit' => 'meter',
                'description' => 'High quality synthetic leather for upholstery.'
            ],
            [
                'name' => 'High-Density Foam (4-inch)',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 850.00,
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'unit' => 'pcs',
                'description' => 'Standard foam for sofa cushions.'
            ],
            [
                'name' => 'Solid Oak Wood Planks',
                'supplier_id' => $ecoSupplier->supplier_id ?? 3,
                'cost_per_unit' => 1200.00,
                'stock_quantity' => 200,
                'low_stock_threshold' => 40,
                'unit' => 'pcs',
                'description' => 'Durable oak wood for furniture framing.'
            ],
            [
                'name' => 'Steel Springs (Heavy Duty)',
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 45.00,
                'stock_quantity' => 500,
                'low_stock_threshold' => 100,
                'unit' => 'pcs',
                'description' => 'Industrial grade springs for seating support.'
            ],
            [
                'name' => 'Cotton Fabric (Beige)',
                'supplier_id' => $ecoSupplier->supplier_id ?? 3,
                'cost_per_unit' => 150.00,
                'stock_quantity' => 300,
                'low_stock_threshold' => 50,
                'unit' => 'meter',
                'description' => 'Breathable cotton fabric for cushions.'
            ],
        ];

        foreach ($materials as $material) {
            RawMaterial::create($material);
        }
    }
}
