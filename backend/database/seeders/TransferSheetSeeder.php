<?php

namespace Database\Seeders;

use App\Models\RawMaterial;
use App\Models\TransferSheet;
use Illuminate\Database\Seeder;

/**
 * Point the transfer sheet at the paper it is stocked as.
 *
 * Same reason InkChannelSeeder exists: the migration that creates the sheet
 * links it by name, but on a fresh database it runs before the paper has
 * been seeded and finds nothing. This does the lookup once the paper is
 * there, so a rebuilt demo measures paper the moment it is seeded.
 */
class TransferSheetSeeder extends Seeder
{
    public function run(): void
    {
        $paper = RawMaterial::where('name', 'Sublimation Transfer Paper (A4)')->first();

        TransferSheet::updateOrCreate(
            ['name' => 'A4'],
            [
                'raw_material_id' => $paper?->raw_material_id,
                'width_cm' => TransferSheet::A4_WIDTH_CM,
                'height_cm' => TransferSheet::A4_HEIGHT_CM,
                'margin_cm' => TransferSheet::current()['margin_cm'],
            ],
        );

        TransferSheet::flushCache();
    }
}
