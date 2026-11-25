<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlaced;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Checkout confirms itself to the customer by email as well as the in-app
 * bell — including a Purchase Request order, which gets its filing
 * instructions in writing because nothing else emails the customer until an
 * admin reviews it.
 */
class OrderPlacedEmailTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);

        $category = Category::create(['name' => 'Mugs', 'description' => 'x']);

        $this->product = Product::create([
            'name' => 'Mug', 'sku' => 'MG-1', 'category_id' => $category->category_id,
            'price' => 100, 'stock' => 20, 'is_customizable' => false,
        ]);
    }

    private function checkout(string $method): Order
    {
        Sanctum::actingAs($this->customer);

        $line = CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'price' => 100,
        ]);

        $this->post(route('customer.cart.checkout'), [
            'selected_items' => [(string) $line->cart_item_id],
            'payment_method' => $method,
            // A Purchase Request is always filed by an office.
            'office' => $method === Order::METHOD_PR ? 'Office of the Registrar' : null,
        ]);

        return Order::latest('order_id')->first();
    }

    public function test_checkout_emails_the_customer_a_confirmation(): void
    {
        Notification::fake();

        $this->checkout(Order::METHOD_CASH);

        Notification::assertSentTo(
            $this->customer,
            OrderPlaced::class,
            fn ($notification, $channels) => in_array('mail', $channels, true)
                && in_array('database', $channels, true)
        );
    }

    public function test_a_cash_confirmation_lists_the_items_and_says_review_is_next(): void
    {
        $order = $this->checkout(Order::METHOD_CASH);

        $mail = (new OrderPlaced($order))->toMail($this->customer);
        $html = view($mail->view, $mail->viewData)->render();

        $this->assertStringContainsString("Order {$order->order_number} - Received", $mail->subject);
        $this->assertStringContainsString('Mug', $html);
        $this->assertStringContainsString('200.00', $html);
        $this->assertStringContainsString('awaiting review', $html);
    }

    public function test_a_pr_confirmation_carries_the_filing_instructions(): void
    {
        config(['fablab.pr_deadline_days' => 3, 'fablab.procurement_email' => 'procurement@example.test']);

        $order = $this->checkout(Order::METHOD_PR);

        $mail = (new OrderPlaced($order))->toMail($this->customer);
        $html = view($mail->view, $mail->viewData)->render();

        $this->assertStringContainsString('procurement@example.test', $html);
        $this->assertStringContainsString($order->pr_deadline->format('j M Y'), $html);
    }

    /**
     * The Settings switch is about the in-app bell. The confirmation email
     * follows what the customer ticked at checkout.
     */
    public function test_turning_notifications_off_silences_the_bell_but_not_the_confirmation_email(): void
    {
        Notification::fake();

        $this->customer->update(['notifications_enabled' => false]);

        $this->checkout(Order::METHOD_CASH);

        Notification::assertSentTo(
            $this->customer,
            OrderPlaced::class,
            fn ($notification, $channels) => $channels === ['mail']
        );
    }

    public function test_with_the_bell_off_and_email_unticked_nothing_is_sent(): void
    {
        Notification::fake();

        $this->customer->update(['notifications_enabled' => false]);

        Sanctum::actingAs($this->customer);
        $line = CartItem::create([
            'user_id' => $this->customer->id, 'product_id' => $this->product->product_id,
            'quantity' => 1, 'price' => 100,
        ]);

        $this->post(route('customer.cart.checkout'), [
            'selected_items' => [$line->lineKey()],
            'payment_method' => Order::METHOD_CASH,
            'notify_email' => 0,
            'notify_sms' => 0,
        ]);

        $this->assertSame(1, Order::count());
        Notification::assertNotSentTo($this->customer, OrderPlaced::class);
    }
}
