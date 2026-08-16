<?php

namespace Tests\Feature;

use App\Enums\StockMovementReason;
use App\Models\Category;
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
}
