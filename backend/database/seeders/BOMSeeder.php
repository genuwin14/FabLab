<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\RawMaterial;

/**
 * Bills of materials — what each product is made of, and how much of it.
 *
 * This is the link that makes a raw material mean something: approving an
 * order for 40 ID laces walks this table and draws 40 clips, 36 metres of
 * strap and 40 card holders off the shelf, each through the usage ledger.
 *
 * A line can also be marked as spent only when there is something to print.
 * Transfer paper decorates an item rather than makes it, so a plain white mug
 * ordered straight off the shop page draws none — see PRINT_CONSUMABLES below
 * and the requires_design column it sets.
 *
 * Where the ink lives depends on whether the customer chose what goes on the
 * item:
 *
 *   - A customizable product has no ink here at all. What is printed on it is
 *     whatever the customer built in the studio, so its ink is billed per
 *     element by CustomizationBOMSeeder. A fixed split on the product was
 *     charging every design for black whether or not its artwork used any.
 *   - Everything else keeps its ink here, because the print is part of making
 *     the thing and nobody chose it: an ID lace comes printed or it isn't a
 *     lace.
 */
class BOMSeeder extends Seeder
{
    /**
     * Split a print's total ink across the four bottles, in millilitres.
     *
     * Not an even quarter each: the shop's artwork leans on cyan and magenta,
     * uses a little less yellow and least black. That's what makes one colour
     * reach its reorder point well before the others — which is the whole
     * reason the inks are stocked separately rather than as a set.
     */
    private static function ink(float $millilitres): array
    {
        return [
            'Sublimation Ink (Cyan)' => round($millilitres * 0.30, 2),
            'Sublimation Ink (Magenta)' => round($millilitres * 0.30, 2),
            'Sublimation Ink (Yellow)' => round($millilitres * 0.25, 2),
            'Sublimation Ink (Black)' => round($millilitres * 0.15, 2),
        ];
    }

    /**
     * The materials that pay for a print rather than for the item.
     *
     * The blank mug is the product, and it leaves finished-goods stock however
     * it is ordered. The sheet and the ink it is decorated with should not:
     * ten plain mugs used to take ten sheets of transfer paper for ten prints
     * that never happened.
     *
     * Only on a product a customer can actually design, though. An ID lace is
     * printed as part of being made — there is no such thing as a blank one to
     * order — so its sheet and ink are drawn unconditionally, the same as its
     * clip and its strap. Flagging them there would mean a lace never drew any
     * ink at all.
     *
     * Everything else on a recipe — the metal clip, the oak plank, the bond
     * paper inside a booklet — is the item itself and is always drawn.
     */
    private const PRINT_CONSUMABLES = [
        'Sublimation Transfer Paper (A4)',
        // No customizable product carries these any more, so in practice only
        // the paper above is ever flagged. They stay listed because the rule is
        // "a print consumable on a designable product waits for a design", and
        // that should hold if ink is ever put back on one.
        'Sublimation Ink (Cyan)',
        'Sublimation Ink (Magenta)',
        'Sublimation Ink (Yellow)',
        'Sublimation Ink (Black)',
    ];

    public function run(): void
    {
        // quantity_required is per single unit of the product.
        $recipes = [
            'IDL-LACE-STD' => [
                'Lanyard Metal Clip' => 1,
                'Woven Polyester Strap (16mm)' => 0.9,
                'PVC ID Card Holder' => 1,
                'Sublimation Transfer Paper (A4)' => 1,
                ...self::ink(2),
            ],
            // The seven customizable products below carry no ink of their own.
            // What gets printed on them is whatever the customer put in the
            // studio, so their ink comes entirely from the customization BOM —
            // see CustomizationBOMSeeder. A default split here was charging a
            // design for black it may never use.
            //
            // The sheets stay, because how many a print takes is a property of
            // the garment rather than of the artwork: a mug takes one whatever
            // is on it, a polo takes three. They are flagged below so a plain
            // order still draws none.
            'MG-WHT-11' => [
                'Sublimation Transfer Paper (A4)' => 1,
            ],
            'MG-BLK-11' => [
                'Sublimation Transfer Paper (A4)' => 1,
            ],
            'TS-CTN-WHT' => [
                'Sublimation Transfer Paper (A4)' => 2,
            ],
            // Three sheets rather than the tee's two: the polo is printed front
            // and back, and the collar takes a sheet of its own.
            'PL-PQE-WHT' => [
                'Sublimation Transfer Paper (A4)' => 3,
            ],
            'PL-PQE-NVY' => [
                'Sublimation Transfer Paper (A4)' => 3,
            ],
            // One canopy print, but a big one: the dome is a single panel
            // wider than any garment here, so it takes four sheets.
            'UMB-AUT-WHT' => [
                'Sublimation Transfer Paper (A4)' => 4,
            ],
            // Two, one per printable panel, now that the tote unwraps front and
            // back — see models/bag.js.
            'TB-CVS-NAT' => [
                'Sublimation Transfer Paper (A4)' => 2,
            ],
            'BK-BKLT-A5' => [
                'Bond Paper (A4, 80gsm)' => 6,   // 24 pages, 4 up per sheet
                'Vellum Board (220gsm)' => 1,
            ],
            'WD-PLQ-OAK' => [
                'Solid Oak Wood Planks' => 0.5,
                'Wood Varnish (Gloss)' => 0.05,
            ],
        ];

        $materials = RawMaterial::all()->keyBy('name');

        foreach ($recipes as $sku => $components) {
            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                continue;
            }

            $attach = [];
            foreach ($components as $name => $quantity) {
                if ($material = $materials->get($name)) {
                    $attach[$material->raw_material_id] = [
                        'quantity_required' => $quantity,
                        // The distinction only exists where an order can arrive
                        // without a design. On everything else the print is
                        // part of making the thing.
                        'requires_design' => $product->is_customizable
                            && in_array($name, self::PRINT_CONSUMABLES, true),
                    ];
                }
            }

            if ($attach !== []) {
                $product->rawMaterials()->sync($attach);
            }
        }
    }
}
