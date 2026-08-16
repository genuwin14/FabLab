<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A saved recipe stores only a texture_id; rendering.js resolves the image out
 * of CustomizerConfig.textures. Every page that previews a design in 3D had
 * omitted that catalogue, so a customized model rendered blank white however it
 * actually looked — the snapshot thumbnail beside it showed the real thing.
 */
class DesignPreviewRenderingTest extends TestCase
{
    use RefreshDatabase;

    private function texture(string $name = 'Canvas Weave'): Texture
    {
        return Texture::create([
            'name' => $name, 'image_path' => 'textures/canvas.png', 'price_modifier' => 0,
            'stock_quantity' => 5, 'low_stock_threshold' => 1, 'cost_per_unit' => 50, 'unit' => 'pcs',
        ]);
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Apparel', 'description' => 'Test category']);

        return Product::create([
            'sku' => 'TST-001', 'name' => 'Test Shirt', 'price' => 1000, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active',
            'is_customizable' => true, 'low_stock_threshold' => 2,
        ]);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function design(User $owner, Product $product, Texture $texture): CustomDesign
    {
        return CustomDesign::create([
            'user_id' => $owner->id,
            'product_id' => $product->product_id,
            'recipe' => ['base_style' => 't-shirt', 'texture_id' => $texture->texture_id, 'elements' => []],
            'snapshot' => 'data:image/png;base64,AAA',
        ]);
    }

    private function orderWithDesign(User $customer, Product $product, CustomDesign $design): Order
    {
        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 1000,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'custom_design_id' => $design->custom_design_id,
            'quantity' => 1,
            'price' => 1000,
        ]);

        return $order;
    }

    public function test_my_designs_ships_the_texture_catalogue_to_its_preview(): void
    {
        $customer = $this->user('customer', 'customer@example.test');
        $texture = $this->texture();
        $this->design($customer, $this->product(), $texture);

        $this->actingAs($customer)
            ->get(route('customer.customize.my-designs'))
            ->assertOk()
            ->assertSee('"id":' . $texture->texture_id, false)
            ->assertSee('canvas.png', false);
    }

    public function test_the_admin_order_inspector_ships_the_texture_catalogue(): void
    {
        $customer = $this->user('customer', 'customer@example.test');
        $product = $this->product();
        $texture = $this->texture();
        $this->orderWithDesign($customer, $product, $this->design($customer, $product, $texture));

        $this->actingAs($this->user('admin', 'admin@example.test'))
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('"id":' . $texture->texture_id, false)
            ->assertSee('canvas.png', false);
    }

    public function test_the_staff_order_inspector_ships_the_texture_catalogue(): void
    {
        $customer = $this->user('customer', 'customer@example.test');
        $product = $this->product();
        $texture = $this->texture();
        $this->orderWithDesign($customer, $product, $this->design($customer, $product, $texture));

        $this->actingAs($this->user('staff', 'staff@example.test'))
            ->get(route('staff.orders.index'))
            ->assertOk()
            ->assertSee('"id":' . $texture->texture_id, false)
            ->assertSee('canvas.png', false);
    }

    public function test_a_retired_texture_still_resolves_for_designs_that_use_it(): void
    {
        $customer = $this->user('customer', 'customer@example.test');
        $texture = $this->texture('Retired Weave');
        $this->design($customer, $this->product(), $texture);
        $texture->delete();

        // It stops being offered, but a design already wearing it must still
        // render rather than falling back to blank white.
        $this->actingAs($customer)
            ->get(route('customer.customize.my-designs'))
            ->assertOk()
            ->assertSee('"id":' . $texture->texture_id, false);
    }

    public function test_the_catalogue_carries_what_the_renderer_reads(): void
    {
        $texture = $this->texture();

        $payload = Texture::customizerPayload()->firstWhere('id', $texture->texture_id);

        $this->assertNotNull($payload);
        $this->assertSame(['id', 'name', 'image_path', 'price_modifier'], array_keys($payload));
        $this->assertStringContainsString('canvas.png', $payload['image_path']);
    }
}
