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

        // A shirt comes in sizes; the studio only shows size buttons for a
        // product that does, and only a size with stock can be ordered — so
        // ten of every size.
        $product = Product::create([
            'sku' => 'TST-001', 'name' => 'Test Shirt', 'price' => $price, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active',
            'is_customizable' => true, 'has_sizes' => true, 'low_stock_threshold' => 2,
        ]);
        $product->ensureVariants();
        \App\Models\ProductVariant::where('product_id', $product->product_id)->update(['stock' => 10]);
        $product->syncStockFromVariants();

        return $product;
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
            ->assertSee('value="150.00"', false)
            // Save sits up by the title, outside the form, so it only works
            // while these two agree. Detached, it would silently do nothing.
            ->assertSee('id="pricingForm"', false)
            ->assertSee('form="pricingForm"', false);
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

    public function test_sizes_cost_nothing_until_an_admin_prices_them(): void
    {
        $this->assertSame(0.0, CustomizationRate::amountFor('size_small'));
        $this->assertSame(0.0, CustomizationRate::amountFor('size_large'));

        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode(['size' => 'large', 'elements' => []]),
        ])->assertOk();

        $this->assertSame(1000.0, (float) \App\Models\CartItem::first()->price);
    }

    public function test_a_priced_size_is_charged_and_itemised(): void
    {
        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'size_large' => 120,
            'size_small' => 25,
        ]))->assertRedirect();

        CustomizationRate::flushCache();

        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $response = $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode(['size' => 'large', 'elements' => []]),
        ])->assertOk();

        $this->assertSame(1120.0, (float) \App\Models\CartItem::first()->price);

        $design = CustomDesign::findOrFail($response->json('design_id'));
        $this->assertSame(
            [['label' => 'Size: Large', 'amount' => 120.0]],
            $design->price_breakdown,
            'Only the ordered size is charged, never the others.'
        );
    }

    public function test_every_size_up_to_5xl_can_be_ordered_and_priced(): void
    {
        // The client's brief: sizes run "hanggang 5XL" — up to 5XL.
        $this->assertSame(
            ['small', 'medium', 'large', 'xl', '2xl', '3xl', '4xl', '5xl'],
            array_keys(CustomizationRate::sizes()),
            'The size list must run Small to 5XL, smallest first.'
        );

        // A recipe stores the lowercase code; the lookup is forgiving of case.
        $this->assertSame('size_5xl', CustomizationRate::keyForSize('5XL'));
        $this->assertSame('size_2xl', CustomizationRate::keyForSize(' 2xl '));
        $this->assertNull(CustomizationRate::keyForSize('6xl'), 'Nothing above 5XL is sold.');

        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'size_xl' => 40,
            'size_5xl' => 200,
        ]))->assertRedirect();

        CustomizationRate::flushCache();

        $this->assertSame(40.0, CustomizationRate::amountFor('size_xl'));
        $this->assertSame(200.0, CustomizationRate::amountFor('size_5xl'));

        Sanctum::actingAs($this->customer());
        $product = $this->product(1000);

        $response = $this->postJson(route('customer.cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
            'custom_recipe' => json_encode(['size' => '5xl', 'elements' => []]),
        ])->assertOk();

        $this->assertSame(1200.0, (float) \App\Models\CartItem::first()->price);

        $design = CustomDesign::findOrFail($response->json('design_id'));
        $this->assertSame(
            [['label' => 'Size: 5X-Large', 'amount' => 200.0]],
            $design->price_breakdown
        );
    }

    public function test_the_studio_offers_every_size_up_to_5xl(): void
    {
        $product = $this->product(1000);
        Sanctum::actingAs($this->customer());

        $response = $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            // The rates payload the live quote reads carries every size too.
            ->assertSee('"size_5xl":0', false);

        foreach (['small', 'medium', 'large', 'xl', '2xl', '3xl', '4xl', '5xl'] as $size) {
            $response->assertSee('data-size="' . $size . '"', false);
        }

        // The button shows the garment code, not just the spelled-out name.
        $response->assertSee('5XL');
    }

    public function test_the_admin_and_staff_price_lists_run_to_5xl(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.customization-pricing.index'))
            ->assertOk()
            ->assertSee('name="rates[size_5xl]"', false)
            ->assertSee('5X-Large')
            ->assertSee('5XL');

        $staff = User::create([
            'fullname' => 'Staff', 'email' => 'staff@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);
        $this->actingAs($staff);
        $this->get(route('staff.customization-pricing.index'))
            ->assertOk()
            ->assertSee('5X-Large')
            ->assertSee('5XL');

        $sizes = CustomizationRate::forDisplay()['sizes'];
        $this->assertSame(
            ['size_small', 'size_medium', 'size_large', 'size_xl', 'size_2xl', 'size_3xl', 'size_4xl', 'size_5xl'],
            array_keys($sizes),
            'The pricing screens list the sizes smallest first, ending at 5XL.'
        );
    }

    public function test_an_unknown_size_is_charged_nothing_rather_than_guessed_at(): void
    {
        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), $this->payload(['size_large' => 120]))->assertRedirect();

        CustomizationRate::flushCache();

        $design = new CustomDesign(['recipe' => ['size' => 'enormous', 'elements' => []]]);
        $this->assertSame([], $design->price_breakdown);
    }

    public function test_the_size_buttons_show_their_surcharge_to_the_customer(): void
    {
        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), $this->payload(['size_large' => 120]))->assertRedirect();

        CustomizationRate::flushCache();

        $product = $this->product(1000);
        Sanctum::actingAs($this->customer());

        $this->get(route('customer.customize.index', ['product_id' => $product->product_id]))
            ->assertOk()
            ->assertSee('data-size="large"', false)
            ->assertSee('+₱120.00', false)
            ->assertSee('"size_large":120', false);
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
