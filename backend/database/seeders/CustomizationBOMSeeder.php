<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\CustomizationRate;
use App\Models\CustomizationRateMaterial;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

/**
 * What the customizer's options cost the shop, as opposed to what they charge.
 *
 * `customization_rates` says internal lighting is ₱500. This says it is also
 * an LED kit. Without the second half a design could be charged for lighting
 * while no kit ever left the shelf — the fee was collected and the shelf never
 * moved.
 *
 * Ink is deliberately *not* here. Text, shapes and images used to carry a
 * fixed millilitre figure each, which is a bill of materials guessing at a
 * picture: red text was charged black, an all-yellow logo was charged cyan.
 * A design's ink is now measured off the print the studio exports (see
 * InkEstimator and InkChannelSeeder), so mapping ink to an element here
 * would only be skipped by the order for every product with a print area.
 *
 * Deliberately partial, because both states are worth having in demo data:
 *
 *   - Lighting draws something: the kit.
 *   - Text, shapes and images draw nothing of their own — their ink is
 *     measured, and they take nothing else.
 *   - No size draws anything. The transfer paper a print takes is measured
 *     off the print too, and a cut transfer is the same size on a Small
 *     and a 5XL.
 *   - The four free house colours draw nothing either. The blank garment
 *     already *is* white, black, grey or navy; only the paid finishes are
 *     actually dyed, which is what their surcharge pays for.
 *
 * Textures are left alone on purpose. They already carry stock of their own
 * and move it when one is ordered, so in this shop's model a texture is the
 * printed sheet rather than something printed onto a consumable. The link
 * exists for shops that work the other way; seeding it here would suggest a
 * second draw that this catalogue doesn't have.
 */
class CustomizationBOMSeeder extends Seeder
{
    public function run(): void
    {
        $materials = RawMaterial::all()->keyBy('name');

        // Per one unit of the option: one lit item.
        //
        // No size draws anything. Transfer paper used to be seeded per
        // garment size — a sheet more at Large, three by 5XL — but the shop
        // presses a cut transfer, which is the same size on every garment,
        // and the paper is now measured off the print (see
        // InkEstimator::paper() and TransferSheetSeeder). A figure here would
        // be skipped for every product with a print area.
        $recipes = [
            'led_lighting' => ['LED Light Kit (USB, Warm White)' => 1],
        ];

        foreach ($recipes as $rateKey => $components) {
            foreach ($components as $name => $quantity) {
                if (! $material = $materials->get($name)) {
                    continue;
                }

                CustomizationRateMaterial::updateOrCreate(
                    ['rate_key' => $rateKey, 'raw_material_id' => $material->raw_material_id],
                    ['quantity_required' => $quantity],
                );
            }
        }

        // The paid half of the palette is dyed, and the dye is what the
        // surcharge pays for. Roughly proportional to that surcharge, so the
        // margin on each reads sensibly against its cost_per_unit.
        $finishes = [
            'Cherry Red' => 'Textile Spot Dye (Cherry Red)',
            'Forest Green' => 'Textile Spot Dye (Forest Green)',
            'Sunset Orange' => 'Textile Spot Dye (Sunset Orange)',
            'FABLAB Gold' => 'Textile Spot Dye (FABLAB Gold)',
        ];

        foreach ($finishes as $colorName => $materialName) {
            $color = Color::where('name', $colorName)->first();
            $material = $materials->get($materialName);

            if (! $color || ! $material) {
                continue;
            }

            $color->update([
                'raw_material_id' => $material->raw_material_id,
                'material_quantity' => 6,
            ]);
        }

        CustomizationRate::flushCache();
    }
}
