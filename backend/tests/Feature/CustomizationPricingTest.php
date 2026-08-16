<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\CustomizationRate;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The customizer's per-element fees used to be constants in two files. They now
 * live in customization_rates behind Admin → Customization Pricing, and both the
 * studio's live quote and the cart price designs from the same rows.
 */
class CustomizationPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The rates are memoised per request; a test process spans many.
        CustomizationRate::flushCache();
    }

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

    /** The four rates the admin form posts. */
    private function payload(array $overrides = []): array
    {
        $defaults = collect(CustomizationRate::DEFINITIONS)
            ->map(fn($definition) => $definition['default'])
            ->all();

        return ['rates' => array_merge($defaults, $overrides)];
    }

    public function test_the_migration_seeds_the_rates_the_system_already_charged(): void
    {
        $this->assertSame(50.0, CustomizationRate::amountFor('text'));
        $this->assertSame(30.0, CustomizationRate::amountFor('shape'));
        $this->assertSame(150.0, CustomizationRate::amountFor('logo'));
        $this->assertSame(500.0, CustomizationRate::amountFor('led_lighting'));
    }

    public function test_an_admin_sees_the_current_rates_on_the_pricing_page(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.customization-pricing.index'))
            ->assertOk()
            ->assertSee('Customization Pricing')
            ->assertSee('name="rates[logo]"', false)
            ->assertSee('value="150.00"', false);
    }

    public function test_an_admin_can_reprice_every_element(): void
    {
        $this->actingAs($this->admin());

        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'text' => 75,
            'shape' => 45.5,
            'logo' => 200,
            'led_lighting' => 600,
        ]))->assertRedirect(route('admin.customization-pricing.index'));

        CustomizationRate::flushCache();

        $this->assertSame(75.0, CustomizationRate::amountFor('text'));
        $this->assertSame(45.5, CustomizationRate::amountFor('shape'));
        $this->assertSame(200.0, CustomizationRate::amountFor('logo'));
        $this->assertSame(600.0, CustomizationRate::amountFor('led_lighting'));
    }

    public function test_a_negative_rate_is_rejected_and_nothing_is_saved(): void
    {
        $this->actingAs($this->admin());

        $this->put(route('admin.customization-pricing.update'), $this->payload(['logo' => -1]))
            ->assertSessionHasErrors('rates.logo');

        CustomizationRate::flushCache();
        $this->assertSame(150.0, CustomizationRate::amountFor('logo'), 'A rejected form must not move the price.');
    }

    public function test_a_non_numeric_rate_is_rejected(): void
    {
        $this->actingAs($this->admin());

        $this->put(route('admin.customization-pricing.update'), $this->payload(['text' => 'free']))
            ->assertSessionHasErrors('rates.text');
    }

    public function test_a_customer_cannot_reach_the_pricing_screen(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer);

        // EnsureUserHasRole sends a stray page visit home and refuses writes flat.
        $this->get(route('admin.customization-pricing.index'))
            ->assertRedirect(route($customer->homeRoute()));

        $this->put(route('admin.customization-pricing.update'), $this->payload())->assertForbidden();

        CustomizationRate::flushCache();
        $this->assertSame(150.0, CustomizationRate::amountFor('logo'));
    }

    public function test_a_repriced_image_changes_what_the_cart_charges(): void
    {
        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), $this->payload(['logo' => 300]))->assertRedirect();

        CustomizationRate::flushCache();

        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode([
                'base_style' => 't-shirt',
                'elements' => ['logos' => [['src' => 'data:image/png;base64,AAA', 'scale' => 2]]],
            ]),
        ])->assertOk();

        // 1000 base + the new 300 rate at 2x size
        $this->assertSame(1600.0, (float) \App\Models\CartItem::first()->price);
    }

    public function test_the_new_rates_reach_the_studios_live_quote(): void
    {
        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'text' => 80,
            'logo' => 250,
        ]))->assertRedirect();

        CustomizationRate::flushCache();

        $product = $this->product(1000);
        Sanctum::actingAs($this->customer());

        // What the customizer page hands its JavaScript has to match the rows
        // the cart will price against, or the quote misleads the customer.
        $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('"text":80', false)
            ->assertSee('"logo":250', false)
            ->assertSee('+₱250.00 each at 1&times; size', false);
    }

    public function test_a_missing_row_falls_back_to_the_shipped_default(): void
    {
        CustomizationRate::where('key', 'shape')->delete();
        CustomizationRate::flushCache();

        $this->assertSame(30.0, CustomizationRate::amountFor('shape'), 'A gap must not make an element free.');
    }

    public function test_repricing_does_not_touch_the_scale_clamp(): void
    {
        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), $this->payload(['logo' => 100]))->assertRedirect();

        CustomizationRate::flushCache();

        // Still bounded by the Size slider's own 0.1x - 5x range.
        $this->assertSame(10.0, CustomDesign::logoFee(0));
        $this->assertSame(500.0, CustomDesign::logoFee(99));
    }
}
