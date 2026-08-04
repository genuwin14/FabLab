<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The cart used to live in the session, so signing out emptied it. It now has
 * a table, keyed per user, with a line per product-and-design combination.
 */
class PersistentCartTest extends TestCase
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

        $category = Category::create(['name' => 'Cat', 'description' => 'x']);
        $this->product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
        ]);
    }

    private function addToCart(int $quantity = 1): void
    {
        $this->postJson(route('customer.cart.add'), [
            'product_id' => $this->product->product_id,
            'quantity' => $quantity,
        ])->assertOk();
    }

    public function test_a_cart_outlives_the_session(): void
    {
        Sanctum::actingAs($this->customer);
        $this->addToCart(2);

        // A brand new "session" — the cart is still there.
        Sanctum::actingAs($this->customer->fresh());

        $cart = $this->get('/customer/cart')->assertOk()->viewData('cart');

        $this->assertCount(1, $cart);
        $this->assertSame(2, reset($cart)['quantity']);
    }

    public function test_carts_are_private_to_their_owner(): void
    {
        Sanctum::actingAs($this->customer);
        $this->addToCart(3);

        $other = User::create([
            'fullname' => 'Other', 'email' => 'o@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);
        Sanctum::actingAs($other);

        $this->assertSame(0, $this->getJson(route('customer.cart.count'))->json('count'));
    }

    public function test_adding_the_same_product_twice_stacks_one_line(): void
    {
        Sanctum::actingAs($this->customer);
        $this->addToCart(2);
        $this->addToCart(3);

        $this->assertSame(1, CartItem::where('user_id', $this->customer->id)->count());
        $this->assertSame(5, $this->getJson(route('customer.cart.count'))->json('count'));
    }

    public function test_two_designs_of_one_product_are_two_lines(): void
    {
        Sanctum::actingAs($this->customer);

        foreach (['A', 'B'] as $label) {
            $this->postJson(route('customer.cart.add'), [
                'product_id' => $this->product->product_id,
                'quantity' => 1,
                'custom_recipe' => json_encode([
                    'texture_id' => null,
                    'features' => [],
                    'elements' => ['text' => [['content' => $label]], 'shapes' => [], 'logos' => []],
                ]),
            ])->assertOk();
        }

        $this->assertSame(2, CartItem::where('user_id', $this->customer->id)->count());
        $this->assertSame(2, CustomDesign::where('user_id', $this->customer->id)->count());
    }

    public function test_stock_is_respected_when_adding(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $this->product->product_id,
            'quantity' => 99,
        ])->assertStatus(400);

        $this->assertSame(0, CartItem::count());
    }

    public function test_quantities_can_be_changed_and_lines_removed(): void
    {
        Sanctum::actingAs($this->customer);
        $this->addToCart(1);

        $key = (string) $this->product->product_id;

        $this->postJson(route('customer.cart.update'), ['product_id' => $key, 'quantity' => 4])->assertOk();
        $this->assertSame(4, CartItem::first()->quantity);

        // Beyond stock is refused and the line is left alone.
        $this->postJson(route('customer.cart.update'), ['product_id' => $key, 'quantity' => 99])->assertStatus(400);
        $this->assertSame(4, CartItem::first()->quantity);

        $this->postJson(route('customer.cart.remove'), ['product_id' => $key])->assertOk();
        $this->assertSame(0, CartItem::count());
    }

    public function test_checkout_clears_only_the_selected_lines(): void
    {
        $second = Product::create([
            'sku' => 'P-2', 'name' => 'Mug', 'price' => 50, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $this->product->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
        ]);

        Sanctum::actingAs($this->customer);
        $this->addToCart(2);
        $this->postJson(route('customer.cart.add'), ['product_id' => $second->product_id, 'quantity' => 1])->assertOk();

        $this->post(route('customer.cart.checkout'), [
            'selected_items' => [(string) $this->product->product_id],
        ])->assertRedirect(route('customer.orders.index'));

        $remaining = CartItem::where('user_id', $this->customer->id)->get();
        $this->assertCount(1, $remaining);
        $this->assertSame($second->product_id, $remaining->first()->product_id);

        $order = Order::where('user_id', $this->customer->id)->firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertEquals(200, $order->total_amount);
        $this->assertEquals(8, $this->product->refresh()->stock); // 10 - 2
    }

    /** Carts left in a session when this shipped are carried over, not lost. */
    public function test_a_legacy_session_cart_is_absorbed(): void
    {
        Sanctum::actingAs($this->customer);

        session(['cart' => [
            (string) $this->product->product_id => [
                'product_id' => $this->product->product_id,
                'custom_design_id' => null,
                'quantity' => 3,
                'price' => 100,
            ],
        ]]);

        $cart = $this->get('/customer/cart')->assertOk()->viewData('cart');

        $this->assertCount(1, $cart);
        $this->assertSame(3, reset($cart)['quantity']);
        $this->assertSame(1, CartItem::where('user_id', $this->customer->id)->count());
        $this->assertEmpty(session('cart', []));
    }
}
