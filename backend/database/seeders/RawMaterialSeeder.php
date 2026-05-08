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
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 5,
                'units_consumed' => 35,
                'low_stock_threshold' => 20,
                'unit' => 'meter',
                'description' => 'High quality synthetic leather for upholstery.',
                'department' => \App\Enums\Department::Woodworks->value,
            ],
            [
                'name' => 'High-Density Foam (4-inch)',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 850.00,
                'stock_quantity' => 50,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 18,
                'low_stock_threshold' => 10,
                'unit' => 'pcs',
                'description' => 'Standard foam for sofa cushions.',
                'department' => \App\Enums\Department::Woodworks->value,
            ],
            [
                'name' => 'Solid Oak Wood Planks',
                'supplier_id' => $ecoSupplier->supplier_id ?? 3,
                'cost_per_unit' => 1200.00,
                'stock_quantity' => 200,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 12,
                'units_consumed' => 88,
                'low_stock_threshold' => 40,
                'unit' => 'pcs',
                'description' => 'Durable oak wood for furniture framing.',
                'department' => \App\Enums\Department::Woodworks->value,
            ],
            [
                'name' => 'Steel Springs (Heavy Duty)',
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 45.00,
                'stock_quantity' => 500,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 25,
                'units_consumed' => 75,
                'low_stock_threshold' => 100,
                'unit' => 'pcs',
                'description' => 'Industrial grade springs for seating support.',
                'department' => \App\Enums\Department::Woodworks->value,
            ],
            [
                'name' => 'Cotton Fabric (Beige)',
                'supplier_id' => $ecoSupplier->supplier_id ?? 3,
                'cost_per_unit' => 150.00,
                'stock_quantity' => 300,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 50,
                'unit' => 'meter',
                'description' => 'Breathable cotton fabric for cushions.',
                // No department assigned — demonstrates "Uncategorized" section in report.
            ],
            [
                'name' => 'Bond Paper (A4, 80gsm)',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 280.00,
                'stock_quantity' => 2000,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 200,
                'unit' => 'pcs',
                'description' => 'Standard bond paper for book pages.',
                'department' => \App\Enums\Department::BookProduction->value,
            ],
            [
                'name' => 'Vellum Board (220gsm)',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 22.00,
                'stock_quantity' => 1590,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 410,
                'low_stock_threshold' => 100,
                'unit' => 'pcs',
                'description' => 'Cover stock for booklet printing.',
                'department' => \App\Enums\Department::BookProduction->value,
            ],
        ];

        foreach ($materials as $material) {
            RawMaterial::create($material);
        }
    }
}
