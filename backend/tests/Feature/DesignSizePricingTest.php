<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The panel: "For customization, prices should vary depending on the size of
 * the design."
 *
 * Uploaded images were already charged by the size they print at. Text and
 * shapes were a flat fee whatever their size; they are now charged the same
 * way — their rate at 1×, in proportion to their Size slider — so a design
 * that covers more costs more. At 1× they cost what they always did.
 */
class DesignSizePricingTest extends TestCase
{
    use RefreshDatabase;

    private Product $mug;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Merch', 'description' => 'x']);
        $this->mug = Product::create([
            'sku' => 'MUG', 'name' => 'White Mug', 'price' => 100, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'is_customizable' => true,
        ]);
    }

    public function test_text_is_charged_by_the_size_it_prints_at(): void
    {
        $design = $this->design(['text' => [['text' => 'A', 'scale' => 1], ['text' => 'B', 'scale' => 2]]]);

        $this->assertSame([['label' => 'Custom text × 2 at 1×, 2× size', 'amount' => 150.0]], $design->price_breakdown);
        $this->assertSame(250.0, (float) $design->calculated_price);
    }

    public function test_shapes_are_charged_by_the_size_they_print_at(): void
    {
        $design = $this->design(['shapes' => [['type' => 'circle', 'scale' => 3], ['type' => 'line', 'scale' => 0.5]]]);

        $this->assertSame([['label' => 'Custom shapes × 2 at 3×, 0.5× size', 'amount' => 105.0]], $design->price_breakdown);
    }

    public function test_at_one_times_size_nothing_changes(): void
    {
        $design = $this->design([
            'text' => [['text' => 'A'], ['text' => 'B', 'scale' => 1]],
            'shapes' => [['type' => 'circle']],
        ]);

        $this->assertSame([
            ['label' => 'Custom text × 2', 'amount' => 100.0],
            ['label' => 'Custom shapes × 1', 'amount' => 30.0],
        ], $design->price_breakdown);
    }

    public function test_a_size_outside_the_slider_is_clamped(): void
    {
        $design = $this->design([
            'text' => [['text' => 'huge', 'scale' => 40], ['text' => 'tiny', 'scale' => 0.01]],
            'shapes' => [['type' => 'circle', 'scale' => -5]],
        ]);

        // Text runs 0.5× to 4×, shapes 0.1× to 5×.
        $this->assertSame(225.0, $design->price_breakdown[0]['amount']);
        $this->assertSame(3.0, $design->price_breakdown[1]['amount']);
    }

    public function test_what_a_design_draws_follows_its_size_too(): void
    {
        $design = $this->design([
            'text' => [['text' => 'A', 'scale' => 2.5]],
            'shapes' => [['type' => 'circle', 'scale' => 0.5], ['type' => 'circle']],
        ]);

        $this->assertSame(['text' => 2.5, 'shape' => 1.5], $design->customizationUnits());
    }

    public function test_the_cart_charges_a_bigger_design_more(): void
    {
        Sanctum::actingAs(User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]));

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $this->mug->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode(['elements' => ['text' => [['text' => 'BIG', 'scale' => 3]], 'shapes' => [], 'logos' => []]]),
        ])->assertOk();

        $this->assertSame(250.0, (float) \App\Models\CartItem::sole()->price);
    }

    public function test_the_studio_says_text_and_shapes_are_priced_by_size(): void
    {
        Sanctum::actingAs(User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]));

        $this->get(route('customer.customize.index', ['product_id' => $this->mug->product_id]))
            ->assertOk()
            ->assertSee('+₱50.00 each at 1&times; size, priced by size', false)
            ->assertSee('+₱30.00 each at 1&times; size, priced by size', false);
    }

    private function design(array $elements): CustomDesign
    {
        $design = new CustomDesign([
            'product_id' => $this->mug->product_id,
            'recipe' => ['elements' => $elements + ['text' => [], 'shapes' => [], 'logos' => []]],
        ]);
        $design->setRelation('product', $this->mug);

        return $design;
    }
}
