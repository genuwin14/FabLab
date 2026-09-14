<?php

namespace Tests\Feature;

use App\Enums\StockMovementReason;
use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\CustomizationRate;
use App\Models\CustomizationRateMaterial;
use App\Models\InkChannel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Models\Supplier;
use App\Models\TransferSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * What a customized order draws in ink once ink is measured.
 *
 * The figure is coverage × the product's printable area at the ordered size
 * × the channel's rate, per item, from the bottle the channel is linked to.
 * The element bills of materials stop contributing ink for such a product —
 * a bottle must not be emptied twice for one print — but keep contributing
 * everything else, and a product whose print area is unknown falls back to
 * them exactly as before.
 */
class MeasuredInkDrawTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $shirt;
    /** @var array<string, RawMaterial> */
    private array $bottles = [];
    private RawMaterial $led;
    private RawMaterial $paper;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->customer = $this->user('customer', 'c@example.test');

        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 's@example.test']);
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        // 1000 cm² at Medium, so the arithmetic below reads easily.
        $this->shirt = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
            'is_customizable' => true, 'print_area_cm2' => 1000,
        ]);

        foreach (array_keys(InkChannel::CHANNELS) as $channel) {
            $this->bottles[$channel] = RawMaterial::create([
                'name' => 'Sublimation Ink (' . ucfirst($channel) . ')', 'supplier_id' => $supplier->supplier_id,
                'cost_per_unit' => 4.5, 'stock_quantity' => 100, 'low_stock_threshold' => 5, 'unit' => 'ml',
            ]);

            // A round rate: a square centimetre of solid colour is 0.01 ml.
            InkChannel::where('channel', $channel)->update([
                'raw_material_id' => $this->bottles[$channel]->raw_material_id,
                'ml_per_cm2' => 0.01,
            ]);
        }

        $this->led = RawMaterial::create([
            'name' => 'LED strip', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 100, 'low_stock_threshold' => 5, 'unit' => 'pcs',
        ]);

        // The transfer sheet: an A4 with a half-centimetre cut margin,
        // stocked as this paper.
        $this->paper = RawMaterial::create([
            'name' => 'Sublimation Transfer Paper (A4)', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 3,
            'stock_quantity' => 100, 'low_stock_threshold' => 5, 'unit' => 'pcs',
        ]);
        TransferSheet::query()->update(['raw_material_id' => $this->paper->raw_material_id, 'width_cm' => 21, 'height_cm' => 29.7, 'margin_cm' => 0.5]);

        // Medium prints at 1.0, Large at 1.5 — set explicitly so the test
        // does not depend on the shipped defaults.
        CustomizationRate::where('key', 'size_medium')->update(['print_area_factor' => 1]);
        CustomizationRate::where('key', 'size_large')->update(['print_area_factor' => 1.5]);

        CustomizationRate::flushCache();
        InkChannel::flushCache();
        TransferSheet::flushCache();
    }

    private function user(string $role, string $email): User
    {
        return User::firstOrCreate(['email' => $email], [
            'fullname' => ucfirst($role), 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    /**
     * A design whose print was measured as the given coverage.
     *
     * Its one panel is the left half of the atlas, so with the product's
     * 1000 cm² the atlas is √(1000 / 0.5) ≈ 44.72 cm across; and the
     * artwork on it is boxed at a tenth of the atlas wide by a fifth tall,
     * so it prints 4.47 × 8.94 cm.
     *
     * @param  array<string, float>  $coverage
     * @param  array<string, mixed>  $recipe
     * @param  array<string, float>|null  $bbox  where the artwork sits, or null for a blank panel
     */
    private function measured(array $coverage, string $size = 'medium', array $recipe = [], ?array $bbox = ['u0' => 0.1, 'v0' => 0.1, 'u1' => 0.2, 'v1' => 0.3]): CustomDesign
    {
        return CustomDesign::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->shirt->product_id,
            'recipe' => $recipe + ['base_style' => 't-shirt', 'size' => $size, 'elements' => ['text' => [], 'shapes' => [], 'logos' => [['scale' => 1]]]],
            'ink_coverage' => $coverage + ['cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'black' => 0],
            'print_image' => 'designs/prints/1.png',
            'print_zones' => [
                ['id' => 'front', 'label' => 'Front', 'u0' => 0.0, 'v0' => 0.0, 'u1' => 0.5, 'v1' => 1.0, 'flipU' => false, 'flipV' => false, 'bbox' => $bbox],
            ],
        ]);
    }

    private function paperStock(): float
    {
        return (float) $this->paper->refresh()->stock_quantity;
    }

    private function order(CustomDesign $design, int $quantity = 1, string $status = 'pending'): Order
    {
        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'status' => $status,
            'total_amount' => 100 * $quantity,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $this->shirt->product_id,
            'custom_design_id' => $design->custom_design_id,
            'quantity' => $quantity,
            'price' => 100,
        ]);

        return $order->refresh();
    }

    private function approve(Order $order, array $quantities = []): void
    {
        Sanctum::actingAs($this->user('admin', 'a@example.test'));
        $this->post("/admin/orders/{$order->order_id}/review", array_filter([
            'status' => 'approved',
            'material_quantities' => $quantities,
        ]))->assertRedirect()->assertSessionMissing('error');
    }

    private function stock(string $channel): float
    {
        return (float) $this->bottles[$channel]->refresh()->stock_quantity;
    }

    public function test_a_trace_of_ink_is_still_drawn(): void
    {
        // A small logo: 0.03% magenta of 1000 cm² at 0.01 ml/cm² is 0.003 ml.
        // At two decimals that rounded to nothing and the bottle dropped off
        // the order, so a red-and-yellow print showed only its yellow.
        $order = $this->order($this->measured(['magenta' => 0.0003, 'yellow' => 0.0012]));

        Sanctum::actingAs($this->user('admin', 'a@example.test'));
        $lines = collect($this->get("/admin/orders/{$order->order_id}/materials")->assertOk()->json('lines'));
        $this->assertSame('0.003', $lines->firstWhere('id', $this->bottles['magenta']->raw_material_id)['quantity']);

        // Each bottle says which channel it feeds, so the screen can swatch
        // the row and paint the print with that ink alone; paper does not.
        $this->assertSame(['key' => 'magenta', 'label' => 'Magenta', 'swatch' => '#ec008c'], $lines->firstWhere('id', $this->bottles['magenta']->raw_material_id)['ink']);
        $this->assertNull($lines->firstWhere('id', $this->paper->raw_material_id)['ink']);

        $this->approve($order);

        $this->assertSame(99.997, $this->stock('magenta'));
        $this->assertSame(99.988, $this->stock('yellow'));
        $this->assertEquals(0.003, RawMaterialMovement::where('raw_material_id', $this->bottles['magenta']->raw_material_id)->firstOrFail()->quantity);
    }

    public function test_ink_is_coverage_times_area_times_rate(): void
    {
        // 50% cyan of 1000 cm² at 0.01 ml/cm² = 5 ml; 20% black = 2 ml.
        $this->approve($this->order($this->measured(['cyan' => 0.5, 'black' => 0.2])));

        $this->assertSame(95.0, $this->stock('cyan'));
        $this->assertSame(98.0, $this->stock('black'));
        // Nothing of these colours in the artwork, so nothing moves.
        $this->assertSame(100.0, $this->stock('magenta'));
        $this->assertSame(100.0, $this->stock('yellow'));
        $this->assertSame(0, RawMaterialMovement::where('raw_material_id', $this->bottles['magenta']->raw_material_id)->count());
    }

    public function test_a_bigger_garment_takes_more_ink_for_the_same_design(): void
    {
        // Large prints at 1.5× Medium: 50% of 1500 cm² × 0.01 = 7.5 ml.
        $this->approve($this->order($this->measured(['cyan' => 0.5], 'large')));

        $this->assertSame(92.5, $this->stock('cyan'));
    }

    public function test_every_item_on_the_line_takes_its_own_ink(): void
    {
        $this->approve($this->order($this->measured(['cyan' => 0.5]), quantity: 3));

        $this->assertSame(85.0, $this->stock('cyan'));
    }

    public function test_the_element_bills_no_longer_draw_ink_for_a_measured_product(): void
    {
        // An old-style BOM still mapping the image to cyan. If it applied on
        // top of the measurement the bottle would be drawn twice.
        CustomizationRateMaterial::create([
            'rate_key' => 'logo', 'raw_material_id' => $this->bottles['cyan']->raw_material_id, 'quantity_required' => 10,
        ]);
        // Lighting is not ink and must still be drawn.
        CustomizationRateMaterial::create([
            'rate_key' => 'led_lighting', 'raw_material_id' => $this->led->raw_material_id, 'quantity_required' => 1,
        ]);
        CustomizationRate::flushCache();

        $design = $this->measured(['cyan' => 0.5], recipe: ['features' => ['led_lighting' => true]]);
        $this->approve($this->order($design));

        $this->assertSame(95.0, $this->stock('cyan'), 'Only the measured 5 ml, not the BOM\'s 10 on top.');
        $this->assertSame(99.0, (float) $this->led->refresh()->stock_quantity);
    }

    public function test_a_product_with_no_print_area_falls_back_to_the_element_bills(): void
    {
        CustomizationRateMaterial::create([
            'rate_key' => 'logo', 'raw_material_id' => $this->bottles['cyan']->raw_material_id, 'quantity_required' => 10,
        ]);
        CustomizationRate::flushCache();

        $this->shirt->update(['print_area_cm2' => null]);

        $this->approve($this->order($this->measured(['cyan' => 0.5])));

        // The BOM's 10 ml, because there is no area to multiply coverage by.
        $this->assertSame(90.0, $this->stock('cyan'));
    }

    public function test_with_no_channel_linked_the_measurement_draws_nothing(): void
    {
        InkChannel::query()->update(['raw_material_id' => null]);
        InkChannel::flushCache();

        CustomizationRateMaterial::create([
            'rate_key' => 'logo', 'raw_material_id' => $this->bottles['cyan']->raw_material_id, 'quantity_required' => 10,
        ]);
        CustomizationRate::flushCache();

        $this->approve($this->order($this->measured(['cyan' => 0.5])));

        // Falls back to the BOM, as an unconfigured install did before.
        $this->assertSame(90.0, $this->stock('cyan'));
    }

    public function test_a_design_saved_without_a_print_is_estimated_from_its_recipe(): void
    {
        $design = CustomDesign::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->shirt->product_id,
            'recipe' => [
                'base_style' => 't-shirt', 'size' => 'medium',
                'elements' => ['text' => [['text' => 'RED', 'color' => '#ff0000', 'scale' => 4]], 'shapes' => [], 'logos' => []],
            ],
        ]);

        $this->approve($this->order($design));

        // Red is magenta and yellow. No cyan, no black.
        $this->assertLessThan(100.0, $this->stock('magenta'));
        $this->assertSame($this->stock('magenta'), $this->stock('yellow'));
        $this->assertSame(100.0, $this->stock('cyan'));
        $this->assertSame(100.0, $this->stock('black'));
    }

    public function test_the_panel_shows_the_working_and_the_print_behind_each_figure(): void
    {
        $order = $this->order($this->measured(['cyan' => 0.5], 'large'), quantity: 2);

        Sanctum::actingAs($this->user('admin', 'a@example.test'));
        $data = $this->get("/admin/orders/{$order->order_id}/materials")->assertOk()->json();

        $cyan = collect($data['lines'])->firstWhere('id', $this->bottles['cyan']->raw_material_id);
        $this->assertNotNull($cyan);
        $this->assertSame('15', $cyan['quantity']);
        $this->assertTrue($cyan['editable']);
        $this->assertCount(1, $cyan['notes']);
        $this->assertStringContainsString('50% Cyan coverage of 1500 cm²', $cyan['notes'][0]);
        $this->assertStringContainsString('measured from the print', $cyan['notes'][0]);
        $this->assertStringContainsString('size L', $cyan['notes'][0]);
        $this->assertStringContainsString('× 2 items', $cyan['notes'][0]);

        // The print comes with its panels, so the screen can crop and label
        // each one instead of showing the whole atlas.
        $this->assertCount(1, $data['prints']);
        $this->assertSame('/storage/designs/prints/1.png', $data['prints'][0]['url']);
        $this->assertSame('Shirt', $data['prints'][0]['label']);
        $this->assertSame('Front', $data['prints'][0]['zones'][0]['label']);

        // Staff see the same working at the bench.
        Sanctum::actingAs($this->user('staff', 'st@example.test'));
        $this->get("/staff/orders/{$order->order_id}/materials")->assertOk()->assertJsonPath('lines.0.notes.0', $cyan['notes'][0]);
    }

    // ------------------------------------------------ the transfer paper

    public function test_paper_is_the_fraction_of_a_sheet_the_cut_piece_takes(): void
    {
        // 4.47 × 8.94 cm of artwork, cut 5.47 × 9.94 with the margin:
        // 54.37 cm² of an A4's 623.7 = 0.0872 of a sheet, kept as is in the ledger.
        $this->approve($this->order($this->measured(['cyan' => 0.5])));

        $this->assertSame(99.9128, $this->paperStock());
    }

    public function test_every_item_takes_its_own_piece_of_paper(): void
    {
        // 0.0872 × 3 = 0.2616 of a sheet.
        $this->approve($this->order($this->measured(['cyan' => 0.5]), quantity: 3));

        $this->assertSame(99.7384, $this->paperStock());
    }

    public function test_a_transfer_is_the_same_size_on_every_garment_by_default(): void
    {
        // Large prints at 1.5× in this test's settings, so the piece grows;
        // out of the box every factor is 1 and it would not. Both are
        // honoured — the point is that the paper follows the same factor
        // the ink does.
        $this->approve($this->order($this->measured(['cyan' => 0.5], 'large')));

        // √(1500 / 0.5) = 54.77 cm across: 5.48 × 10.95 artwork, cut 6.48 × 11.95 = 77.44 cm² → 0.1242.
        $this->assertSame(99.8758, $this->paperStock());
    }

    public function test_the_bills_no_longer_draw_paper_for_a_measured_print(): void
    {
        // The old fixed sheets: one per Large garment in the size BOM, and
        // one per print on the product's own BOM, flagged as decoration.
        CustomizationRateMaterial::create([
            'rate_key' => 'size_large', 'raw_material_id' => $this->paper->raw_material_id, 'quantity_required' => 1,
        ]);
        $this->shirt->rawMaterials()->attach($this->paper->raw_material_id, ['quantity_required' => 1, 'requires_design' => true]);
        CustomizationRate::flushCache();

        $this->approve($this->order($this->measured(['cyan' => 0.5], 'large')));

        // Only the measured piece, not two whole sheets on top.
        $this->assertSame(99.8758, $this->paperStock());
    }

    public function test_a_design_saved_before_panels_were_boxed_falls_back_to_the_bills(): void
    {
        CustomizationRateMaterial::create([
            'rate_key' => 'size_large', 'raw_material_id' => $this->paper->raw_material_id, 'quantity_required' => 1,
        ]);
        CustomizationRate::flushCache();

        // A panel with no box: nothing to measure a piece from.
        $this->approve($this->order($this->measured(['cyan' => 0.5], 'large', bbox: null)));

        $this->assertSame(99.0, $this->paperStock());
    }

    public function test_paper_needs_the_sheet_linked_to_stock(): void
    {
        TransferSheet::query()->update(['raw_material_id' => null]);
        TransferSheet::flushCache();

        $this->approve($this->order($this->measured(['cyan' => 0.5])));

        $this->assertSame(100.0, $this->paperStock());
    }

    public function test_a_print_bigger_than_the_sheet_is_flagged_but_not_blocked(): void
    {
        // The whole panel: 22.36 × 44.72 cm, which no way round fits an A4.
        $order = $this->order($this->measured(['cyan' => 0.5], bbox: ['u0' => 0, 'v0' => 0, 'u1' => 0.5, 'v1' => 1]));

        Sanctum::actingAs($this->user('admin', 'a@example.test'));
        $data = $this->get("/admin/orders/{$order->order_id}/materials")->assertOk()->json();

        $this->assertCount(1, $data['warnings']);
        $this->assertStringContainsString('larger than an A4 sheet', $data['warnings'][0]);
        $this->assertSame([], $data['shortages']);

        // Still counted — the reviewer decides — and still approvable.
        $this->approve($order);
        $this->assertLessThan(100.0, $this->paperStock());
    }

    public function test_the_panel_shows_the_print_size_behind_the_paper_figure(): void
    {
        $order = $this->order($this->measured(['cyan' => 0.5]));

        Sanctum::actingAs($this->user('admin', 'a@example.test'));
        $data = $this->get("/admin/orders/{$order->order_id}/materials")->assertOk()->json();

        $paper = collect($data['lines'])->firstWhere('id', $this->paper->raw_material_id);
        $this->assertNotNull($paper);
        $this->assertSame('0.0872', $paper['quantity']);
        $this->assertStringContainsString('Front prints 4.47 × 8.94 cm (1.8 × 3.5 in)', $paper['notes'][0]);
        // Four decimals in the working, like the ledger it lands in.
        $this->assertStringContainsString('cut 5.47 × 9.94 cm with the 0.5 cm margin = 0.0872 of an A4 sheet', $paper['notes'][0]);
        $this->assertSame([], $data['warnings']);

        // The panel crop carries the same size, for its caption.
        $this->assertSame(4.47, $data['prints'][0]['zones'][0]['width_cm']);
        $this->assertSame(8.94, $data['prints'][0]['zones'][0]['height_cm']);
    }

    public function test_the_reviewer_can_still_correct_a_measured_figure(): void
    {
        $this->approve($this->order($this->measured(['cyan' => 0.5])), [$this->bottles['cyan']->raw_material_id => 2]);

        $this->assertSame(98.0, $this->stock('cyan'));
        $this->assertStringContainsString(
            'set by reviewer',
            RawMaterialMovement::where('raw_material_id', $this->bottles['cyan']->raw_material_id)->sole()->note
        );
    }

    public function test_production_consumes_what_was_measured_and_reserved(): void
    {
        $order = $this->order($this->measured(['cyan' => 0.5]));
        $this->approve($order);

        $order->update(['status' => 'paid', 'payment_reference' => 'OR-1']);
        Sanctum::actingAs($this->user('staff', 'st@example.test'));
        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'processing'])
            ->assertRedirect();

        $this->assertSame(95.0, $this->stock('cyan'));
        $consumed = RawMaterialMovement::where('raw_material_id', $this->bottles['cyan']->raw_material_id)
            ->where('reason', StockMovementReason::Consumed)
            ->sole();
        $this->assertSame(5.0, (float) $consumed->quantity);
    }
}
