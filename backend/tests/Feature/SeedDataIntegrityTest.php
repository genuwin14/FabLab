<?php

namespace Tests\Feature;

use App\Enums\StockMovementReason;
use App\Models\Category;
use App\Models\Color;
use App\Models\CustomizationRate;
use App\Models\CustomizationRateMaterial;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Guards the two rules the demo data used to break.
 *
 * Raw materials were seeded twice — once in `raw_materials` and again as
 * products in a "Raw Materials" category — with two stock figures that drifted
 * apart. And the four report counters were typed as literals, so seeded data
 * showed consumption that had never happened.
 */
class SeedDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Notification::fake();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_there_is_no_raw_materials_product_category(): void
    {
        // Raw materials are not products. They live in `raw_materials`, are
        // drawn down by a bill of materials and restocked by purchase orders.
        $this->assertSame(0, Category::where('name', 'Raw Materials')->count());
    }

    public function test_no_raw_material_is_also_seeded_as_a_product(): void
    {
        $productNames = Product::pluck('name')->map(fn ($n) => strtolower($n));

        foreach (RawMaterial::pluck('name') as $materialName) {
            $this->assertFalse(
                $productNames->contains(strtolower($materialName)),
                "\"{$materialName}\" is seeded as both a raw material and a product."
            );
        }
    }

    public function test_every_report_counter_equals_its_ledger_total(): void
    {
        $buckets = [
            'units_consumed' => StockMovementReason::Consumed,
            'units_damaged' => StockMovementReason::Damaged,
            'units_sponsored' => StockMovementReason::Sponsored,
            'units_on_display' => StockMovementReason::OnDisplay,
        ];

        foreach (RawMaterial::with('movements')->get() as $material) {
            foreach ($buckets as $column => $reason) {
                $fromLedger = $material->movements
                    ->where('reason', $reason)
                    ->sum(fn ($m) => (float) $m->quantity);

                $this->assertEqualsWithDelta(
                    $fromLedger,
                    (float) $material->$column,
                    0.001,
                    "{$material->name}.{$column} does not match its usage ledger."
                );
            }
        }
    }

    public function test_everything_stocked_is_seeded_with_a_listed_unit(): void
    {
        $allowed = \App\Enums\MaterialUnit::values();

        $rows = RawMaterial::all()
            ->concat(Product::all())
            ->concat(\App\Models\Texture::all());

        foreach ($rows as $row) {
            $this->assertContains(
                $row->unit,
                $allowed,
                "\"{$row->name}\" is seeded with \"{$row->unit}\", which the unit dropdown does not offer."
            );
        }
    }

    public function test_the_seeded_ledger_is_not_empty(): void
    {
        // An empty log would make the assertion above pass trivially.
        $this->assertGreaterThan(0, RawMaterialMovement::count());
    }

    public function test_every_customizable_product_has_a_model_to_open_in(): void
    {
        // The studio renders four shapes and defaults to the t-shirt for
        // anything else, so a customizable product it cannot match came out as
        // a shirt wearing that product's name — an ID lace, an oak plaque.
        foreach (Product::where('is_customizable', true)->get() as $product) {
            $this->assertNotNull(
                $product->customizerShape(),
                "\"{$product->name}\" is marked customizable, but the studio has no model for it."
            );
        }
    }

    public function test_only_customizable_products_are_assigned_textures(): void
    {
        foreach (Product::has('textures')->get() as $product) {
            $this->assertTrue(
                (bool) $product->is_customizable,
                "\"{$product->name}\" has textures assigned but never opens in the customizer."
            );
        }
    }

    public function test_the_id_lace_has_a_bill_of_materials(): void
    {
        $lace = Product::where('sku', 'IDL-LACE-STD')->firstOrFail();

        $this->assertNotEmpty($lace->rawMaterials);
        $this->assertTrue(
            $lace->rawMaterials->contains(fn ($m) => $m->name === 'Lanyard Metal Clip'),
            'The ID lace should draw on the metal clip.'
        );
    }

    public function test_print_consumables_are_only_drawn_when_something_is_printed(): void
    {
        // The flag only makes sense where an order can arrive without a design.
        foreach (Product::with('rawMaterials')->where('is_customizable', false)->get() as $product) {
            foreach ($product->rawMaterials as $material) {
                $this->assertFalse(
                    (bool) $material->pivot->requires_design,
                    "\"{$product->name}\" never opens in the studio, so its \"{$material->name}\" would never be drawn."
                );
            }
        }

        $mug = Product::where('sku', 'MG-WHT-11')->firstOrFail();

        // The sheet and the ink decorate the mug; ordering a plain one hands
        // over the blank and prints nothing, so neither should move.
        foreach ($mug->rawMaterials as $material) {
            $this->assertTrue(
                (bool) $material->pivot->requires_design,
                "The mug's \"{$material->name}\" is drawn even on a plain order."
            );
        }

        // The opposite case, and the one easy to get wrong: an ID lace is
        // printed as part of being made and cannot be ordered blank, so *all*
        // of its lines are unconditional — the ink included. Flagging the ink
        // there would mean a lace never drew any.
        $lace = Product::where('sku', 'IDL-LACE-STD')->firstOrFail();

        foreach ($lace->rawMaterials as $material) {
            $this->assertFalse(
                (bool) $material->pivot->requires_design,
                "The lace's \"{$material->name}\" would never be drawn: a lace has no design to require."
            );
        }
    }

    public function test_a_customizable_product_carries_no_ink_of_its_own(): void
    {
        // What gets printed on a designable product is the customer's choice,
        // so its ink is billed per element rather than as a fixed split. A
        // default here charged every design for black whether its artwork used
        // any or not.
        foreach (Product::with('rawMaterials')->where('is_customizable', true)->get() as $product) {
            foreach ($product->rawMaterials as $material) {
                $this->assertStringNotContainsString(
                    'Ink',
                    $material->name,
                    "\"{$product->name}\" is customizable, so \"{$material->name}\" belongs to the customization BOM."
                );
            }
        }

        // And the converse: a lace is printed as part of being made, so its
        // ink stays on the product.
        $lace = Product::where('sku', 'IDL-LACE-STD')->firstOrFail();
        $this->assertTrue($lace->rawMaterials->contains(fn ($m) => str_contains($m->name, 'Ink')));
    }

    public function test_the_customizer_options_that_cost_something_are_mapped(): void
    {
        // Not every option: small and medium fit the blank's own sheet, so
        // they draw nothing on purpose. These four are the ones a customer is
        // charged for, and each was collecting a fee against nothing before
        // the customization BOM existed.
        foreach (['text', 'shape', 'logo', 'led_lighting'] as $key) {
            $this->assertNotEmpty(
                CustomizationRate::materialsFor($key),
                "The '{$key}' option is charged for but draws no materials."
            );
        }

        $this->assertSame([], CustomizationRate::materialsFor('size_medium'));
    }

    public function test_every_seeded_option_material_resolves_to_a_real_material(): void
    {
        // The seeder maps by name, so a rename in RawMaterialSeeder would
        // silently drop a line rather than fail.
        $mapped = CustomizationRateMaterial::pluck('raw_material_id')->unique();

        $this->assertSame(
            $mapped->count(),
            RawMaterial::whereIn('raw_material_id', $mapped)->count(),
            'A customization option is mapped to a material that no longer exists.'
        );
    }

    public function test_only_the_paid_finishes_draw_a_dye(): void
    {
        // The blank garment already is white, black, grey or navy — nothing is
        // applied, which is why those four are free. The paid ones are dyed,
        // and that dye is what the surcharge pays for.
        $colors = Color::all();

        foreach ($colors as $color) {
            $paid = $color->price_modifier > 0;

            $this->assertSame(
                $paid,
                $color->raw_material_id !== null,
                $paid
                    ? "{$color->name} charges a surcharge but draws no dye."
                    : "{$color->name} is free but draws a dye."
            );
        }

        $this->assertGreaterThan(0, $colors->where('price_modifier', '>', 0)->count());
    }
}
