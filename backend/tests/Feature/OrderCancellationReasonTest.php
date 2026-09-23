<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Rejecting a pending order, cancelling one past review, and closing one held
 * for its PR number all need a reason: the customer reads it on their order.
 * The Review modal used to let an empty reason through to the server, which
 * refused it only after the page had reloaded; it now stops it in place, and
 * the server says what is missing in plain words.
 */
class OrderCancellationReasonTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = $this->user('admin', 'a@example.test');
        $this->customer = $this->user('customer', 'c@example.test');

        $category = Category::create(['name' => 'Cat', 'description' => 'x']);
        $this->product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
        ]);
    }

    public static function blankReasons(): array
    {
        return [
            'missing' => [null],
            'empty' => [''],
            'only spaces' => ['    '],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('blankReasons')]
    public function test_a_pending_order_is_not_rejected_without_a_reason(?string $reason): void
    {
        $order = $this->order('pending');
        Sanctum::actingAs($this->admin);

        $this->from(route('admin.orders.index'))
            ->post(route('admin.orders.review', $order->order_id), array_filter(['status' => 'cancelled', 'reason' => $reason], fn ($v) => $v !== null))
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHasErrors(['reason' => 'Give a reason for rejecting this order. The customer reads it on their order.']);

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->reason);
        $this->assertSame(18, $this->product->fresh()->stock, 'nothing handed back');
    }

    public function test_a_reason_rejects_it_and_the_customer_sees_why(): void
    {
        $order = $this->order('pending');
        Sanctum::actingAs($this->admin);

        $this->post(route('admin.orders.review', $order->order_id), ['status' => 'cancelled', 'reason' => '  Insufficient stock  '])
            ->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('Insufficient stock', $order->fresh()->reason);

        Sanctum::actingAs($this->customer);
        $this->get(route('customer.orders.index'))->assertSee('Reason: Insufficient stock');
    }

    public function test_approving_needs_no_reason(): void
    {
        $order = $this->order('pending');
        Sanctum::actingAs($this->admin);

        $this->post(route('admin.orders.review', $order->order_id), ['status' => 'approved'])
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $order->fresh()->status);
    }

    public function test_an_order_past_review_is_not_cancelled_without_a_reason(): void
    {
        $order = $this->order('approved');
        Sanctum::actingAs($this->admin);

        $this->post(route('admin.orders.cancel', $order->order_id), ['reason' => '   '])
            ->assertSessionHasErrors(['reason' => 'Give a reason for cancelling this order. The customer reads it on their order.']);

        $this->assertSame('approved', $order->fresh()->status);
    }

    public function test_a_held_order_is_not_closed_without_a_reason(): void
    {
        $order = $this->order('awaiting_pr', Order::METHOD_PR);
        Sanctum::actingAs($this->admin);

        $this->post(route('admin.orders.closePr', $order->order_id), ['reason' => ''])
            ->assertSessionHasErrors(['reason' => 'Give a reason for closing this order. The customer reads it on their order.']);

        $this->assertSame('awaiting_pr', $order->fresh()->status);
    }

    public function test_an_overlong_reason_is_refused(): void
    {
        $order = $this->order('pending');
        Sanctum::actingAs($this->admin);

        $this->post(route('admin.orders.review', $order->order_id), ['status' => 'cancelled', 'reason' => str_repeat('x', 1001)])
            ->assertSessionHasErrors(['reason' => 'Keep the reason under 1,000 characters.']);

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_the_review_and_close_dialogs_mark_the_reason_required_and_check_it(): void
    {
        $this->order('pending');
        $this->order('awaiting_pr', Order::METHOD_PR);
        Sanctum::actingAs($this->admin);

        $html = $this->get(route('admin.orders.index'))->assertOk()->getContent();

        // Both reason boxes carry the marker the check looks for...
        $this->assertMatchesRegularExpression('/<textarea name="reason" id="reviewReason"[^>]*\sdata-required-reason[\s>]/', $html);
        $this->assertMatchesRegularExpression('/<textarea name="reason" id="closePrReason"[^>]*\sdata-required-reason\s+required[\s>]/', $html);
        $this->assertSame(2, substr_count($html, '<span class="order-required-pill ms-2">Required</span>'));

        // ...the Review modal checks before its scripted submit...
        $this->assertStringContainsString("if (status === 'cancelled' && !window.requireReason(document.getElementById('reviewReason')))", $html);

        // ...and the check itself is on the page, once.
        $this->assertSame(1, substr_count($html, 'window.requireReason = function (field)'));
    }

    private function order(string $status, string $method = Order::METHOD_CASH): Order
    {
        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'status' => $status,
            'payment_method' => $method,
            'office' => $method === Order::METHOD_PR ? 'Office of the Registrar' : null,
            'pr_deadline' => $method === Order::METHOD_PR ? now()->addDays(7) : null,
            'total_amount' => 200,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'price' => 100,
        ]);

        // Checkout took the two shirts off the shelf.
        $this->product->decrement('stock', 2);

        return $order;
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }
}
