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
 * `customization_rates` says a line of text is ₱50. This says it is also a
 * quarter of a millilitre of black ink. Without the second half a design could
 * be charged for twelve lines of text and internal lighting while no ink and no
 * LED kit ever left the shelf — the fee was collected and the shelf never
 * moved.
 *
 * Deliberately partial, because both states are worth having in demo data:
 *
 *   - Text, shapes, images, lighting and the large size all draw something.
 *   - Small and medium draw nothing — they fit the same sheet as the blank,
 *     so there is nothing extra to deduct.
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
    /**
     * Split a print's total ink across the four bottles, in millilitres.
     *
     * The same weighting BOMSeeder uses for the products themselves, so a
     * design's extra ink empties the bottles in the same order the base print
     * does — magenta first — rather than flattening the difference the shop
     * stocks them separately for.
     *
     * @return array<string, float>
     */
    private static function ink(float $millilitres): array
    {
        return [
            'Sublimation Ink (Cyan)' => round($millilitres * 0.30, 4),
            'Sublimation Ink (Magenta)' => round($millilitres * 0.30, 4),
            'Sublimation Ink (Yellow)' => round($millilitres * 0.25, 4),
            'Sublimation Ink (Black)' => round($millilitres * 0.15, 4),
        ];
    }

    public function run(): void
    {
        $materials = RawMaterial::all()->keyBy('name');

        // Per one unit of the option: one line of text, one shape, one image
        // at 1x size, one lit item, one item in that size.
        $recipes = [
            // Text is set in a single flat colour and nearly always a dark one,
            // so it draws black alone rather than a four-colour split.
            'text' => ['Sublimation Ink (Black)' => 0.25],

            // A circle or a line is a flat fill, but it can be any colour.
            'shape' => self::ink(0.4),

            // An uploaded image is full colour and much the largest draw of the
            // three. This is the figure at 1x; the order multiplies it by the
            // Size slider, so a 5x print costs five times the ink, exactly as
            // it costs five times the fee.
            'logo' => self::ink(2.5),

            'led_lighting' => ['LED Light Kit (USB, Warm White)' => 1],

            // Small and medium fit the sheet the blank already uses. Large
            // doesn't, so it takes one more. Worth seeding even though the
            // size surcharge is ₱0 out of the box: an option can cost the shop
            // something while charging the customer nothing, and the report
            // should still see it.
            'size_large' => ['Sublimation Transfer Paper (A4)' => 1],
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
