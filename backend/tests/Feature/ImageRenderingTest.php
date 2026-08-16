<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Every page that shows an item's picture was reading the raw column, which
 * now holds a path rather than a data URI. These render each of them and check
 * the markup carries a servable URL.
 */
class ImageRenderingTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private Texture $texture;
    private RawMaterial $material;

    protected function setUp(): void
    {
        parent::setUp();

        $supplier = Supplier::create(['name' => 'Acme', 'email' => 'acme@example.test']);
        $category = Category::create(['name' => 'Apparel', 'description' => 'x']);

        $this->product = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 3, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active',
            'low_stock_threshold' => 10, 'image' => 'products/shirt.jpg',
        ]);

        $this->texture = Texture::create([
            'name' => 'Weave', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 5,
            'stock_quantity' => 1, 'low_stock_threshold' => 10, 'unit' => 'pcs',
            'price_modifier' => 0, 'image_path' => 'textures/weave.png',
        ]);

        $this->material = RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 1, 'low_stock_threshold' => 10, 'unit' => 'm',
            'image_path' => 'raw-materials/fabric.png',
        ]);
    }

    private function signInAs(string $role): User
    {
        $user = User::create([
            'fullname' => ucfirst($role), 'email' => "{$role}@example.test", 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    public static function adminPages(): array
    {
        return [
            'products' => ['/admin/products', 'products/shirt.jpg'],
            'textures' => ['/admin/textures', 'textures/weave.png'],
            'raw materials' => ['/admin/raw-materials', 'raw-materials/fabric.png'],
            'inventory watchlist' => ['/admin/inventory', 'products/shirt.jpg'],
            'dashboard' => ['/admin/dashboard', 'products/shirt.jpg'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('adminPages')]
    public function test_admin_pages_render_stored_image_urls(string $url, string $expected): void
    {
        $this->signInAs('admin');

        $this->get($url)->assertOk()->assertSee('/storage/' . $expected, false);
    }

    public function test_staff_pages_render_stored_image_urls(): void
    {
        $this->signInAs('staff');

        $this->get('/staff/products')->assertOk()->assertSee('/storage/products/shirt.jpg', false);
        $this->get('/staff/textures')->assertOk()->assertSee('/storage/textures/weave.png', false);
        $this->get('/staff/raw-materials')->assertOk()->assertSee('/storage/raw-materials/fabric.png', false);
        $this->get('/staff/inventory')->assertOk()->assertSee('/storage/products/shirt.jpg', false);
    }

    public function test_the_shop_and_customizer_render_stored_image_urls(): void
    {
        $this->signInAs('customer');

        $this->get('/customer/shop')->assertOk()->assertSee('/storage/products/shirt.jpg', false);

        // The studio hands texture URLs to three.js — but only for the textures
        // assigned to the product, so this one has to be one of them.
        $this->product->textures()->sync([$this->texture->texture_id]);

        $this->get('/customer/customize?product_id=' . $this->product->product_id)
            ->assertOk()
            ->assertSee('/storage/textures/weave.png', false);
    }

    public function test_the_cart_renders_stored_image_urls(): void
    {
        $customer = $this->signInAs('customer');

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $this->product->product_id,
            'quantity' => 1,
        ])->assertOk();

        $this->get('/customer/cart')->assertOk()->assertSee('/storage/products/shirt.jpg', false);
    }

    public function test_order_views_render_stored_image_urls(): void
    {
        $customer = $this->signInAs('customer');

        $order = Order::create([
            'order_number' => 'ORDR-IMG', 'user_id' => $customer->id,
            'status' => 'approved', 'total_amount' => 100,
        ]);
        OrderItem::create([
            'order_id' => $order->order_id, 'product_id' => $this->product->product_id,
            'quantity' => 1, 'price' => 100,
        ]);

        // Customer's own view (details drawer).
        $this->get('/customer/orders')->assertOk()->assertSee('/storage/products/shirt.jpg', false);

        // Admin list serialises orders into the review/view modals as JSON.
        $this->signInAs('admin');
        $this->get('/admin/orders')->assertOk()->assertSee('image_url', false);
    }

    public function test_no_page_leaks_a_raw_storage_path_as_an_image_source(): void
    {
        $this->signInAs('admin');

        $html = $this->get('/admin/products')->assertOk()->getContent();

        // The bare column value must never end up in markup on its own.
        $this->assertStringNotContainsString('url(\'products/shirt.jpg\')', $html);
        $this->assertStringNotContainsString('src="products/shirt.jpg"', $html);
    }
}
