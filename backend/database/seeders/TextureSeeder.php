<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Texture;
use App\Models\Supplier;

class TextureSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $techSupply = $suppliers->firstWhere('name', 'Tech Supply Co.');
        $genMerch = $suppliers->firstWhere('name', 'General Merchandise Inc.');
        $ecoMaterials = $suppliers->firstWhere('name', 'Eco-Materials Ltd.');

        $textures = [
            // Healthy stock, standard pricing
            [
                'name' => 'Smooth Black Leather',
                'description' => 'Matte black leather texture with fine grain.',
                'supplier_id' => $ecoMaterials?->supplier_id,
                'cost_per_unit' => 120.00,
                'stock_quantity' => 80,
                'units_on_display' => 2,
                'units_sponsored' => 0,
                'units_damaged' => 1,
                'units_consumed' => 17,
                'low_stock_threshold' => 15,
                'unit' => 'm',
                'price_modifier' => 50.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            [
                'name' => 'Natural Oak Grain',
                'description' => 'Light oak wood pattern with visible rings.',
                'supplier_id' => $ecoMaterials?->supplier_id,
                'cost_per_unit' => 95.00,
                'stock_quantity' => 60,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 9,
                'low_stock_threshold' => 10,
                'unit' => 'm',
                'price_modifier' => 0,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Low stock — should appear in inventory alerts
            [
                'name' => 'Rustic Walnut',
                'description' => 'Dark walnut texture for a vintage look.',
                'supplier_id' => $ecoMaterials?->supplier_id,
                'cost_per_unit' => 130.00,
                'stock_quantity' => 8,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 2,
                'units_consumed' => 0,
                'low_stock_threshold' => 12,
                'unit' => 'm',
                'price_modifier' => 75.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Healthy fabric
            [
                'name' => 'Woven Fabric (Grey)',
                'description' => 'Coarse woven fabric pattern for modern sofas.',
                'supplier_id' => $genMerch?->supplier_id,
                'cost_per_unit' => 65.00,
                'stock_quantity' => 120,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 25,
                'unit' => 'm',
                'price_modifier' => 0,
                // No department — demonstrates "Uncategorized" section in report.
            ],
            // Premium / very low stock
            [
                'name' => 'Polished Chrome',
                'description' => 'Reflective metallic texture for chair legs.',
                'supplier_id' => $techSupply?->supplier_id,
                'cost_per_unit' => 220.00,
                'stock_quantity' => 4,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 10,
                'unit' => 'pcs',
                'price_modifier' => 100.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
        ];

        foreach ($textures as $texture) {
            Texture::create($texture);
        }
    }
}
