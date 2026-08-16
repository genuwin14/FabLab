<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomDesign;
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

    /**
     * A design carrying a single uploaded image printed at $scale, nothing else.
     */
    private function logoRecipe(float $scale): string
    {
        return json_encode([
            'base_style' => 't-shirt',
            'size' => 'medium',
            'texture_id' => null,
            'features' => ['led_lighting' => false],
            'elements' => [
                'text' => [],
                'shapes' => [],
                'logos' => [['src' => 'data:image/png;base64,AAA', 'scale' => $scale]],
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

    public function test_enlarging_an_uploaded_image_raises_its_fee(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => $this->logoRecipe(2.5),
        ])->assertOk();

        // 1000 base + 150 x 2.5 for an image printed at two-and-a-half size
        $this->assertSame(1375.0, $this->cartLinePrice());
    }

    public function test_shrinking_an_uploaded_image_lowers_its_fee(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => $this->logoRecipe(0.4),
        ])->assertOk();

        // 1000 base + 150 x 0.4 for an image printed at under half size
        $this->assertSame(1060.0, $this->cartLinePrice());
    }

    public function test_image_scale_outside_the_sliders_range_is_clamped(): void
    {
        // A hand-edited recipe must not be able to buy a free or runaway logo.
        $this->assertSame(15.0, CustomDesign::logoFee(-99));
        $this->assertSame(15.0, CustomDesign::logoFee(0));
        $this->assertSame(750.0, CustomDesign::logoFee(1000));
        $this->assertSame(150.0, CustomDesign::logoFee(null), 'A recipe with no scale is standard size.');
    }

    public function test_each_uploaded_image_is_itemised_at_the_size_it_prints(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $response = $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode([
                'base_style' => 't-shirt',
                'features' => ['led_lighting' => false],
                'elements' => [
                    'logos' => [
                        ['src' => 'data:image/png;base64,AAA', 'scale' => 0.5],
                        ['src' => 'data:image/png;base64,BBB', 'scale' => 3],
                    ],
                ],
            ]),
        ])->assertOk();

        $design = CustomDesign::findOrFail($response->json('design_id'));

        // What the admin order inspector lists for this design.
        $this->assertSame([
            ['label' => 'Uploaded image 1 at 0.5× size', 'amount' => 75.0],
            ['label' => 'Uploaded image 2 at 3× size', 'amount' => 450.0],
        ], $design->price_breakdown);

        $this->assertSame(1525.0, $this->cartLinePrice());
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
