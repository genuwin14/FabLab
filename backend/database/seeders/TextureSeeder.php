<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Texture;
use App\Models\Supplier;

/**
 * Textures are named after their ambientCG asset ID — Fabric083, Leather037 —
 * because that is what identifies the material the images came from, and it
 * keeps the row and the downloaded folder obviously the same thing.
 *
 * Images are not seeded: upload the asset's `_Color` map through
 * Admin → Textures. Only the Color map, and it must be seamless — the
 * customizer tiles a texture across the model, so a plain photograph shows its
 * grid.
 *
 * The stock figures below are deliberately varied: some healthy, one under its
 * threshold to exercise low-stock alerts, and one with no department so the
 * materials report still has an "Uncategorized" section to show.
 */
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
                'name' => 'Leather037',
                'description' => 'Fine-grain leather. Seamless.',
                'supplier_id' => $ecoMaterials?->supplier_id,
                'cost_per_unit' => 120.00,
                'stock_quantity' => 80,
                'units_on_display' => 2,
                'units_sponsored' => 0,
                'units_damaged' => 1,
                'units_consumed' => 17,
                'low_stock_threshold' => 15,
                'unit' => 'meter',
                'price_modifier' => 50.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            [
                'name' => 'Fabric077',
                'description' => 'Plain woven cloth. Seamless.',
                'supplier_id' => $genMerch?->supplier_id,
                'cost_per_unit' => 95.00,
                'stock_quantity' => 60,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 9,
                'low_stock_threshold' => 10,
                'unit' => 'meter',
                'price_modifier' => 0,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Low stock — should appear in inventory alerts
            [
                'name' => 'Leather034C',
                'description' => 'Dark tooled leather. Seamless.',
                'supplier_id' => $ecoMaterials?->supplier_id,
                'cost_per_unit' => 130.00,
                'stock_quantity' => 8,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 2,
                'units_consumed' => 0,
                'low_stock_threshold' => 12,
                'unit' => 'meter',
                'price_modifier' => 75.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Healthy fabric
            [
                'name' => 'Fabric083',
                'description' => 'Checked cotton weave. Seamless.',
                'supplier_id' => $genMerch?->supplier_id,
                'cost_per_unit' => 65.00,
                'stock_quantity' => 120,
                'units_on_display' => 0,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 25,
                'unit' => 'meter',
                'price_modifier' => 0,
                // No department — demonstrates "Uncategorized" section in report.
            ],
            [
                'name' => 'Fabric080',
                'description' => 'Ribbed knit. Seamless.',
                'supplier_id' => $genMerch?->supplier_id,
                'cost_per_unit' => 78.00,
                'stock_quantity' => 95,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 4,
                'low_stock_threshold' => 20,
                'unit' => 'meter',
                'price_modifier' => 40.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
            // Premium / very low stock
            [
                'name' => 'Fabric062',
                'description' => 'Heavy canvas. Seamless.',
                'supplier_id' => $techSupply?->supplier_id,
                'cost_per_unit' => 220.00,
                'stock_quantity' => 4,
                'units_on_display' => 1,
                'units_sponsored' => 0,
                'units_damaged' => 0,
                'units_consumed' => 0,
                'low_stock_threshold' => 10,
                'unit' => 'meter',
                'price_modifier' => 100.00,
                'department' => \App\Enums\Department::DigitalCustomizationCenter->value,
            ],
        ];

        foreach ($textures as $texture) {
            Texture::create($texture);
        }
    }
}
