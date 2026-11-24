<?php

namespace Tests\Feature;

use App\Mail\OrderReceipt;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The panel: "Highlight/emphasize the cost provided by the system is just
 * estimation and will require confirmation from the shop."
 *
 * Every customer screen that quotes a price calls it an estimate, and the
 * shop confirms it when an admin approves the order: each line can be
 * re-priced in the review, the total follows, the estimate is kept beside it,
 * and the customer is told the confirmed figure.
 */
class PriceConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->customer = $this->user('customer', 'c@example.test');

        $category = Category::create(['name' => 'Merch', 'description' => 'x']);
        $this->product = Product::create([
            'sku' => 'MUG-1', 'name' => 'White Mug', 'price' => 100, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
            'is_customizable' => true,
        ]);
    }

    // ---------------------------------------------------------------- checkout

    public function test_checkout_keeps_the_estimate_beside_the_price(): void
    {
        Sanctum::actingAs($this->customer);
        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $this->product->product_id, 'quantity' => 2, 'price' => 100]);

        $this->postJson(route('customer.cart.checkout'), ['selected_items' => [(string) $this->product->product_id]])
            ->assertOk()->assertJson(['success' => true]);

        $order = Order::with('orderItems')->sole();
        $this->assertSame([200.0, 200.0], [(float) $order->total_amount, $order->estimatedTotal()]);
        $this->assertSame(100.0, (float) $order->orderItems->first()->estimated_price);
        $this->assertFalse($order->priceIsConfirmed());
    }

    // ---------------------------------------------------------------- review

    public function test_approving_unchanged_confirms_the_estimate(): void
    {
        $order = $this->order();
        $this->asAdmin();

        $this->post(route('admin.orders.review', $order->order_id), ['status' => 'approved'])->assertRedirect();

        $order->refresh();
        $this->assertSame('approved', $order->status);
        $this->assertSame(200.0, (float) $order->total_amount);
        $this->assertFalse($order->priceWasAdjusted());
        $this->assertSame(
            'Your order is now Approved. FabLab confirmed the total at ₱200.00.',
            $this->customer->notifications()->where('type', OrderStatusChanged::class)->sole()->data['body']
        );
    }

    public function test_the_admin_can_confirm_a_different_price_when_approving(): void
    {
        $order = $this->order();
        $item = $order->orderItems->first();
        $this->asAdmin();

        $this->post(route('admin.orders.review', $order->order_id), [
            'status' => 'approved',
            'confirmed_prices' => [$item->order_item_id => '130.50'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $order->refresh()->load('orderItems');
        $item->refresh();

        $this->assertSame([130.5, 100.0], [(float) $item->price, (float) $item->estimated_price]);
        $this->assertSame([261.0, 200.0], [(float) $order->total_amount, $order->estimatedTotal()]);
        $this->assertTrue($order->priceIsConfirmed());
        $this->assertTrue($order->priceWasAdjusted());

        $this->assertSame(
            'Your order is now Approved. FabLab confirmed the total at ₱261.00 (your estimate was ₱200.00).',
            $this->customer->notifications()->where('type', OrderStatusChanged::class)->sole()->data['body']
        );

        // The slip charges the confirmed price, and the email says so.
        Mail::assertSent(OrderReceipt::class, function (OrderReceipt $mail) {
            $html = $mail->render();

            return str_contains($html, '261.00') && str_contains($html, 'The estimate at checkout was');
        });
        $slip = view('emails.orders.transaction-slip', ['order' => $order])->render();
        $this->assertStringContainsString('P261.00', $slip);
    }

    public function test_prices_ride_along_with_a_rejection_without_changing_anything(): void
    {
        $order = $this->order();
        $item = $order->orderItems->first();
        $this->asAdmin();

        $this->post(route('admin.orders.review', $order->order_id), [
            'status' => 'cancelled',
            'reason' => 'Out of blanks',
            'confirmed_prices' => [$item->order_item_id => '-5'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertSame(100.0, (float) $item->refresh()->price);
    }

    public function test_a_negative_confirmed_price_is_refused(): void
    {
        $order = $this->order();
        $item = $order->orderItems->first();
        $this->asAdmin();

        $this->post(route('admin.orders.review', $order->order_id), [
            'status' => 'approved',
            'confirmed_prices' => [$item->order_item_id => '-1'],
        ])->assertSessionHasErrors(['confirmed_prices.' . $item->order_item_id => 'A confirmed price cannot be below zero.']);

        $this->assertSame('pending', $order->refresh()->status);
        $this->assertSame(100.0, (float) $item->refresh()->price);
    }

    public function test_a_price_for_another_orders_line_is_ignored(): void
    {
        $order = $this->order();
        $other = $this->order();
        $foreign = $other->orderItems->first();
        $this->asAdmin();

        $this->post(route('admin.orders.review', $order->order_id), [
            'status' => 'approved',
            'confirmed_prices' => [$foreign->order_item_id => '999'],
        ])->assertRedirect();

        $this->assertSame(200.0, (float) $order->refresh()->total_amount);
        $this->assertSame(100.0, (float) $foreign->refresh()->price);
    }

    // ---------------------------------------------------------------- screens

    public function test_the_review_modal_offers_the_price_confirmation(): void
    {
        $this->order();
        $this->asAdmin();

        $this->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Price Confirmation')
            ->assertSee('id="reviewPriceBody"', false)
            ->assertSee('fillPriceConfirmation(items);', false);
    }

    public function test_the_customer_sees_an_estimate_until_the_shop_confirms_it(): void
    {
        $order = $this->order();
        Sanctum::actingAs($this->customer);

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Estimated Total')
            ->assertSee('FabLab confirms the final price when it')
            ->assertDontSee('Confirmed Total');

        $order->orderItems->first()->update(['price' => 120]);
        $order->update(['status' => 'approved', 'total_amount' => 240]);

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Confirmed Total')
            ->assertSee('est. ₱200.00');
    }

    public function test_the_cart_checkout_and_studio_call_the_price_an_estimate(): void
    {
        Sanctum::actingAs($this->customer);
        CartItem::create(['user_id' => $this->customer->id, 'product_id' => $this->product->product_id, 'quantity' => 1, 'price' => 100]);

        $this->get(route('customer.cart.index'))
            ->assertOk()
            ->assertSee('Estimated Total')
            ->assertSee('This is an estimate, not the final price.')
            ->assertSee('Estimate only.');

        $this->get(route('customer.customize.index', ['product_id' => $this->product->product_id]))
            ->assertOk()
            ->assertSee('Estimated Price')
            ->assertSee('FabLab reviews every design and confirms the final price before you pay.');
    }

    // ---------------------------------------------------------------- helpers

    private function order(): Order
    {
        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total_amount' => 200,
            'estimated_total' => 200,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'price' => 100,
            'estimated_price' => 100,
        ]);

        return $order->refresh()->load('orderItems');
    }

    private function asAdmin(): void
    {
        Sanctum::actingAs($this->user('admin', 'a@example.test'));
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
            'status' => 'active', 'notifications_enabled' => true,
        ]);
    }
}
