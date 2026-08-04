<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartCustomPricingTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::create([
            'fullname' => 'Test Customer',
            'email' => 'customer@example.test',
            'password' => 'password',
            'role' => 'customer',
            'contact_number' => '09123456789',
            'phone_verified' => true,
        ]);
    }

    private function product(float $price = 1000): Product
    {
        $category = Category::create(['name' => 'Apparel', 'description' => 'Test category']);

        return Product::create([
            'sku' => 'TST-001',
            'name' => 'Test Shirt',
            'price' => $price,
            'stock' => 10,
            'unit' => 'pcs',
            'category_id' => $category->category_id,
            'status' => 'active',
            'is_customizable' => true,
            'low_stock_threshold' => 2,
        ]);
    }

    /**
     * The recipe the customizer serialises: two text elements, one logo,
     * LED lighting on. 2*50 + 150 + 500 = 750 of element fees.
     */
    private function recipe(?int $textureId): string
    {
        return json_encode([
            'base_style' => 't-shirt',
            'size' => 'medium',
            'texture_id' => $textureId,
            'features' => ['led_lighting' => true],
            'elements' => [
                'text' => [['content' => 'A'], ['content' => 'B']],
                'shapes' => [],
                'logos' => [['src' => 'data:image/png;base64,AAA']],
            ],
        ]);
    }

    private function cartLinePrice(): float
    {
        $lines = \App\Models\CartItem::all();
        $this->assertCount(1, $lines, 'Expected exactly one cart line.');

        return (float) $lines->first()->price;
    }

    public function test_texture_price_modifier_is_charged_to_the_cart(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $texture = Texture::create([
            'name' => 'Premium Weave',
            'price_modifier' => 250,
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'cost_per_unit' => 100,
            'unit' => 'pcs',
        ]);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => $this->recipe($texture->texture_id),
        ])->assertOk();

        // 1000 base + 750 elements + 250 texture surcharge
        $this->assertSame(2000.0, $this->cartLinePrice());
    }

    public function test_design_without_a_texture_is_charged_element_fees_only(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => $this->recipe(null),
        ])->assertOk();

        // 1000 base + 750 elements, no texture selected
        $this->assertSame(1750.0, $this->cartLinePrice());
    }

    public function test_cart_price_matches_the_saved_design_price(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $texture = Texture::create([
            'name' => 'Matte',
            'price_modifier' => 75.5,
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'cost_per_unit' => 40,
            'unit' => 'pcs',
        ]);

        $response = $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => $this->recipe($texture->texture_id),
        ])->assertOk();

        $design = \App\Models\CustomDesign::findOrFail($response->json('design_id'));

        // What My Designs shows and what the cart charges must not diverge.
        $this->assertSame((float) $design->calculated_price, $this->cartLinePrice());
    }

    public function test_uncustomised_product_is_charged_its_base_price(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 2,
        ])->assertOk();

        $this->assertSame(1000.0, $this->cartLinePrice());
    }
}
