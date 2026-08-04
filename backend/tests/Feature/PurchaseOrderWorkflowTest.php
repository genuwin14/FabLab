<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use App\Notifications\PurchaseOrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Procurement is where stock comes back in: marking a purchase order
 * delivered is the click that restocks everything on it.
 */
class PurchaseOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Supplier $supplier;
    private Product $product;
    private RawMaterial $material;
    private Texture $texture;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::create([
            'fullname' => 'Staff', 'email' => 's@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);

        $this->supplier = Supplier::create(['name' => 'Acme', 'email' => 'acme@example.test']);
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        $this->product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 5, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 10,
        ]);

        $this->material = RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $this->supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 20, 'low_stock_threshold' => 50, 'unit' => 'm',
        ]);

        $this->texture = Texture::create([
            'name' => 'Weave', 'supplier_id' => $this->supplier->supplier_id, 'cost_per_unit' => 5,
            'stock_quantity' => 2, 'low_stock_threshold' => 10, 'unit' => 'pcs', 'price_modifier' => 0,
        ]);
    }

    private function purchaseOrder(string $status = 'draft'): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-' . strtoupper(uniqid()),
            'supplier_id' => $this->supplier->supplier_id,
            'status' => $status,
            'total_cost' => 500,
            'created_by' => $this->staff->id,
            'expected_delivery_date' => now()->addDays(3)->toDateString(),
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->purchase_order_id,
            'product_id' => $this->product->product_id, 'quantity' => 10, 'cost' => 20,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->purchase_order_id,
            'raw_material_id' => $this->material->raw_material_id, 'quantity' => 100, 'cost' => 10,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->purchase_order_id,
            'texture_id' => $this->texture->texture_id, 'quantity' => 25, 'cost' => 5,
        ]);

        return $po;
    }

    private function setStatus(PurchaseOrder $po, string $status)
    {
        return $this->put("/staff/purchase/{$po->purchase_order_id}/status", ['status' => $status]);
    }

    public function test_a_new_purchase_order_starts_as_a_draft(): void
    {
        Sanctum::actingAs($this->staff);

        $this->post('/staff/purchase', [
            'supplier_id' => $this->supplier->supplier_id,
            'expected_delivery_date' => now()->addWeek()->toDateString(),
            'items' => [
                ['product_id' => $this->product->product_id, 'quantity' => 10, 'cost' => 20],
                ['raw_material_id' => $this->material->raw_material_id, 'quantity' => 100, 'cost' => 10],
            ],
        ])->assertRedirect(route('staff.purchase.index'));

        $po = PurchaseOrder::firstOrFail();

        $this->assertSame('draft', $po->status);
        $this->assertSame($this->staff->id, $po->created_by);
        $this->assertEquals(1200, $po->total_cost); // 10*20 + 100*10
        $this->assertMatchesRegularExpression('/^PO-\d{8}-[A-Z0-9]{4}$/', $po->po_number);
        $this->assertCount(2, $po->items);

        // Nothing is restocked until it's delivered.
        $this->assertEquals(5, $this->product->refresh()->stock);
    }

    public function test_marking_delivered_restocks_every_line(): void
    {
        $po = $this->purchaseOrder('confirmed');
        Sanctum::actingAs($this->staff);

        $this->setStatus($po, 'delivered')->assertRedirect();

        $this->assertSame('delivered', $po->refresh()->status);
        $this->assertEquals(15, $this->product->refresh()->stock);          // 5 + 10
        $this->assertEquals(120, $this->material->refresh()->stock_quantity); // 20 + 100
        $this->assertEquals(27, $this->texture->refresh()->stock_quantity);   // 2 + 25
    }

    public function test_moving_back_out_of_delivered_takes_the_stock_off_again(): void
    {
        $po = $this->purchaseOrder('confirmed');
        Sanctum::actingAs($this->staff);

        $this->setStatus($po, 'delivered');
        $this->setStatus($po, 'cancelled');

        $this->assertSame('cancelled', $po->refresh()->status);
        $this->assertEquals(5, $this->product->refresh()->stock);
        $this->assertEquals(20, $this->material->refresh()->stock_quantity);
        $this->assertEquals(2, $this->texture->refresh()->stock_quantity);
    }

    public function test_earlier_statuses_move_no_stock(): void
    {
        $po = $this->purchaseOrder('draft');
        Sanctum::actingAs($this->staff);

        $this->setStatus($po, 'sent');
        $this->setStatus($po, 'confirmed');

        $this->assertSame('confirmed', $po->refresh()->status);
        $this->assertEquals(5, $this->product->refresh()->stock);
        $this->assertEquals(20, $this->material->refresh()->stock_quantity);
    }

    public function test_a_status_change_tells_the_team(): void
    {
        $admin = User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);

        $po = $this->purchaseOrder('draft');
        Sanctum::actingAs($this->staff);
        Notification::fake();

        $this->setStatus($po, 'sent');

        Notification::assertSentTo([$this->staff, $admin], PurchaseOrderStatusChanged::class);
    }

    public function test_restocking_past_a_threshold_stops_the_low_stock_alert(): void
    {
        $po = $this->purchaseOrder('confirmed');
        Sanctum::actingAs($this->staff);

        // The material sits under its threshold (20 of 50) before delivery.
        $this->assertTrue($this->material->stock_quantity < $this->material->low_stock_threshold);

        $this->setStatus($po, 'delivered');

        $this->assertTrue($this->material->refresh()->stock_quantity > $this->material->low_stock_threshold);
    }

    public function test_admins_run_the_same_workflow(): void
    {
        $admin = User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);

        $po = $this->purchaseOrder('confirmed');
        Sanctum::actingAs($admin);

        $this->put("/admin/purchase/{$po->purchase_order_id}/status", ['status' => 'delivered'])
            ->assertRedirect();

        $this->assertEquals(15, $this->product->refresh()->stock);
    }
}
