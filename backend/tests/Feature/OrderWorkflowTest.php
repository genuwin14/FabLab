<?php

namespace Tests\Feature;

use App\Mail\OrderReceipt;
use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The order pipeline's guard rails: review happens once and only on a pending
 * order, approval refuses to drive materials negative, cancelling after
 * approval gives everything back, and staff can only take the next step.
 */
class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;
    private RawMaterial $material;

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

        // One shirt eats 3 units of fabric.
        $this->material = RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 100, 'low_stock_threshold' => 5, 'unit' => 'm',
        ]);
        $this->product->rawMaterials()->attach($this->material->raw_material_id, ['quantity_required' => 3]);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function order(string $status = 'pending', int $quantity = 2, ?int $designId = null): Order
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
            'custom_design_id' => $designId,
            'quantity' => $quantity,
            'price' => 100,
        ]);

        return $order->refresh();
    }

    private function asAdmin(): void
    {
        Sanctum::actingAs($this->user('admin', 'a@example.test'));
    }

    // ---------------------------------------------------------------- review

    public function test_approving_consumes_materials_and_mails_the_slip(): void
    {
        $order = $this->order();
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertRedirect();

        $this->assertSame('approved', $order->refresh()->status);
        // 2 shirts x 3m = 6m off 100m
        $this->assertEquals(94, $this->material->refresh()->stock_quantity);
        Mail::assertSent(OrderReceipt::class);
    }

    public function test_an_order_can_only_be_reviewed_once(): void
    {
        $order = $this->order();
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);
        $this->assertEquals(94, $this->material->refresh()->stock_quantity);

        // A second review must not deduct the materials again.
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertSessionHas('error');

        $this->assertEquals(94, $this->material->refresh()->stock_quantity);
    }

    public function test_approval_is_refused_when_materials_are_short(): void
    {
        $this->material->update(['stock_quantity' => 5]); // needs 6
        $order = $this->order();
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->refresh()->status);
        $this->assertEquals(5, $this->material->refresh()->stock_quantity);
        Mail::assertNothingSent();
    }

    public function test_rejecting_returns_product_stock_and_needs_a_reason(): void
    {
        $order = $this->order();
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'cancelled'])
            ->assertSessionHasErrors('reason');

        $this->post("/admin/orders/{$order->order_id}/review", [
            'status' => 'cancelled',
            'reason' => 'Out of fabric',
        ])->assertRedirect();

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertSame('Out of fabric', $order->reason);
        $this->assertEquals(22, $this->product->refresh()->stock); // 20 + 2 returned
        $this->assertEquals(100, $this->material->refresh()->stock_quantity); // never consumed
    }

    // ---------------------------------------------------------------- cancel

    public function test_cancelling_an_approved_order_returns_everything(): void
    {
        $texture = Texture::create([
            'name' => 'Weave', 'price_modifier' => 0, 'stock_quantity' => 10,
            'low_stock_threshold' => 1, 'cost_per_unit' => 5, 'unit' => 'pcs',
        ]);
        $design = CustomDesign::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'recipe' => ['texture_id' => $texture->texture_id, 'elements' => [], 'features' => []],
        ]);

        $order = $this->order('pending', 2, $design->custom_design_id);
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);
        $this->assertEquals(94, $this->material->refresh()->stock_quantity);
        $this->assertEquals(8, $texture->refresh()->stock_quantity);

        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Customer changed their mind'])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('Customer changed their mind', $order->reason);
        $this->assertEquals(100, $this->material->refresh()->stock_quantity);
        $this->assertEquals(10, $texture->refresh()->stock_quantity);
        $this->assertEquals(22, $this->product->refresh()->stock);
    }

    public function test_cancelling_works_mid_production(): void
    {
        $order = $this->order('processing');
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Machine broke'])
            ->assertRedirect();

        $this->assertSame('cancelled', $order->refresh()->status);
    }

    public function test_cancel_needs_a_reason(): void
    {
        $order = $this->order('approved');
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/cancel", [])
            ->assertSessionHasErrors('reason');

        $this->assertSame('approved', $order->refresh()->status);
    }

    public function test_a_pending_order_is_rejected_at_review_not_cancelled(): void
    {
        $order = $this->order('pending');
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Nope'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_a_completed_order_cannot_be_cancelled(): void
    {
        $order = $this->order('completed');
        $this->asAdmin();

        $this->post("/admin/orders/{$order->order_id}/cancel", ['reason' => 'Too late'])
            ->assertSessionHas('error');

        $this->assertSame('completed', $order->refresh()->status);
        $this->assertEquals(20, $this->product->refresh()->stock); // nothing returned
    }

    // ------------------------------------------------------ staff pipeline

    public function test_staff_advance_one_step_at_a_time(): void
    {
        $order = $this->order('approved');
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->post("/staff/orders/{$order->order_id}/update-status", [
            'status' => 'processing', 'payment_reference' => 'OR-1234',
        ])->assertRedirect();

        $this->assertSame('processing', $order->refresh()->status);
        $this->assertSame('OR-1234', $order->payment_reference);

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'ready_for_pickup']);
        $this->assertSame('ready_for_pickup', $order->refresh()->status);

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'completed']);
        $this->assertSame('completed', $order->refresh()->status);
    }

    public function test_staff_cannot_skip_a_stage(): void
    {
        $order = $this->order('approved');
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'completed'])
            ->assertSessionHas('error');

        $this->assertSame('approved', $order->refresh()->status);
    }

    public function test_staff_cannot_move_an_order_backwards(): void
    {
        $order = $this->order('ready_for_pickup');
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->post("/staff/orders/{$order->order_id}/update-status", [
            'status' => 'processing', 'payment_reference' => 'OR-1',
        ])->assertSessionHas('error');

        $this->assertSame('ready_for_pickup', $order->refresh()->status);
    }

    public function test_staff_cannot_touch_an_unreviewed_order(): void
    {
        $order = $this->order('pending');
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->post("/staff/orders/{$order->order_id}/update-status", [
            'status' => 'processing', 'payment_reference' => 'OR-1',
        ])->assertSessionHas('error');

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_processing_requires_a_payment_reference(): void
    {
        $order = $this->order('approved');
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'processing'])
            ->assertSessionHasErrors('payment_reference');

        $this->assertSame('approved', $order->refresh()->status);
    }
}
