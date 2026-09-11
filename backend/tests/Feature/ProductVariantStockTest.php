<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Services\OrderStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Stock per size and colour.
 *
 * A garment was one figure. It is now a grid — a cell per size and colour,
 * each with its own stock — and the product's figure is the sum of the
 * cells. Every move of stock lands in a cell: the cart asks which, checkout
 * takes from it, cancellation gives back to it, and a cell running low
 * raises its own alert. A product with neither sizes nor colours keeps its
 * single figure and behaves exactly as before.
 */
class ProductVariantStockTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private Product $shirt;
    private Color $white;
    private Color $navy;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Mail::fake();

        $this->customer = User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
        $this->admin = User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456780', 'phone_verified' => true,
        ]);

        $category = Category::create(['name' => 'Cat', 'description' => 'x']);
        $this->white = Color::create(['name' => 'Classic White', 'hex_code' => '#f5f5f5']);
        $this->navy = Color::create(['name' => 'Navy Blue', 'hex_code' => '#1b2a4a']);

        // A hundred shirts, then sizes and two colours: the hundred sit in
        // the first cell until someone spreads them.
        $this->shirt = Product::create([
            'sku' => 'TS-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 100, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
            'is_customizable' => true, 'has_sizes' => true,
        ]);
        $this->shirt->colors()->sync([$this->white->color_id, $this->navy->color_id]);
        $this->shirt->ensureVariants();
    }

    private function cell(string $size, Color $color): ProductVariant
    {
        return $this->shirt->fresh()->variantFor($size, $color->color_id);
    }

    private function setCell(string $size, Color $color, int $stock): ProductVariant
    {
        return tap($this->cell($size, $color), fn ($cell) => $cell->update(['stock' => $stock]));
    }

    private function add(array $payload, int $quantity = 1)
    {
        return $this->postJson(route('customer.cart.add'), $payload + [
            'product_id' => $this->shirt->product_id,
            'quantity' => $quantity,
        ]);
    }

    // ---------------------------------------------------------------- the grid

    public function test_the_grid_is_every_size_by_every_colour_and_the_total_is_their_sum(): void
    {
        $variants = $this->shirt->variants;

        $this->assertCount(16, $variants, 'Eight sizes by two colours.');
        $this->assertSame(100, $variants->first()->stock, 'The figure the product had went into its first cell.');
        $this->assertSame(100, $variants->sum('stock'));
        $this->assertSame(100, $this->shirt->fresh()->stock);
        $this->assertSame('Classic White · S', $variants->first()->label);
    }

    public function test_moving_a_cell_re_sums_the_product(): void
    {
        $this->setCell('medium', $this->navy, 10);

        $this->assertSame(110, $this->shirt->fresh()->stock);

        $this->cell('medium', $this->navy)->decrement('stock', 4);
        $this->assertSame(106, $this->shirt->fresh()->stock);
    }

    public function test_a_one_size_product_with_no_colours_keeps_a_single_figure(): void
    {
        $mug = Product::create([
            'sku' => 'MG-1', 'name' => 'Mug', 'price' => 85, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $this->shirt->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
        ]);
        $mug->ensureVariants();

        $this->assertFalse($mug->tracksVariants());
        $this->assertCount(0, $mug->variants);
        $this->assertNull($mug->variantFor(null, null));

        // And the cart takes it without asking for a size or colour.
        Sanctum::actingAs($this->customer);
        $this->postJson(route('customer.cart.add'), ['product_id' => $mug->product_id, 'quantity' => 2])->assertOk();
        $this->post(route('customer.cart.checkout'), ['selected_items' => [(string) $mug->product_id]])->assertRedirect();

        $this->assertSame(18, $mug->fresh()->stock);
    }

    // ------------------------------------------------------------- the cart

    public function test_a_plain_add_to_cart_needs_a_size_and_a_colour(): void
    {
        $this->setCell('medium', $this->navy, 5);
        Sanctum::actingAs($this->customer);

        $this->add([])->assertStatus(422)->assertJsonPath('needs', 'size');
        $this->add(['size' => 'medium'])->assertStatus(422)->assertJsonPath('needs', 'colour');
        $this->add(['size' => 'medium', 'color_id' => $this->navy->color_id])->assertOk();

        $line = CartItem::sole();
        $this->assertSame($this->cell('medium', $this->navy)->product_variant_id, $line->product_variant_id);

        // The cart says which cell the line is for, and keys the line by it.
        $cart = $this->get('/customer/cart')->assertOk()->viewData('cart');
        $this->assertSame('Navy Blue · M', array_values($cart)[0]['variant']);
        $this->assertSame($this->shirt->product_id . '_v_' . $line->product_variant_id, array_key_first($cart));
    }

    public function test_an_empty_cell_cannot_be_added_however_full_the_others_are(): void
    {
        $this->setCell('5xl', $this->navy, 0);
        Sanctum::actingAs($this->customer);

        // A hundred shirts in stock, none of them a Navy 5XL.
        $this->add(['size' => '5xl', 'color_id' => $this->navy->color_id])
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'Insufficient stock! Only 0 pcs of Shirt (Navy Blue · 5XL) available.']);
    }

    public function test_two_cells_of_one_product_are_two_cart_lines(): void
    {
        $this->setCell('medium', $this->navy, 5);
        $this->setCell('large', $this->navy, 5);
        Sanctum::actingAs($this->customer);

        $this->add(['size' => 'medium', 'color_id' => $this->navy->color_id])->assertOk();
        $this->add(['size' => 'large', 'color_id' => $this->navy->color_id])->assertOk();
        $this->add(['size' => 'medium', 'color_id' => $this->navy->color_id])->assertOk();

        $lines = CartItem::orderBy('cart_item_id')->get();
        $this->assertCount(2, $lines);
        $this->assertSame(2, $lines[0]->quantity);
    }

    public function test_checkout_takes_the_cell_and_giving_back_returns_to_it(): void
    {
        $cell = $this->setCell('large', $this->navy, 5);
        Sanctum::actingAs($this->customer);
        $this->add(['size' => 'large', 'color_id' => $this->navy->color_id], 2)->assertOk();

        $key = $this->shirt->product_id . '_v_' . $cell->product_variant_id;
        $this->post(route('customer.cart.checkout'), ['selected_items' => [$key]])->assertRedirect();

        $this->assertSame(3, $cell->fresh()->stock);
        $this->assertSame(103, $this->shirt->fresh()->stock, '100 in the first cell, 5 in Navy L, 2 sold.');

        $order = Order::where('user_id', $this->customer->id)->sole();
        $this->assertSame($cell->product_variant_id, $order->orderItems->first()->product_variant_id);

        // Cancelling puts them back in the same cell, not on the total.
        app(OrderStockService::class)->returnProducts($order);
        $this->assertSame(5, $cell->fresh()->stock);
        $this->assertSame(105, $this->shirt->fresh()->stock);
    }

    public function test_a_customer_cancelling_returns_the_cell_too(): void
    {
        $cell = $this->setCell('large', $this->navy, 5);
        Sanctum::actingAs($this->customer);
        $this->add(['size' => 'large', 'color_id' => $this->navy->color_id], 2)->assertOk();
        $this->post(route('customer.cart.checkout'), ['selected_items' => [$this->shirt->product_id . '_v_' . $cell->product_variant_id]])->assertRedirect();

        $order = Order::where('user_id', $this->customer->id)->sole();
        $this->post("/customer/orders/{$order->order_id}/cancel")->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $cell->fresh()->stock);
    }

    // ---------------------------------------------------------- the studio

    public function test_a_design_takes_the_cell_its_recipe_names(): void
    {
        $this->setCell('large', $this->navy, 5);
        Sanctum::actingAs($this->customer);

        $this->add(['custom_recipe' => json_encode([
            'base_style' => 't-shirt', 'size' => 'large', 'color_id' => $this->navy->color_id,
            'elements' => ['text' => [], 'shapes' => [], 'logos' => []],
        ])])->assertOk();

        $this->assertSame($this->cell('large', $this->navy)->product_variant_id, CartItem::sole()->product_variant_id);
    }

    public function test_a_design_with_a_texture_finish_takes_the_first_colour(): void
    {
        // A texture is printed on a blank; the blank is the product's first colour.
        Sanctum::actingAs($this->customer);

        $this->add(['custom_recipe' => json_encode([
            'base_style' => 't-shirt', 'size' => 'small', 'texture_id' => 999,
            'elements' => ['text' => [], 'shapes' => [], 'logos' => []],
        ])])->assertOk();

        $this->assertSame($this->cell('small', $this->white)->product_variant_id, CartItem::sole()->product_variant_id);
    }

    public function test_a_design_for_an_empty_cell_is_refused(): void
    {
        Sanctum::actingAs($this->customer);

        $this->add(['custom_recipe' => json_encode([
            'base_style' => 't-shirt', 'size' => '5xl', 'color_id' => $this->navy->color_id,
            'elements' => ['text' => [], 'shapes' => [], 'logos' => []],
        ])])->assertStatus(400);
    }

    public function test_the_studio_shows_sizes_only_for_a_product_that_has_them(): void
    {
        $mug = Product::create([
            'sku' => 'MG-1', 'name' => 'Mug', 'price' => 85, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $this->shirt->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
            'is_customizable' => true,
        ]);
        Sanctum::actingAs($this->customer);

        $this->get(route('customer.customize.index', ['product_id' => $this->shirt->product_id]))
            ->assertOk()
            ->assertSee('1. Select Size')
            ->assertSee('hasSizes: true', false);

        $this->get(route('customer.customize.index', ['product_id' => $mug->product_id]))
            ->assertOk()
            ->assertDontSee('1. Select Size')
            ->assertSee('hasSizes: false', false);
    }

    // ----------------------------------------------------------- the alerts

    public function test_a_cell_raises_its_own_alert_naming_the_cell(): void
    {
        // The product's threshold is 2 over sixteen cells: a cell's own line
        // is its share of that, which rounds up to one.
        $cell = $this->setCell('5xl', $this->navy, 3);
        $this->assertSame(1.0, $cell->stockThreshold());

        $cell->decrement('stock', 1); // 3 -> 2: above the line, no alert
        Notification::assertNotSentTo($this->admin, LowStockAlert::class);

        $cell->decrement('stock', 1); // 2 -> 1: on the line

        Notification::assertSentTo($this->admin, LowStockAlert::class, function ($notification) {
            $data = $notification->toArray($this->admin);

            return $data['item_type'] === 'Product variant'
                && str_contains($data['title'], 'Shirt — Navy Blue · 5XL');
        });
    }

    public function test_a_cell_can_carry_its_own_threshold(): void
    {
        $cell = $this->setCell('medium', $this->navy, 10);
        $cell->update(['low_stock_threshold' => 5]);

        $this->assertSame(5.0, $cell->fresh()->stockThreshold());
    }

    // ------------------------------------------------------- the admin grid

    public function test_the_admin_sets_the_cells_and_the_total_follows(): void
    {
        $this->actingAs($this->admin);

        $navyMedium = ProductVariant::keyFor('medium', $this->navy->color_id);
        $whiteSmall = ProductVariant::keyFor('small', $this->white->color_id);

        $this->put(route('admin.products.update', $this->shirt->product_id), [
            'name' => 'Shirt', 'sku' => 'TS-1', 'category_id' => $this->shirt->category_id,
            'price' => 100, 'unit' => 'pcs', 'has_sizes' => 1,
            // The total field is read-only on screen; a stale figure posted
            // for it must not win over the cells.
            'stock' => 999,
            'variants' => [
                $navyMedium => ['stock' => 7],
                $whiteSmall => ['stock' => 1],
            ],
        ])->assertRedirect();

        $this->assertSame(7, $this->cell('medium', $this->navy)->stock);
        $this->assertSame(1, $this->cell('small', $this->white)->stock);
        $this->assertSame(8, $this->shirt->fresh()->stock);
    }

    public function test_unassigning_a_colour_moves_its_stock_to_a_cell_that_remains(): void
    {
        $this->setCell('medium', $this->navy, 10);
        $this->assertSame(110, $this->shirt->fresh()->stock);

        $this->shirt->colors()->sync([$this->white->color_id]);
        $this->shirt->unsetRelation('colors');
        $this->shirt->ensureVariants();

        $this->assertCount(8, $this->shirt->fresh()->variants, 'One colour left: eight sizes.');
        $this->assertSame(110, $this->shirt->fresh()->stock, 'Nothing lost.');
        $this->assertSame(110, $this->cell('small', $this->white)->stock, 'The navy tens landed in the first cell.');
    }

    public function test_switching_sizes_off_merges_each_colour_into_one_cell(): void
    {
        $this->setCell('medium', $this->navy, 10);
        $this->setCell('large', $this->navy, 5);

        $this->shirt->update(['has_sizes' => false]);
        $this->shirt->ensureVariants();

        $variants = $this->shirt->fresh()->variants;
        $this->assertCount(2, $variants, 'One cell per colour.');
        $this->assertSame(100, $this->shirt->fresh()->variantFor(null, $this->white->color_id)->stock);
        $this->assertSame(15, $this->shirt->fresh()->variantFor(null, $this->navy->color_id)->stock);
        $this->assertSame(115, $this->shirt->fresh()->stock);
    }

    public function test_the_product_list_shows_the_breakdown(): void
    {
        $this->setCell('5xl', $this->navy, 1);
        $this->actingAs($this->admin);

        // The list carries the cells on the button that opens the grid modal,
        // and says on it how many are low or empty: the Navy 5XL at one, and
        // the fourteen cells nothing has been spread into yet.
        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('by size / colour')
            ->assertSee('id="variantBreakdownModal"', false)
            ->assertSee('&quot;color&quot;:&quot;Navy Blue&quot;', false)
            ->assertSee('title="15 low or empty"', false);
    }
}
