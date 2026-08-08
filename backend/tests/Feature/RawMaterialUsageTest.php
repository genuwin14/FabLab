<?php

namespace Tests\Feature;

use App\Enums\StockMovementReason;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Raw material stock used to be typed straight into the edit form while the
 * four report counters were typed separately, so a material could show 40
 * consumed having never once dropped. Stock now moves only through the usage
 * ledger, which keeps the counter, the shelf and the history in step.
 */
class RawMaterialUsageTest extends TestCase
{
    use RefreshDatabase;

    private RawMaterial $material;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 'sup@example.test']);

        $this->material = RawMaterial::create([
            'name' => 'Metal Clip', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 5,
            'stock_quantity' => 100, 'low_stock_threshold' => 10, 'unit' => 'pcs',
        ]);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function asAdmin(): User
    {
        $admin = $this->user('admin', 'a@example.test');
        Sanctum::actingAs($admin);

        return $admin;
    }

    private function asStaff(): User
    {
        $staff = $this->user('staff', 'st@example.test');
        Sanctum::actingAs($staff);

        return $staff;
    }

    private function record(array $payload, string $prefix = 'admin')
    {
        return $this->post("/{$prefix}/raw-materials/{$this->material->raw_material_id}/usage", $payload);
    }

    // ------------------------------------------------------------- recording

    public function test_recording_consumption_moves_stock_and_the_counter_together(): void
    {
        $admin = $this->asAdmin();

        $this->record(['reason' => 'consumed', 'quantity' => 12, 'note' => '40 ID laces'])
            ->assertRedirect();

        $this->material->refresh();
        $this->assertEquals(88, $this->material->stock_quantity);
        $this->assertEquals(12, $this->material->units_consumed);

        $movement = RawMaterialMovement::firstOrFail();
        $this->assertSame(StockMovementReason::Consumed, $movement->reason);
        $this->assertEquals(-12, $movement->stock_delta);
        $this->assertEquals(88, $movement->stock_after);
        $this->assertSame($admin->id, $movement->user_id);
        $this->assertSame('40 ID laces', $movement->note);
    }

    public function test_damaged_and_sponsored_also_leave_the_shelf(): void
    {
        $this->asAdmin();

        $this->record(['reason' => 'damaged', 'quantity' => 5]);
        $this->record(['reason' => 'sponsored', 'quantity' => 3]);

        $this->material->refresh();
        $this->assertEquals(92, $this->material->stock_quantity);
        $this->assertEquals(5, $this->material->units_damaged);
        $this->assertEquals(3, $this->material->units_sponsored);
    }

    public function test_display_units_are_tagged_without_touching_stock(): void
    {
        $this->asAdmin();

        $this->record(['reason' => 'on_display', 'quantity' => 6])->assertRedirect();

        $this->material->refresh();
        // Still owned by the shop — just standing in a cabinet.
        $this->assertEquals(100, $this->material->stock_quantity);
        $this->assertEquals(6, $this->material->units_on_display);
        $this->assertEquals(0, RawMaterialMovement::firstOrFail()->stock_delta);
    }

    public function test_recording_more_than_the_shop_holds_is_refused(): void
    {
        $this->asAdmin();

        $this->record(['reason' => 'consumed', 'quantity' => 101])
            ->assertSessionHas('error');

        $this->assertEquals(100, $this->material->refresh()->stock_quantity);
        $this->assertSame(0, RawMaterialMovement::count());
    }

    public function test_a_zero_quantity_is_refused(): void
    {
        $this->asAdmin();

        $this->record(['reason' => 'consumed', 'quantity' => 0])
            ->assertSessionHas('error');

        $this->assertSame(0, RawMaterialMovement::count());
    }

    // ------------------------------------------------------------ correction

    public function test_a_correction_reconciles_against_a_physical_count(): void
    {
        $this->asAdmin();

        // Someone counted 93 on the shelf against the 100 on record.
        $this->record(['reason' => 'correction', 'quantity' => 93])->assertRedirect();

        $this->material->refresh();
        $this->assertEquals(93, $this->material->stock_quantity);
        // A correction is not consumption, so no report counter moves.
        $this->assertEquals(0, $this->material->units_consumed);

        $movement = RawMaterialMovement::firstOrFail();
        $this->assertEquals(-7, $movement->stock_delta);
        $this->assertEquals(7, $movement->quantity);
    }

    public function test_a_correction_can_raise_stock_too(): void
    {
        $this->asAdmin();

        $this->record(['reason' => 'correction', 'quantity' => 118]);

        $this->assertEquals(118, $this->material->refresh()->stock_quantity);
        $this->assertEquals(18, RawMaterialMovement::firstOrFail()->stock_delta);
    }

    public function test_staff_cannot_correct_stock(): void
    {
        $this->asStaff();

        $this->record(['reason' => 'correction', 'quantity' => 5], 'staff')
            ->assertSessionHasErrors('reason');

        $this->assertEquals(100, $this->material->refresh()->stock_quantity);
    }

    public function test_staff_can_record_ordinary_usage(): void
    {
        $staff = $this->asStaff();

        $this->record(['reason' => 'damaged', 'quantity' => 4], 'staff')->assertRedirect();

        $this->assertEquals(96, $this->material->refresh()->stock_quantity);
        $this->assertSame($staff->id, RawMaterialMovement::firstOrFail()->user_id);
    }

    // -------------------------------------------------------------- reversal

    public function test_reversing_puts_the_stock_and_the_counter_back(): void
    {
        $this->asAdmin();
        $this->record(['reason' => 'consumed', 'quantity' => 20]);

        $movement = RawMaterialMovement::firstOrFail();

        $this->post("/admin/raw-material-movements/{$movement->movement_id}/reverse")
            ->assertRedirect();

        $this->material->refresh();
        $this->assertEquals(100, $this->material->stock_quantity);
        $this->assertEquals(0, $this->material->units_consumed);

        // The original stays put — the log shows the mistake and the fix.
        $this->assertSame(2, RawMaterialMovement::count());
        $this->assertNotNull($movement->refresh()->reversal);
    }

    public function test_an_entry_cannot_be_reversed_twice(): void
    {
        $this->asAdmin();
        $this->record(['reason' => 'consumed', 'quantity' => 20]);
        $movement = RawMaterialMovement::firstOrFail();

        $this->post("/admin/raw-material-movements/{$movement->movement_id}/reverse");
        $this->post("/admin/raw-material-movements/{$movement->movement_id}/reverse")
            ->assertSessionHas('error');

        $this->assertEquals(100, $this->material->refresh()->stock_quantity);
        $this->assertSame(2, RawMaterialMovement::count());
    }

    public function test_reversing_a_correction_that_added_stock_cannot_go_negative(): void
    {
        $this->asAdmin();

        // Correct up to 118, then consume most of it.
        $this->record(['reason' => 'correction', 'quantity' => 118]);
        $correction = RawMaterialMovement::firstOrFail();
        $this->record(['reason' => 'consumed', 'quantity' => 110]);

        // Undoing the +18 would need 18 back off a shelf holding 8.
        $this->post("/admin/raw-material-movements/{$correction->movement_id}/reverse")
            ->assertSessionHas('error');

        $this->assertEquals(8, $this->material->refresh()->stock_quantity);
    }

    // ----------------------------------------------------------- form guards

    public function test_the_edit_form_can_no_longer_overwrite_stock(): void
    {
        $this->asAdmin();

        $this->put("/admin/raw-materials/{$this->material->raw_material_id}", [
            'name' => 'Metal Clip', 'supplier_id' => $this->material->supplier_id,
            'cost_per_unit' => 5, 'low_stock_threshold' => 10, 'unit' => 'pcs',
            // The old form posted these; they must now be ignored.
            'stock_quantity' => 4000, 'units_consumed' => 999,
        ])->assertRedirect();

        $this->material->refresh();
        $this->assertEquals(100, $this->material->stock_quantity);
        $this->assertEquals(0, $this->material->units_consumed);
    }

    public function test_creating_a_material_cannot_seed_the_report_counters(): void
    {
        $this->asAdmin();

        $this->post('/admin/raw-materials', [
            'name' => 'Woven Strap', 'supplier_id' => $this->material->supplier_id,
            'cost_per_unit' => 8, 'stock_quantity' => 50, 'low_stock_threshold' => 5,
            'unit' => 'm', 'units_consumed' => 300, 'units_damaged' => 12,
        ])->assertRedirect();

        $strap = RawMaterial::where('name', 'Woven Strap')->firstOrFail();
        $this->assertEquals(50, $strap->stock_quantity);
        $this->assertEquals(0, $strap->units_consumed);
        $this->assertEquals(0, $strap->units_damaged);
    }

    // ------------------------------------------------------------ the screen

    public function test_both_tabs_render_for_admin_and_staff(): void
    {
        $admin = $this->asAdmin();
        $staff = $this->user('staff', 'st@example.test');

        $this->record(['reason' => 'consumed', 'quantity' => 7, 'note' => 'First batch']);

        foreach (['admin' => $admin, 'staff' => $staff] as $prefix => $actor) {
            Sanctum::actingAs($actor);

            $this->get("/{$prefix}/raw-materials")
                ->assertOk()
                ->assertSee('Usage Log')
                ->assertSee('Record Usage')
                ->assertSee('Metal Clip');

            $this->get("/{$prefix}/raw-materials?tab=log")
                ->assertOk()
                ->assertSee('Consumed')
                ->assertSee('First batch');
        }
    }

    public function test_the_log_filters_by_material_and_reason(): void
    {
        $this->asAdmin();
        $this->record(['reason' => 'consumed', 'quantity' => 7, 'note' => 'Lace batch']);
        $this->record(['reason' => 'damaged', 'quantity' => 2, 'note' => 'Bent in the press']);

        $this->get('/admin/raw-materials?tab=log&log_reason=damaged')
            ->assertOk()
            ->assertSee('Bent in the press')
            ->assertDontSee('Lace batch');
    }

    // ---------------------------------------------------------- order wiring

    public function test_approving_an_order_writes_a_ledger_row_and_raises_units_consumed(): void
    {
        $order = $this->orderFor(quantity: 4, perUnit: 3);
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertRedirect();

        $this->material->refresh();
        $this->assertEquals(88, $this->material->stock_quantity);   // 100 - (4 x 3)
        // The gap this whole feature closes: approvals used to move stock and
        // leave this counter untouched.
        $this->assertEquals(12, $this->material->units_consumed);

        $movement = RawMaterialMovement::firstOrFail();
        $this->assertSame($order->order_id, $movement->order_id);
        $this->assertSame(StockMovementReason::Consumed, $movement->reason);
    }

    public function test_cancelling_an_approved_order_reverses_its_ledger_rows(): void
    {
        $order = $this->orderFor(quantity: 4, perUnit: 3);
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);
        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Customer changed their mind']);

        $this->material->refresh();
        $this->assertEquals(100, $this->material->stock_quantity);
        $this->assertEquals(0, $this->material->units_consumed);
        $this->assertSame(2, RawMaterialMovement::count());
    }

    public function test_cancelling_returns_what_the_order_took_not_what_the_bom_says_today(): void
    {
        $product = $this->productFor(perUnit: 3);
        $order = $this->orderFor(quantity: 4, product: $product);
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);
        $this->assertEquals(88, $this->material->refresh()->stock_quantity);

        // The recipe changes after approval — 5 clips per lace instead of 3.
        $product->rawMaterials()->sync([
            $this->material->raw_material_id => ['quantity_required' => 5],
        ]);

        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Recipe changed']);

        // Giving back 20 would invent 8 clips the order never took.
        $this->assertEquals(100, $this->material->refresh()->stock_quantity);
    }

    private function productFor(float $perUnit): Product
    {
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        $product = Product::create([
            'sku' => 'ID-LACE', 'name' => 'ID Lace', 'price' => 60, 'stock' => 50, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 5,
        ]);

        $product->rawMaterials()->attach($this->material->raw_material_id, ['quantity_required' => $perUnit]);

        return $product;
    }

    private function orderFor(int $quantity, float $perUnit = 3, ?Product $product = null): Order
    {
        $product ??= $this->productFor($perUnit);
        $customer = $this->user('customer', 'cust' . uniqid() . '@example.test');

        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 60 * $quantity,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => $quantity,
            'price' => 60,
        ]);

        return $order->refresh();
    }
}
