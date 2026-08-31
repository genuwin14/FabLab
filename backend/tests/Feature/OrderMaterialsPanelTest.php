<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The materials preview behind both order modals.
 *
 * The point of it is that the answer changes with the order's stage. Before
 * approval it is a forecast off the bills of materials, and a shortage is
 * worth shouting about. After approval it is a fact read back off the ledger,
 * because a BOM edited in between would make a fresh calculation disagree with
 * what the bench will actually consume.
 */
class OrderMaterialsPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;
    private RawMaterial $fabric;

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

        $this->fabric = RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 100, 'low_stock_threshold' => 5, 'unit' => 'm',
        ]);
        $this->product->rawMaterials()->attach($this->fabric->raw_material_id, ['quantity_required' => 3]);
    }

    private function user(string $role, string $email): User
    {
        return User::firstOrCreate(['email' => $email], [
            'fullname' => ucfirst($role), 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function order(string $status = 'pending', int $quantity = 2): Order
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

    public function test_a_pending_order_forecasts_what_approval_will_reserve(): void
    {
        $order = $this->order();
        $this->asAdmin();

        $this->get("/admin/orders/{$order->order_id}/materials")
            ->assertOk()
            ->assertJsonPath('stage', 'reserve')
            ->assertJsonPath('lines.0.name', 'Fabric')
            // 2 shirts x 3m, against 100m, leaving 94m.
            ->assertJsonPath('lines.0.quantity', '6')
            ->assertJsonPath('lines.0.stock', '100')
            ->assertJsonPath('lines.0.remaining', '94')
            ->assertJsonPath('lines.0.short', false)
            // The reviewer has to be able to correct this, which means the row
            // has to say so and name the material the correction applies to.
            // Both were silently dropped once by an array union that kept the
            // left operand's keys, and the table rendered read-only with no
            // error anywhere.
            ->assertJsonPath('lines.0.editable', true)
            ->assertJsonPath('lines.0.id', $this->fabric->raw_material_id)
            ->assertJsonPath('shortages', []);
    }

    public function test_a_material_line_carries_what_the_form_needs_to_correct_it(): void
    {
        $order = $this->order();
        $this->asAdmin();

        $lines = $this->get("/admin/orders/{$order->order_id}/materials")->assertOk()->json('lines');

        $this->assertNotEmpty($lines);
        foreach ($lines as $line) {
            $this->assertTrue($line['editable'], 'A raw material line should be correctable at review.');
            $this->assertIsInt($line['id']);
        }
    }

    public function test_nothing_is_correctable_once_the_stock_has_moved(): void
    {
        $order = $this->order();
        $this->asAdmin();
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);

        // Past approval the figure is a fact off the ledger, not an estimate,
        // so there is nothing left to weigh up.
        $this->get("/staff/orders/{$order->order_id}/materials")
            ->assertOk()
            ->assertJsonPath('stage', 'consume')
            ->assertJsonPath('lines.0.editable', false);
    }

    public function test_a_shortage_is_flagged_before_the_admin_approves(): void
    {
        $this->fabric->update(['stock_quantity' => 4]);
        $order = $this->order();
        $this->asAdmin();

        $this->get("/admin/orders/{$order->order_id}/materials")
            ->assertOk()
            ->assertJsonPath('lines.0.short', true)
            // Clamped at zero rather than shown as a negative shelf.
            ->assertJsonPath('lines.0.remaining', '0')
            ->assertJsonCount(1, 'shortages');
    }

    public function test_an_approved_order_reports_what_it_actually_reserved(): void
    {
        $order = $this->order();
        $this->asAdmin();
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);

        $this->asStaff();

        $response = $this->get("/staff/orders/{$order->order_id}/materials")
            ->assertOk()
            ->assertJsonPath('stage', 'consume')
            ->assertJsonPath('lines.0.quantity', '6')
            // Stock already left the shelf at approval, so there is no further
            // deduction to project and no shortage to warn about.
            ->assertJsonPath('lines.0.remaining', null)
            ->assertJsonPath('lines.0.short', false);

        $this->assertSame('94', $response->json('lines.0.stock'));
    }

    public function test_the_reserved_figure_wins_over_a_bom_edited_since_approval(): void
    {
        $order = $this->order();
        $this->asAdmin();
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);

        // The recipe is retuned after approval. The job at the bench was costed
        // at the old figure, so that is what staff must be shown.
        $this->product->rawMaterials()->sync([
            $this->fabric->raw_material_id => ['quantity_required' => 50],
        ]);

        $this->asStaff();
        $this->get("/staff/orders/{$order->order_id}/materials")
            ->assertOk()
            ->assertJsonPath('stage', 'consume')
            ->assertJsonPath('lines.0.quantity', '6');
    }

    public function test_an_order_in_production_reports_what_it_already_drew(): void
    {
        $order = $this->order();
        $this->asAdmin();
        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved']);

        $this->asStaff();
        $this->post("/staff/orders/{$order->order_id}/update-status", [
            'status' => 'processing', 'payment_reference' => 'OR-1',
        ]);

        $this->get("/staff/orders/{$order->order_id}/materials")
            ->assertOk()
            ->assertJsonPath('stage', 'consumed')
            ->assertJsonPath('lines.0.quantity', '6');
    }

    public function test_an_order_with_no_bill_of_materials_reports_nothing_to_draw(): void
    {
        $this->product->rawMaterials()->detach();
        $order = $this->order();
        $this->asAdmin();

        $this->get("/admin/orders/{$order->order_id}/materials")
            ->assertOk()
            ->assertJsonPath('stage', 'reserve')
            ->assertJsonPath('lines', []);
    }

    public function test_a_customer_cannot_read_what_an_order_costs_the_shop(): void
    {
        $order = $this->order();
        Sanctum::actingAs($this->customer);

        // Cost prices and shelf levels are the shop's business, not a
        // customer's — even for their own order.
        $this->get("/admin/orders/{$order->order_id}/materials")->assertRedirect();
        $this->get("/staff/orders/{$order->order_id}/materials")->assertRedirect();
    }
}
