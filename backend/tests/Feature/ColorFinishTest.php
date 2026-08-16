<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\CustomDesign;
use App\Models\Product;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A customer could only finish a product with an image texture, so a plain navy
 * shirt was unorderable. Colors are the plain-finish counterpart: admin-managed,
 * priced like a texture, and mutually exclusive with one.
 */
class ColorFinishTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'fullname' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function customer(): User
    {
        return User::create([
            'fullname' => 'Customer', 'email' => 'customer@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09987654321', 'phone_verified' => true,
        ]);
    }

    private function product(float $price = 1000): Product
    {
        $category = Category::create(['name' => 'Apparel', 'description' => 'Test category']);

        return Product::create([
            'sku' => 'TST-001', 'name' => 'Test Shirt', 'price' => $price, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active',
            'is_customizable' => true, 'low_stock_threshold' => 2,
        ]);
    }

    private function color(string $name = 'Navy Blue', float $surcharge = 0): Color
    {
        return Color::create([
            'name' => $name,
            'hex_code' => '#1b2a4a',
            'price_modifier' => $surcharge,
        ]);
    }

    private function texture(float $surcharge = 0, string $name = 'Canvas Weave'): Texture
    {
        return Texture::create([
            'name' => $name, 'price_modifier' => $surcharge,
            'stock_quantity' => 5, 'low_stock_threshold' => 1, 'cost_per_unit' => 50, 'unit' => 'pcs',
        ]);
    }

    // ── Admin management ────────────────────────────────────────────────

    public function test_an_admin_can_add_a_color(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.colors.store'), [
            'name' => 'Forest Green',
            'hex_code' => '#1F4D2E',
            'description' => 'Matte cotton',
            'price_modifier' => 40,
        ])->assertRedirect(route('admin.colors.index'));

        $color = Color::firstWhere('name', 'Forest Green');
        $this->assertNotNull($color);
        $this->assertSame('#1f4d2e', $color->hex_code, 'Hex codes are normalised to lower case.');
        $this->assertSame(40.0, $color->price_modifier);
    }

    public function test_a_malformed_hex_code_is_rejected(): void
    {
        $this->actingAs($this->admin());

        foreach (['red', '#FFF', '1b2a4a', '#12345g'] as $bad) {
            $this->post(route('admin.colors.store'), ['name' => 'Bad', 'hex_code' => $bad])
                ->assertSessionHasErrors('hex_code');
        }

        $this->assertSame(0, Color::count());
    }

    public function test_retiring_a_color_keeps_it_resolvable_for_saved_designs(): void
    {
        $color = $this->color('Navy Blue', 120);
        $this->actingAs($this->admin());

        $this->delete(route('admin.colors.destroy', $color->color_id))->assertRedirect();

        $this->assertSoftDeleted('colors', ['color_id' => $color->color_id]);
        $this->assertSame(0, Color::count(), 'A retired colour stops being offered.');

        // ...but a design already saved in it still prices correctly.
        $design = new CustomDesign(['recipe' => ['color_id' => $color->color_id]]);
        $this->assertSame(
            [['label' => 'Navy Blue finish', 'amount' => 120.0]],
            $design->price_breakdown
        );
    }

    public function test_a_customer_cannot_manage_colors(): void
    {
        $this->actingAs($this->customer());

        $this->post(route('admin.colors.store'), ['name' => 'Sneaky', 'hex_code' => '#000000'])
            ->assertForbidden();
        $this->assertSame(0, Color::count());
    }

    // ── Pricing ─────────────────────────────────────────────────────────

    public function test_a_color_surcharge_is_charged_to_the_cart(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);
        $color = $this->color('Navy Blue', 120);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode([
                'base_style' => 't-shirt',
                'color_id' => $color->color_id,
                'color_hex' => $color->hex_code,
                'elements' => [],
            ]),
        ])->assertOk();

        $this->assertSame(1120.0, (float) \App\Models\CartItem::first()->price);
    }

    public function test_a_free_color_adds_nothing(): void
    {
        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);
        $color = $this->color('Plain White', 0);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode(['color_id' => $color->color_id, 'elements' => []]),
        ])->assertOk();

        $this->assertSame(1000.0, (float) \App\Models\CartItem::first()->price);
    }

    public function test_a_recipe_naming_both_finishes_is_charged_for_one(): void
    {
        // The customizer can't produce this, but a hand-edited recipe could.
        // The texture wins; the colour must not stack on top of it.
        $texture = $this->texture(200);
        $color = $this->color('Navy Blue', 120);

        $design = new CustomDesign(['recipe' => [
            'texture_id' => $texture->texture_id,
            'color_id' => $color->color_id,
        ]]);

        $this->assertSame(
            [['label' => 'Canvas Weave texture', 'amount' => 200.0]],
            $design->price_breakdown
        );
    }

    public function test_the_admin_breakdown_names_the_color(): void
    {
        $color = $this->color('Sunset Orange', 75.5);

        $design = new CustomDesign(['recipe' => [
            'color_id' => $color->color_id,
            'elements' => ['text' => [['text' => 'Hi']]],
        ]]);

        $this->assertSame([
            ['label' => 'Custom text × 1', 'amount' => 50.0],
            ['label' => 'Sunset Orange finish', 'amount' => 75.5],
        ], $design->price_breakdown);
    }

    // ── Customizer wiring ───────────────────────────────────────────────

    public function test_the_customizer_offers_the_colors_assigned_to_the_product(): void
    {
        $product = $this->product();
        $assigned = $this->color('Navy Blue', 120);
        $unassigned = $this->color('Cherry Red', 0);
        $product->colors()->sync([$assigned->color_id]);

        Sanctum::actingAs($this->customer());

        $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('PLAIN COLORS')
            ->assertSee('data-color-id="' . $assigned->color_id . '"', false)
            ->assertDontSee('data-color-id="' . $unassigned->color_id . '"', false);
    }

    public function test_a_product_with_no_assignments_offers_every_color(): void
    {
        $product = $this->product();
        $a = $this->color('Navy Blue');
        $b = $this->color('Cherry Red');

        Sanctum::actingAs($this->customer());

        $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('data-color-id="' . $a->color_id . '"', false)
            ->assertSee('data-color-id="' . $b->color_id . '"', false);
    }

    public function test_a_texture_is_the_default_finish_when_the_product_has_one(): void
    {
        $product = $this->product();
        $this->color('Navy Blue');
        $texture = $this->texture();
        $product->textures()->sync([$texture->texture_id]);

        Sanctum::actingAs($this->customer());

        // The colour swatches render, but none of them starts selected.
        $html = $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('class="color-option "', $html);
        $this->assertStringNotContainsString('class="color-option active"', $html);
    }

    public function test_the_first_color_is_the_default_when_there_are_no_textures(): void
    {
        $product = $this->product();
        $this->color('Navy Blue');

        // Only this product's colours exist; it has no textures assigned and
        // the catalogue has none either.
        Sanctum::actingAs($this->customer());

        $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('class="color-option active"', false);
    }

    public function test_the_customizer_offers_only_the_textures_assigned_to_the_product(): void
    {
        $product = $this->product();
        $assigned = $this->texture();
        $unassigned = $this->texture(0, 'Denim');
        $product->textures()->sync([$assigned->texture_id]);

        Sanctum::actingAs($this->customer());

        $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('data-texture-id="' . $assigned->texture_id . '"', false)
            ->assertDontSee('data-texture-id="' . $unassigned->texture_id . '"', false);
    }

    public function test_a_product_with_no_textures_assigned_offers_none(): void
    {
        $product = $this->product();
        $this->color('Navy Blue');
        $this->texture();

        // Assigning nothing on the admin page used to offer the whole texture
        // catalogue here, the opposite of what that page promises.
        Sanctum::actingAs($this->customer());

        $html = $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('No textures available for this product.')
            ->getContent();

        $this->assertStringNotContainsString('data-texture-id=', $html);
    }

    public function test_a_product_with_neither_finish_says_so_once(): void
    {
        $product = $this->product();
        $this->texture();

        Sanctum::actingAs($this->customer());

        $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('No finishes available for this product.')
            ->assertDontSee('No textures available for this product.');
    }
}
