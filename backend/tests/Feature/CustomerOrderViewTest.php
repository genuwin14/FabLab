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
