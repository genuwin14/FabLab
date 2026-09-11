<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Order details live in a per-order drawer on the orders page. The receipt is
 * reachable from every view of an order, and a status notification opens the
 * order it refers to rather than the whole list.
 */
class CustomerOrderViewTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function order(string $status): Order
    {
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);
        $product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
        ]);

        $order = Order::create([
            'order_number' => 'ORDR-VIEW', 'user_id' => $this->customer->id,
            'status' => $status, 'total_amount' => 100,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id, 'product_id' => $product->product_id,
            'quantity' => 1, 'price' => 100,
        ]);

        return $order;
    }

    public function test_an_approved_order_offers_its_receipt(): void
    {
        $order = $this->order('approved');
        Sanctum::actingAs($this->customer);

        $this->get('/customer/orders')
            ->assertOk()
            ->assertSee(route('customer.orders.receipt', $order->order_id), false);
    }

    public function test_a_pending_order_offers_no_receipt(): void
    {
        $order = $this->order('pending');
        Sanctum::actingAs($this->customer);

        $this->get('/customer/orders')
            ->assertOk()
            ->assertDontSee(route('customer.orders.receipt', $order->order_id), false);
    }

    public function test_the_receipt_streams_a_pdf(): void
    {
        $order = $this->order('completed');
        Sanctum::actingAs($this->customer);

        $response = $this->get("/customer/orders/{$order->order_id}/receipt")->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_a_pending_order_has_no_receipt_to_stream(): void
    {
        $order = $this->order('pending');
        Sanctum::actingAs($this->customer);

        $this->get("/customer/orders/{$order->order_id}/receipt")->assertNotFound();
    }

    public function test_one_customer_cannot_open_another_customers_receipt(): void
    {
        $order = $this->order('approved');

        $other = User::create([
            'fullname' => 'Other', 'email' => 'o@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);
        Sanctum::actingAs($other);

        $this->get("/customer/orders/{$order->order_id}/receipt")->assertNotFound();
    }

    public function test_a_status_notification_links_to_that_order(): void
    {
        $order = $this->order('processing');

        $data = (new OrderStatusChanged($order, 'approved', 'processing'))->toArray($this->customer);

        $this->assertStringEndsWith('#order-' . $order->order_id, $data['url']);
        $this->assertSame($order->order_id, $data['order_id']);
    }

    public function test_the_drawer_describes_a_tailored_item(): void
    {
        $order = $this->order('approved');
        $item = $order->orderItems()->first();

        $design = \App\Models\CustomDesign::create([
            'user_id' => $this->customer->id,
            'product_id' => $item->product_id,
            'recipe' => [
                'base_style' => 't-shirt',
                'size' => '2xl',
                'features' => ['led_lighting' => true],
                'elements' => [
                    'text' => [['text' => 'Team FabLab']],
                    'shapes' => [],
                    'logos' => [['scale' => 1]],
                ],
            ],
        ]);
        $item->update(['custom_design_id' => $design->custom_design_id, 'price' => 800]);

        Sanctum::actingAs($this->customer);

        $this->get('/customer/orders')
            ->assertOk()
            ->assertSee('Team FabLab')
            ->assertSee('2XL')
            ->assertSee('1 uploaded')
            ->assertSee('Internal LED')
            ->assertSee('Custom text × 1')
            ->assertSee('₱800.00');
    }

    public function test_the_drawer_names_the_size_and_colour_of_a_stock_item(): void
    {
        $order = $this->order('approved');
        $item = $order->orderItems()->first();

        $color = \App\Models\Color::create(['name' => 'Navy Blue', 'hex_code' => '#001f3f']);
        $variant = \App\Models\ProductVariant::create([
            'product_id' => $item->product_id, 'size' => 'large', 'color_id' => $color->color_id, 'stock' => 5,
        ]);
        $item->update(['product_variant_id' => $variant->product_variant_id, 'quantity' => 3]);

        Sanctum::actingAs($this->customer);

        $this->get('/customer/orders')
            ->assertOk()
            ->assertSee('Navy Blue · L')
            ->assertSee('₱100.00 × 3')
            ->assertSee('₱300.00');
    }

    public function test_the_drawer_tells_the_customer_the_receipt_number_to_bring(): void
    {
        $order = $this->order('ready_for_pickup');
        $order->update(['payment_reference' => 'OR-55123']);

        Sanctum::actingAs($this->customer);

        $this->get('/customer/orders')
            ->assertOk()
            ->assertSee('Receipt Number')
            ->assertSee('OR-55123')
            ->assertSee('Bring this number to the FabLab to collect your order.');
    }

    public function test_the_pickup_email_carries_the_receipt_number(): void
    {
        $order = $this->order('ready_for_pickup');
        $order->update(['payment_reference' => 'OR-55123']);

        $html = (new OrderStatusChanged($order, 'processing', 'ready_for_pickup'))
            ->toMail($this->customer)->render();

        $this->assertStringContainsString('RECEIPT NUMBER', $html);
        $this->assertStringContainsString('OR-55123', $html);
    }

    public function test_the_page_opens_the_drawer_named_in_the_fragment(): void
    {
        $order = $this->order('approved');
        Sanctum::actingAs($this->customer);

        $this->get('/customer/orders')
            ->assertOk()
            ->assertSee('orderDetails-' . $order->order_id, false)
            ->assertSee('/^#order-(\\d+)$/', false);
    }
}
