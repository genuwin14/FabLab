<?php

namespace Tests\Feature;

use App\Enums\StockMovementReason;
use App\Models\Category;
use App\Models\Color;
use App\Models\CustomDesign;
use App\Models\CustomizationRate;
use App\Models\CustomizationRateMaterial;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use App\Services\OrderStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * What a customized order takes off the shelf, and when.
 *
 * Two things were broken and are covered here. A design's *customization* drew
 * nothing — twelve lines of text and internal lighting were charged for and no
 * ink or LED strip ever moved — and the whole draw landed at approval, so the
 * materials report counted a job as consumed before anyone had started it.
 */
class CustomizationMaterialTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;
    private RawMaterial $fabric;
    private RawMaterial $ink;
    private RawMaterial $led;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->customer = $this->user('customer', 'c@example.test');

        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 's@example.test']);
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        $this->product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
        ]);

        $this->fabric = $this->material('Fabric', 'm', $supplier->supplier_id);
        $this->ink = $this->material('Ink (Red)', 'ml', $supplier->supplier_id);
        $this->led = $this->material('LED strip', 'pcs', $supplier->supplier_id);

        // One shirt eats 3m of fabric before anyone customizes anything.
        $this->product->rawMaterials()->attach($this->fabric->raw_material_id, ['quantity_required' => 3]);

        CustomizationRate::flushCache();
    }

    private function material(string $name, string $unit, int $supplierId): RawMaterial
    {
        return RawMaterial::create([
            'name' => $name, 'supplier_id' => $supplierId, 'cost_per_unit' => 10,
            'stock_quantity' => 100, 'low_stock_threshold' => 5, 'unit' => $unit,
        ]);
    }

    /**
     * firstOrCreate, not create: a test that approves and then cancels signs
     * in as the admin twice, and a second create would trip the email index
     * rather than testing anything.
     */
    private function user(string $role, string $email): User
    {
        return User::firstOrCreate(['email' => $email], [
            'fullname' => ucfirst($role), 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    /** Map a customization option onto what one unit of it consumes. */
    private function optionCosts(string $rateKey, RawMaterial $material, float $quantity): void
    {
        CustomizationRateMaterial::create([
            'rate_key' => $rateKey,
            'raw_material_id' => $material->raw_material_id,
            'quantity_required' => $quantity,
        ]);

        CustomizationRate::flushCache();
    }

    /**
     * @param  array<string, mixed>  $recipe
     */
    private function design(array $recipe): CustomDesign
    {
        return CustomDesign::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'recipe' => $recipe + [
                'base_style' => 't-shirt',
                'size' => 'medium',
                'elements' => ['text' => [], 'shapes' => [], 'logos' => []],
            ],
        ]);
    }

    private function order(string $status = 'pending', int $quantity = 1, ?CustomDesign $design = null): Order
    {
        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'status' => $status,
            'total_amount' => 100 * $quantity,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $this->product->product_id,
            'custom_design_id' => $design?->custom_design_id,
            'quantity' => $quantity,
            'price' => 100,
        ]);

        return $order->refresh();
    }

    private function asAdmin(): void
    {
        Sanctum::actingAs($this->user('admin', 'a@example.test'));
    }

    private function asStaff(): void
    {
        Sanctum::actingAs($this->user('staff', 'st@example.test'));
    }

    private function approve(Order $order): void
    {
        $this->asAdmin();
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])->assertRedirect();
    }

    private function startProduction(Order $order): void
    {
        $this->asStaff();
        $this->post("/staff/orders/{$order->order_id}/update-status", [
            'status' => 'processing', 'payment_reference' => 'OR-1',
        ])->assertRedirect();
    }

    // ------------------------------------------------- the customization BOM

    public function test_design_elements_draw_the_materials_mapped_to_them(): void
    {
        // 2ml of ink per line of text, 1 LED strip per lit item.
        $this->optionCosts('text', $this->ink, 2);
        $this->optionCosts('led_lighting', $this->led, 1);

        $design = $this->design([
            'features' => ['led_lighting' => true],
            'elements' => [
                'text' => [['text' => 'One'], ['text' => 'Two'], ['text' => 'Three']],
                'shapes' => [],
                'logos' => [],
            ],
        ]);

        $this->approve($this->order('pending', 2, $design));

        // 3 lines x 2ml x 2 shirts = 12ml
        $this->assertEquals(88, $this->ink->refresh()->stock_quantity);
        // 1 strip x 2 shirts
        $this->assertEquals(98, $this->led->refresh()->stock_quantity);
        // The product's own BOM still applies on top: 2 x 3m
        $this->assertEquals(94, $this->fabric->refresh()->stock_quantity);
    }

    public function test_an_uploaded_image_draws_in_proportion_to_its_printed_size(): void
    {
        $this->optionCosts('logo', $this->ink, 10);

        $design = $this->design([
            'elements' => [
                'text' => [], 'shapes' => [],
                // Half size and double size — 0.5 + 2 = 2.5 units of ink.
                'logos' => [['scale' => 0.5], ['scale' => 2]],
            ],
        ]);

        $this->approve($this->order('pending', 1, $design));

        $this->assertEquals(75, $this->ink->refresh()->stock_quantity);
    }

    public function test_a_logo_scale_outside_the_slider_cannot_inflate_the_draw(): void
    {
        $this->optionCosts('logo', $this->ink, 10);

        // A hand-edited recipe asking for 1000x is clamped to the slider's max,
        // the same way its fee is.
        $design = $this->design([
            'elements' => ['text' => [], 'shapes' => [], 'logos' => [['scale' => 1000]]],
        ]);

        $this->approve($this->order('pending', 1, $design));

        $this->assertEquals(100 - (CustomDesign::LOGO_MAX_SCALE * 10), $this->ink->refresh()->stock_quantity);
    }

    public function test_a_colour_finish_draws_its_linked_material(): void
    {
        $red = Color::create([
            'name' => 'Red', 'hex_code' => '#ff0000',
            'raw_material_id' => $this->ink->raw_material_id, 'material_quantity' => 5,
        ]);

        $this->approve($this->order('pending', 3, $this->design(['color_id' => $red->color_id])));

        // 5ml x 3 shirts
        $this->assertEquals(85, $this->ink->refresh()->stock_quantity);
    }

    public function test_a_colour_with_no_material_linked_draws_nothing(): void
    {
        $plain = Color::create(['name' => 'Plain', 'hex_code' => '#ffffff']);

        $this->approve($this->order('pending', 2, $this->design(['color_id' => $plain->color_id])));

        $this->assertEquals(100, $this->ink->refresh()->stock_quantity);
    }

    public function test_a_texture_draws_its_own_stock_and_its_linked_material(): void
    {
        $texture = Texture::create([
            'name' => 'Camo', 'stock_quantity' => 50, 'low_stock_threshold' => 5,
            'raw_material_id' => $this->ink->raw_material_id, 'material_quantity' => 4,
        ]);

        $this->approve($this->order('pending', 2, $this->design(['texture_id' => $texture->texture_id])));

        $this->assertEquals(48, $texture->refresh()->stock_quantity);
        $this->assertEquals(92, $this->ink->refresh()->stock_quantity);
    }

    public function test_the_same_material_reached_two_ways_is_charged_once_combined(): void
    {
        // The product is made of ink too, so the shortage check has to see the
        // product BOM and the customization BOM as one figure.
        $this->product->rawMaterials()->attach($this->ink->raw_material_id, ['quantity_required' => 10]);
        $this->optionCosts('text', $this->ink, 2);

        $design = $this->design([
            'elements' => ['text' => [['text' => 'a'], ['text' => 'b']], 'shapes' => [], 'logos' => []],
        ]);

        $this->approve($this->order('pending', 2, $design));

        // (10 product + 2 lines x 2ml) x 2 shirts = 28ml, in one movement.
        $this->assertEquals(72, $this->ink->refresh()->stock_quantity);
        $this->assertSame(1, RawMaterialMovement::where('raw_material_id', $this->ink->raw_material_id)->count());
    }

    public function test_approval_is_refused_when_a_customization_material_is_short(): void
    {
        $this->optionCosts('text', $this->ink, 60);
        $this->asAdmin();

        $design = $this->design([
            'elements' => ['text' => [['text' => 'a'], ['text' => 'b']], 'shapes' => [], 'logos' => []],
        ]);
        $order = $this->order('pending', 1, $design);

        // 2 lines x 60ml = 120ml against 100ml on the shelf.
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->refresh()->status);
        $this->assertEquals(100, $this->ink->refresh()->stock_quantity);
        $this->assertEquals(100, $this->fabric->refresh()->stock_quantity);
    }

    // ------------------------------------------------- reserve, then consume

    public function test_approval_reserves_without_marking_anything_consumed(): void
    {
        $this->approve($this->order('pending', 2));

        $this->fabric->refresh();

        // Off the shelf, so a second order can't be promised it...
        $this->assertEquals(94, $this->fabric->stock_quantity);
        // ...but not yet used up, because nobody has started making it.
        $this->assertEquals(0, $this->fabric->units_consumed);

        $movement = RawMaterialMovement::where('raw_material_id', $this->fabric->raw_material_id)->sole();
        $this->assertSame(StockMovementReason::Reserved, $movement->reason);
    }

    public function test_starting_production_consumes_the_reservation_without_moving_stock_again(): void
    {
        $this->approve($order = $this->order('pending', 2));

        $this->startProduction($order);

        $this->fabric->refresh();

        // Stock is where the reservation left it — the material left the shelf
        // once, at approval.
        $this->assertEquals(94, $this->fabric->stock_quantity);
        // But it now counts as used, which is what the materials report reads.
        $this->assertEquals(6, $this->fabric->units_consumed);

        $reasons = RawMaterialMovement::where('raw_material_id', $this->fabric->raw_material_id)
            ->orderBy('movement_id')
            ->pluck('reason')
            ->all();

        // Reserved, released, consumed — the ledger reads as what happened.
        $this->assertSame(
            [StockMovementReason::Reserved, StockMovementReason::Reversal, StockMovementReason::Consumed],
            $reasons
        );
    }

    public function test_cancelling_before_production_gives_back_the_reservation(): void
    {
        $this->approve($order = $this->order('pending', 2));
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Customer changed their mind'])
            ->assertRedirect();

        $this->fabric->refresh();
        $this->assertEquals(100, $this->fabric->stock_quantity);
        $this->assertEquals(0, $this->fabric->units_consumed);
    }

    public function test_cancelling_after_production_gives_back_the_consumption(): void
    {
        $this->approve($order = $this->order('pending', 2));
        $this->startProduction($order);

        $this->asAdmin();
        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Machine broke'])
            ->assertRedirect();

        $this->fabric->refresh();
        $this->assertEquals(100, $this->fabric->stock_quantity);
        // The consumption was unwound too — the report must not keep counting
        // material that went back on the shelf.
        $this->assertEquals(0, $this->fabric->units_consumed);
    }

    public function test_editing_a_bom_between_approval_and_production_does_not_change_what_is_consumed(): void
    {
        $this->optionCosts('text', $this->ink, 2);

        $design = $this->design([
            'elements' => ['text' => [['text' => 'a']], 'shapes' => [], 'logos' => []],
        ]);
        $this->approve($order = $this->order('pending', 1, $design));

        $this->assertEquals(98, $this->ink->refresh()->stock_quantity);

        // An admin retunes the recipe after approval. The job at the bench was
        // costed at the old figure, and that is what it consumes.
        CustomizationRateMaterial::where('rate_key', 'text')->update(['quantity_required' => 50]);
        CustomizationRate::flushCache();

        $this->startProduction($order);

        $this->ink->refresh();
        $this->assertEquals(98, $this->ink->stock_quantity);
        $this->assertEquals(2, $this->ink->units_consumed);
    }

    public function test_an_order_approved_before_reservations_existed_is_left_alone(): void
    {
        // Simulates history: the ledger holds a Consumed row from the days when
        // approval consumed outright, so there is nothing to convert.
        $this->approve($order = $this->order('pending', 2));

        RawMaterialMovement::where('order_id', $order->order_id)
            ->update(['reason' => StockMovementReason::Consumed]);
        $this->fabric->update(['units_consumed' => 6]);

        $this->startProduction($order);

        $this->fabric->refresh();
        $this->assertEquals(94, $this->fabric->stock_quantity);
        $this->assertEquals(6, $this->fabric->units_consumed);
        $this->assertSame(1, RawMaterialMovement::where('order_id', $order->order_id)->count());
    }

    public function test_an_uncustomized_order_still_works_when_no_options_are_mapped(): void
    {
        $this->approve($order = $this->order('pending', 2));
        $this->startProduction($order);

        $this->assertSame('processing', $order->refresh()->status);
        $this->assertEquals(94, $this->fabric->refresh()->stock_quantity);
    }

    // ------------------------------------------- decorating vs. making

    public function test_a_plain_order_skips_the_materials_that_only_a_print_uses(): void
    {
        // The fabric is what the shirt is; the ink is what decorates it.
        $this->product->rawMaterials()->syncWithoutDetaching([
            $this->ink->raw_material_id => ['quantity_required' => 8, 'requires_design' => true],
        ]);

        // No design on the line — ordered straight off the shop page.
        $this->approve($this->order('pending', 2));

        // The blank still costs its fabric...
        $this->assertEquals(94, $this->fabric->refresh()->stock_quantity);
        // ...but no ink is spent on a print nobody asked for.
        $this->assertEquals(100, $this->ink->refresh()->stock_quantity);
    }

    public function test_a_designed_order_still_draws_them(): void
    {
        $this->product->rawMaterials()->syncWithoutDetaching([
            $this->ink->raw_material_id => ['quantity_required' => 8, 'requires_design' => true],
        ]);

        $this->approve($this->order('pending', 2, $this->design([])));

        $this->assertEquals(94, $this->fabric->refresh()->stock_quantity);
        $this->assertEquals(84, $this->ink->refresh()->stock_quantity);
    }

    public function test_an_unflagged_line_is_drawn_either_way(): void
    {
        // The default. A bill of materials written before the flag existed
        // must keep behaving exactly as it did.
        $this->approve($this->order('pending', 2));

        $this->assertEquals(94, $this->fabric->refresh()->stock_quantity);
    }

    public function test_the_shortage_check_ignores_a_print_material_a_plain_order_wont_use(): void
    {
        $this->ink->update(['stock_quantity' => 1]);
        $this->product->rawMaterials()->syncWithoutDetaching([
            $this->ink->raw_material_id => ['quantity_required' => 8, 'requires_design' => true],
        ]);
        $this->asAdmin();

        // Approval must not be blocked by ink the order was never going to use.
        $order = $this->order('pending', 2);
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertSame('approved', $order->refresh()->status);
    }

    // ------------------------------- the reviewer correcting the estimate

    /** Approve, correcting the calculated figures to the ones given. */
    private function approveWith(Order $order, array $quantities)
    {
        $this->asAdmin();

        return $this->post("/admin/orders/{$order->order_id}/review", [
            'status' => 'approved',
            'material_quantities' => $quantities,
        ]);
    }

    public function test_the_reviewer_can_correct_what_a_design_costs_in_ink(): void
    {
        $this->optionCosts('logo', $this->ink, 10);
        $design = $this->design(['elements' => ['text' => [], 'shapes' => [], 'logos' => [['scale' => 1]]]]);

        // The formula says 10ml. The reviewer can see the artwork is a thin
        // outline and says 3.
        $this->approveWith($this->order('pending', 1, $design), [$this->ink->raw_material_id => 3])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertEquals(97, $this->ink->refresh()->stock_quantity);
    }

    public function test_a_corrected_figure_says_so_in_the_ledger(): void
    {
        $this->optionCosts('logo', $this->ink, 10);
        $design = $this->design(['elements' => ['text' => [], 'shapes' => [], 'logos' => [['scale' => 1]]]]);

        $this->approveWith($this->order('pending', 1, $design), [$this->ink->raw_material_id => 3]);

        $ink = RawMaterialMovement::where('raw_material_id', $this->ink->raw_material_id)->sole();
        $this->assertStringContainsString('set by reviewer', $ink->note);

        // The fabric was left alone, so its row must not claim a person chose it.
        $fabric = RawMaterialMovement::where('raw_material_id', $this->fabric->raw_material_id)->sole();
        $this->assertStringNotContainsString('set by reviewer', $fabric->note);
    }

    public function test_posting_the_calculated_figure_back_is_not_an_adjustment(): void
    {
        // An untouched form posts every input as it was rendered. That is the
        // formula's number, and the ledger should say so.
        $this->approveWith($this->order('pending', 2), [$this->fabric->raw_material_id => 6]);

        $movement = RawMaterialMovement::where('raw_material_id', $this->fabric->raw_material_id)->sole();
        $this->assertStringNotContainsString('set by reviewer', $movement->note);
        $this->assertEquals(94, $this->fabric->refresh()->stock_quantity);
    }

    public function test_zero_means_this_artwork_uses_none_of_it(): void
    {
        $this->optionCosts('logo', $this->ink, 10);
        $design = $this->design(['elements' => ['text' => [], 'shapes' => [], 'logos' => [['scale' => 1]]]]);

        // An all-red image uses no cyan at all.
        $this->approveWith($this->order('pending', 1, $design), [$this->ink->raw_material_id => 0]);

        $this->assertEquals(100, $this->ink->refresh()->stock_quantity);
        $this->assertSame(0, RawMaterialMovement::where('raw_material_id', $this->ink->raw_material_id)->count());
    }

    public function test_a_correction_cannot_be_raised_past_the_shelf(): void
    {
        $order = $this->order('pending', 2);

        $this->approveWith($order, [$this->fabric->raw_material_id => 500])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->refresh()->status);
        $this->assertEquals(100, $this->fabric->refresh()->stock_quantity);
    }

    public function test_a_correction_cannot_invent_a_draw_on_an_unrelated_material(): void
    {
        // The LED kit has nothing to do with this order. A crafted post naming
        // it must be ignored rather than deducted.
        $this->approveWith($this->order('pending', 2), [$this->led->raw_material_id => 25]);

        $this->assertEquals(100, $this->led->refresh()->stock_quantity);
        $this->assertSame(0, RawMaterialMovement::where('raw_material_id', $this->led->raw_material_id)->count());
    }

    public function test_a_correction_survives_into_production(): void
    {
        $this->optionCosts('logo', $this->ink, 10);
        $design = $this->design(['elements' => ['text' => [], 'shapes' => [], 'logos' => [['scale' => 1]]]]);
        $order = $this->order('pending', 1, $design);

        $this->approveWith($order, [$this->ink->raw_material_id => 3]);
        $this->startProduction($order);

        $this->ink->refresh();
        // The reviewer's figure is what the bench consumes, not the formula's.
        $this->assertEquals(97, $this->ink->stock_quantity);
        $this->assertEquals(3, $this->ink->units_consumed);
    }

    public function test_cancelling_gives_back_the_corrected_figure(): void
    {
        $this->optionCosts('logo', $this->ink, 10);
        $design = $this->design(['elements' => ['text' => [], 'shapes' => [], 'logos' => [['scale' => 1]]]]);
        $order = $this->order('pending', 1, $design);

        $this->approveWith($order, [$this->ink->raw_material_id => 3]);

        $this->asAdmin();
        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Changed their mind']);

        // 3 back, not the 10 the formula would have said.
        $this->assertEquals(100, $this->ink->refresh()->stock_quantity);
    }

    public function test_requirements_ignores_options_with_no_materials_mapped(): void
    {
        $design = $this->design([
            'features' => ['led_lighting' => true],
            'elements' => ['text' => [['text' => 'a']], 'shapes' => [], 'logos' => [['scale' => 1]]],
        ]);

        $requirements = app(OrderStockService::class)->requirements($this->order('pending', 1, $design));

        // Only the product's own fabric — nothing has been mapped yet.
        $this->assertCount(1, $requirements['materials']);
        $this->assertEquals(3, $requirements['materials'][$this->fabric->raw_material_id]['quantity']);
    }
}
