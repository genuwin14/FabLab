<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Assign Textures / Assign Colors pickers.
 *
 * Deselecting a card used to leave it looking selected: the tick badge carried
 * Bootstrap's `.d-flex`, which is `display: flex !important`, and the JS hid it
 * with an inline `display: none` that the !important rule outranked. The
 * checkbox unticked but nothing on screen said so. Selection is CSS-driven off
 * `:checked` now, so there is no second copy of the state to fall out of step —
 * these guard the markup that made that possible.
 */
class ProductAssignmentUiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'fullname' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
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

    /** Ticked checkboxes, counted across the tag since the attributes span lines. */
    private function countCheckedBoxes(string $html): int
    {
        return preg_match_all('/<input\b[^>]*\bchecked\b[^>]*>/s', $html);
    }

    public function test_the_texture_picker_marks_selection_without_an_overridable_inline_style(): void
    {
        $product = $this->product();
        $assigned = Texture::create([
            'name' => 'Canvas Weave', 'price_modifier' => 0, 'stock_quantity' => 5,
            'low_stock_threshold' => 1, 'cost_per_unit' => 50, 'unit' => 'pcs',
        ]);
        $spare = Texture::create([
            'name' => 'Denim', 'price_modifier' => 0, 'stock_quantity' => 5,
            'low_stock_threshold' => 1, 'cost_per_unit' => 50, 'unit' => 'pcs',
        ]);
        $product->textures()->sync([$assigned->texture_id]);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.products.textures.assign', $product->product_id))
            ->assertOk()
            ->getContent();

        // Both cards render, and only the assigned one is ticked.
        $this->assertStringContainsString('value="' . $assigned->texture_id . '"', $html);
        $this->assertStringContainsString('value="' . $spare->texture_id . '"', $html);
        $this->assertSame(1, $this->countCheckedBoxes($html));

        $this->assertStringNotContainsString(
            'texture-check-badge position-absolute top-0 end-0 m-2 rounded-circle bg-primary text-white d-flex',
            $html,
            'The tick badge must not carry .d-flex — its !important beats any inline display used to hide it.'
        );
        $this->assertSame(
            0,
            preg_match('/check-badge[^>]*style="[^"]*display:/', $html),
            'The badge must carry no inline display, or CSS cannot hide it on deselect.'
        );
    }

    public function test_the_color_picker_marks_selection_the_same_way(): void
    {
        $product = $this->product();
        $assigned = Color::create(['name' => 'Navy Blue', 'hex_code' => '#1b2a4a', 'price_modifier' => 0]);
        Color::create(['name' => 'Cherry Red', 'hex_code' => '#b02a37', 'price_modifier' => 0]);
        $product->colors()->sync([$assigned->color_id]);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.products.colors.assign', $product->product_id))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, $this->countCheckedBoxes($html));

        $this->assertStringNotContainsString(
            'color-check-badge position-absolute top-0 end-0 m-2 rounded-circle bg-primary text-white d-flex',
            $html,
            'Same trap as the texture picker — this page was built from it.'
        );
        $this->assertSame(
            0,
            preg_match('/check-badge[^>]*style="[^"]*display:/', $html),
            'The badge must carry no inline display, or CSS cannot hide it on deselect.'
        );
    }
}
