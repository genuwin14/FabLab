<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RawMaterial;
use App\Models\Supplier;

/**
 * The components the FabLab consumes making things — the metal clip on an ID
 * lace, the bond paper inside a booklet, the plank a plaque is cut from.
 *
 * Two rules this file follows on purpose:
 *
 * 1. Nothing here is also a product. Filament and acrylic used to be seeded on
 *    both screens with two stock figures that never agreed.
 * 2. No `units_consumed` / `units_damaged` / `units_sponsored` /
 *    `units_on_display` values. Those counters belong to the usage ledger and
 *    every material starts at zero on all four; RawMaterialMovementSeeder then
 *    records real movements so the counters and the Usage Log always agree.
 */
class RawMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $techSupplier = Supplier::where('name', 'Tech Supply Co.')->first();
        $genSupplier = Supplier::where('name', 'General Merchandise Inc.')->first();
        $ecoSupplier = Supplier::where('name', 'Eco-Materials Ltd.')->first();

        $dcc = \App\Enums\Department::DigitalCustomizationCenter->value;
        $book = \App\Enums\Department::BookProduction->value;
        $wood = \App\Enums\Department::Woodworks->value;

        $materials = [
            // ---- Digital Customization Center: the ID lace components ----
            [
                'name' => 'Lanyard Metal Clip',
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 6.50,
                'stock_quantity' => 1200,
                'low_stock_threshold' => 200,
                'unit' => 'pcs',
                'description' => 'Swivel hook clip crimped to the end of an ID lace.',
                'department' => $dcc,
            ],
            [
                'name' => 'Woven Polyester Strap (16mm)',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 12.00,
                'stock_quantity' => 850,
                'low_stock_threshold' => 150,
                'unit' => 'meter',
                'description' => 'Sublimation-ready lanyard webbing, 16mm wide.',
                'department' => $dcc,
            ],
            [
                'name' => 'PVC ID Card Holder',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 9.00,
                'stock_quantity' => 940,
                'low_stock_threshold' => 200,
                'unit' => 'pcs',
                'description' => 'Clear vertical card sleeve that hangs from the clip.',
                'department' => $dcc,
            ],
            [
                'name' => 'Sublimation Transfer Paper (A4)',
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 4.50,
                'stock_quantity' => 3000,
                'low_stock_threshold' => 400,
                'unit' => 'pcs',
                'description' => 'Heat transfer paper for mugs, shirts and lanyard straps.',
                'department' => $dcc,
            ],
            // The four sublimation inks, stocked and reordered per colour
            // rather than as a set — a print uses a different amount of each,
            // so they empty at different rates and only the short one needs
            // buying. Each starts at 800ml; the usage ledger then draws them
            // down by different amounts, leaving magenta under its threshold
            // and the other three comfortable.
            ...array_map(fn (string $colour) => [
                'name' => "Sublimation Ink ({$colour})",
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 4.50,
                'stock_quantity' => 800,
                'low_stock_threshold' => 200,
                'unit' => 'ml',
                'description' => "Dye-sublimation {$colour} ink for the Epson L1800.",
                'department' => $dcc,
            ], ['Cyan', 'Magenta', 'Yellow', 'Black']),
            [
                'name' => 'PLA Filament Red 1.75mm',
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 600.00,
                // Out of stock — drives the reorder prompts on the dashboard.
                'stock_quantity' => 0,
                'low_stock_threshold' => 5,
                'unit' => 'roll',
                'description' => 'High quality PLA for the Creality 3D printers.',
                'department' => $dcc,
            ],

            // ---- Book Production ----
            [
                'name' => 'Bond Paper (A4, 80gsm)',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 0.80,
                'stock_quantity' => 12000,
                'low_stock_threshold' => 2000,
                'unit' => 'pcs',
                'description' => 'Standard bond paper for booklet pages.',
                'department' => $book,
            ],
            [
                'name' => 'Vellum Board (220gsm)',
                'supplier_id' => $genSupplier->supplier_id ?? 2,
                'cost_per_unit' => 22.00,
                'stock_quantity' => 1590,
                'low_stock_threshold' => 300,
                'unit' => 'pcs',
                'description' => 'Cover stock for saddle-stitched booklets.',
                'department' => $book,
            ],

            // ---- Woodworks ----
            // No plywood: the shop doesn't stock it.
            [
                'name' => 'Solid Oak Wood Planks',
                'supplier_id' => $ecoSupplier->supplier_id ?? 3,
                'cost_per_unit' => 1200.00,
                'stock_quantity' => 200,
                'low_stock_threshold' => 40,
                'unit' => 'pcs',
                'description' => 'Durable oak stock for engraved plaques and framing.',
                'department' => $wood,
            ],
            [
                'name' => 'Wood Varnish (Gloss)',
                'supplier_id' => $ecoSupplier->supplier_id ?? 3,
                'cost_per_unit' => 450.00,
                // Low stock — sits under its threshold to exercise the alerts.
                'stock_quantity' => 6,
                'low_stock_threshold' => 10,
                'unit' => 'liter',
                'description' => 'Clear gloss finish for engraved woodwork.',
                'department' => $wood,
            ],

            // ---- Deliberately unassigned: fills the report's Uncategorized section ----
            [
                'name' => 'Acrylic Sheet Clear 3mm',
                'supplier_id' => $techSupplier->supplier_id ?? 1,
                'cost_per_unit' => 2500.00,
                'stock_quantity' => 3,
                'low_stock_threshold' => 5,
                'unit' => 'pcs',
                'description' => 'Cast acrylic sheet 4x8 ft for laser-cut signage.',
                // No department assigned — demonstrates "Uncategorized" in the report.
            ],
        ];

        foreach ($materials as $material) {
            RawMaterial::create($material);
        }
    }
}
