<?php

namespace Database\Seeders;

use App\Models\InkChannel;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

/**
 * Point each of the printer's four channels at the bottle it empties.
 *
 * The migration that creates the channels already tries this by name, but it
 * runs before RawMaterialSeeder on a fresh database and so finds nothing to
 * link. This does the same lookup once the inks exist, so a rebuilt demo
 * measures ink the moment it is seeded rather than after someone visits the
 * pricing screen.
 *
 * The rate is left at the shipped default. It is a property of the printer,
 * to be calibrated at the bench, and the demo has no better figure.
 */
class InkChannelSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(InkChannel::CHANNELS) as $channel) {
            $material = RawMaterial::where('name', 'Sublimation Ink (' . ucfirst($channel) . ')')->first();

            InkChannel::updateOrCreate(
                ['channel' => $channel],
                [
                    'raw_material_id' => $material?->raw_material_id,
                    'ml_per_cm2' => InkChannel::rates()[$channel]['ml_per_cm2'],
                ],
            );
        }

        InkChannel::flushCache();
    }
}
