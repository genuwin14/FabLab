<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\CustomizationRate;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The panel: "Add size-based pricing for shirts, then must be reflected in the
 * inventory."
 *
 * Each size carries a surcharge (Customization Pricing → Sizes), bigger sizes
 * ship priced, and it is charged on a garment bought plain as well as on a
 * customised one. The shop quotes each size's price, and the product lists
 * and their stock grids show it beside each size's stock.
 */
class SizePricingTest extends TestCase
{
    use RefreshDatabase;

    private Product $shirt;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Apparel', 'description' => 'x']);
        $this->shirt = Product::create([
            'sku' => 'TS-1', 'name' => 'Cotton Tee', 'price' => 180, 'stock' => 80, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
            'is_customizable' => true, 'has_sizes' => true,
        ]);
        $this->shirt->ensureVariants();

        foreach ($this->shirt->variants as $cell) {
            $cell->update(['stock' => 10]);
        }
    }

    public function test_bigger_sizes_ship_with_a_surcharge(): void
    {
        $this->assertSame(
            ['small' => 180.0, 'medium' => 180.0, 'large' => 180.0, 'xl' => 200.0, '2xl' => 220.0, '3xl' => 240.0, '4xl' => 260.0, '5xl' => 280.0],
            $this->shirt->pricesBySize()
        );
        $this->assertTrue($this->shirt->priceVariesBySize());
    }

    public function test_a_plain_shirt_costs_its_sizes_price(): void
    {
        Sanctum::actingAs($this->customer());

        $this->postJson(route('customer.cart.add'), ['product_id' => $this->shirt->product_id, 'quantity' => 1, 'size' => '3xl'])->assertOk();
        $this->postJson(route('customer.cart.add'), ['product_id' => $this->shirt->product_id, 'quantity' => 1, 'size' => 'small'])->assertOk();

        $prices = CartItem::with('productVariant')->get()->mapWithKeys(fn ($line) => [$line->productVariant->size => (float) $line->price]);
        $this->assertSame(['3xl' => 240.0, 'small' => 180.0], $prices->all());
    }

    public function test_an_admin_repricing_a_size_changes_what_it_costs(): void
    {
        CustomizationRate::where('key', 'size_xl')->update(['amount' => 35]);
        CustomizationRate::flushCache();

        $this->assertSame(215.0, $this->shirt->fresh()->priceForSize('xl'));
    }

    public function test_a_product_without_sizes_costs_its_own_price(): void
    {
        $mug = Product::create([
            'sku' => 'MUG', 'name' => 'Mug', 'price' => 120, 'stock' => 5, 'unit' => 'pcs',
            'category_id' => $this->shirt->category_id, 'status' => 'active',
        ]);

        $this->assertSame(120.0, $mug->priceForSize('5xl'));
        $this->assertSame([], $mug->pricesBySize());
        $this->assertFalse($mug->priceVariesBySize());
    }

    public function test_the_shop_quotes_each_sizes_price(): void
    {
        Sanctum::actingAs($this->customer());

        $this->get(route('customer.shop'))
            ->assertOk()
            ->assertSee('Sizes from')
            ->assertSee('From ₱180.00', false)
            ->assertSee('&quot;price&quot;:280', false)
            ->assertSee('Bigger sizes cost more. The price follows the size you pick.');
    }

    public function test_the_product_lists_show_the_price_beside_each_sizes_stock(): void
    {
        $admin = User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456780', 'phone_verified' => true,
        ]);
        Sanctum::actingAs($admin);

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('₱180.00–₱280.00 by size', false)
            ->assertSee('data-prices="{&quot;small&quot;:180', false)
            ->assertSee('const variantGridSurcharges', false);
    }

    public function test_the_migration_prices_sizes_only_where_none_were_ever_priced(): void
    {
        $migration = require database_path('migrations/2026_11_24_000002_price_the_bigger_garment_sizes.php');

        DB::table('customization_rates')->where('key', 'like', 'size_%')->update(['amount' => 0]);
        $migration->up();
        $this->assertSame(100.0, (float) DB::table('customization_rates')->where('key', 'size_5xl')->value('amount'));

        // An admin who priced even one size keeps exactly what they set.
        DB::table('customization_rates')->where('key', 'like', 'size_%')->update(['amount' => 0]);
        DB::table('customization_rates')->where('key', 'size_2xl')->update(['amount' => 15]);
        $migration->up();
        $this->assertSame(0.0, (float) DB::table('customization_rates')->where('key', 'size_5xl')->value('amount'));
        $this->assertSame(15.0, (float) DB::table('customization_rates')->where('key', 'size_2xl')->value('amount'));
    }

    private function customer(): User
    {
        return User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }
}
