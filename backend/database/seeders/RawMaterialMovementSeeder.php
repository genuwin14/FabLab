<?php

namespace Database\Seeders;

use App\Enums\StockMovementReason;
use App\Models\RawMaterial;
use App\Models\User;
use App\Services\RawMaterialStockService;
use Illuminate\Database\Seeder;

/**
 * Seeds the usage ledger with a plausible few weeks of shop floor activity.
 *
 * Every row goes through RawMaterialStockService rather than being written
 * straight to the table, so the demo data obeys the same rule as the live app:
 * stock, the four report counters and the Usage Log are all produced by the
 * same movements and cannot disagree. Seeding `units_consumed => 35` by hand —
 * as this project used to — is exactly the drift the ledger exists to stop.
 */
class RawMaterialMovementSeeder extends Seeder
{
    public function run(RawMaterialStockService $stock): void
    {
        $staff = User::where('role', 'staff')->first() ?? User::where('role', 'admin')->first();
        $materials = RawMaterial::all()->keyBy('name');

        // [material, reason, quantity, note]
        $movements = [
            // A run of 180 ID laces for the College of Education.
            ['Lanyard Metal Clip', StockMovementReason::Consumed, 180, 'Batch of 180 ID laces — College of Education'],
            ['Woven Polyester Strap (16mm)', StockMovementReason::Consumed, 162, 'Batch of 180 ID laces — College of Education'],
            ['PVC ID Card Holder', StockMovementReason::Consumed, 180, 'Batch of 180 ID laces — College of Education'],
            ['Sublimation Transfer Paper (A4)', StockMovementReason::Consumed, 180, 'Strap printing — College of Education'],
            ['Lanyard Metal Clip', StockMovementReason::Damaged, 12, 'Crimped badly on the lanyard press'],
            ['PVC ID Card Holder', StockMovementReason::OnDisplay, 10, 'Sample board at the front desk'],

            // Mug and shirt printing.
            ['Sublimation Transfer Paper (A4)', StockMovementReason::Consumed, 220, 'Mug and shirt orders'],
            ['Sublimation Transfer Paper (A4)', StockMovementReason::Damaged, 25, 'Misfeed on the L1800'],
            ['Sublimation Ink (CMYK Set)', StockMovementReason::Consumed, 3, 'Mug and shirt printing — semester run'],
            ['Sublimation Ink (CMYK Set)', StockMovementReason::Damaged, 1, 'Magenta bottle split in storage'],

            // Book Production.
            ['Bond Paper (A4, 80gsm)', StockMovementReason::Consumed, 2400, 'Booklet run — 400 copies'],
            ['Vellum Board (220gsm)', StockMovementReason::Consumed, 410, 'Booklet covers'],
            ['Vellum Board (220gsm)', StockMovementReason::Sponsored, 50, 'Donated to the campus paper'],

            // Woodworks.
            ['Solid Oak Wood Planks', StockMovementReason::Consumed, 88, 'Recognition plaques — recognition day'],
            ['Solid Oak Wood Planks', StockMovementReason::Damaged, 12, 'Split along the grain during planing'],
            ['Wood Varnish (Gloss)', StockMovementReason::Consumed, 2, 'Finishing the plaque batch'],

            // Uncategorised material.
            ['Acrylic Sheet Clear 3mm', StockMovementReason::OnDisplay, 1, 'Cut sample in the display case'],
        ];

        foreach ($movements as [$name, $reason, $quantity, $note]) {
            $material = $materials->get($name);

            if (! $material) {
                continue;
            }

            $stock->record($material->refresh(), $reason, $quantity, [
                'user_id' => $staff?->id,
                'note' => $note,
            ]);
        }
    }
}
