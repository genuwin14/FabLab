<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\CustomizationRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Colors and Customization Pricing now appear in the staff area, but gated:
 * staff may edit an existing colour, and may only read the price list.
 *
 * The gate is the absence of a route, not a hidden button — a page that merely
 * hides its Add and Retire controls still accepts a hand-rolled POST, so these
 * tests check the routes as well as the markup.
 */
class StaffCustomizationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CustomizationRate::flushCache();
    }

    private function staff(): User
    {
        return User::create([
            'fullname' => 'Staff', 'email' => 'staff@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'fullname' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09222222222', 'phone_verified' => true,
        ]);
    }

    /**
     * The full rate payload the admin form posts. array_merge, not `+` — union
     * keeps the left-hand value, so overrides would silently do nothing.
     */
    private function rates(array $overrides = []): array
    {
        $defaults = collect(CustomizationRate::DEFINITIONS)->map(fn($d) => $d['default'])->all();

        return array_merge($defaults, $overrides);
    }

    private function color(array $overrides = []): Color
    {
        return Color::create(array_merge([
            'name' => 'Navy',
            'hex_code' => '#1b2a4a',
            'description' => 'Deep blue',
            'price_modifier' => 20,
        ], $overrides));
    }

    /* ---------------------------------------------------------------- Colors */

    public function test_staff_see_the_palette(): void
    {
        $this->color();
        $this->actingAs($this->staff());

        $this->get(route('staff.colors.index'))
            ->assertOk()
            ->assertSee('Colors')
            ->assertSee('Navy')
            ->assertSee('#1B2A4A');
    }

    public function test_the_staff_palette_offers_no_way_to_add_or_retire(): void
    {
        $this->color();
        $this->actingAs($this->staff());

        $this->get(route('staff.colors.index'))
            ->assertOk()
            ->assertDontSee('addColorModal')
            ->assertDontSee('deleteColorModal')
            ->assertDontSee('Add Color')
            ->assertDontSee('Retire');
    }

    public function test_staff_can_edit_a_colour(): void
    {
        $color = $this->color();
        $this->actingAs($this->staff());

        $this->put(route('staff.colors.update', $color->color_id), [
            'name' => 'Midnight Navy',
            'hex_code' => '#0E2A45',
            'description' => 'Deeper blue',
            'price_modifier' => 35,
        ])->assertRedirect(route('staff.colors.index'));

        $color->refresh();
        $this->assertSame('Midnight Navy', $color->name);
        // Stored lower-case, same as the admin screen does it.
        $this->assertSame('#0e2a45', $color->hex_code);
        $this->assertSame(35.0, $color->price_modifier);
    }

    public function test_a_bad_hex_from_staff_is_rejected(): void
    {
        $color = $this->color();
        $this->actingAs($this->staff());

        $this->put(route('staff.colors.update', $color->color_id), [
            'name' => 'Navy',
            'hex_code' => 'navy blue',
            'price_modifier' => 0,
        ])->assertSessionHasErrors('hex_code');

        $this->assertSame('#1b2a4a', $color->fresh()->hex_code);
    }

    public function test_staff_have_no_route_to_add_or_retire_a_colour(): void
    {
        $color = $this->color();
        $this->actingAs($this->staff());

        // Hiding the buttons isn't the gate — the endpoints don't exist.
        $this->assertFalse(Route::has('staff.colors.store'));
        $this->assertFalse(Route::has('staff.colors.destroy'));

        // Both URIs answer other verbs, so a hand-rolled write is a 405 rather
        // than a 404. Either way nothing reaches a controller.
        $this->post('/staff/colors', ['name' => 'Crimson', 'hex_code' => '#dc3545'])
            ->assertMethodNotAllowed();
        $this->delete('/staff/colors/' . $color->color_id)->assertMethodNotAllowed();

        $this->assertSame(1, Color::count());
        $this->assertNotSoftDeleted($color);
    }

    public function test_staff_are_turned_away_from_the_admin_colour_screens(): void
    {
        $color = $this->color();
        $staff = $this->staff();
        $this->actingAs($staff);

        // EnsureUserHasRole sends a stray page visit home and refuses writes flat.
        $this->get(route('admin.colors.index'))->assertRedirect(route($staff->homeRoute()));
        $this->post(route('admin.colors.store'), ['name' => 'Crimson', 'hex_code' => '#dc3545'])->assertForbidden();
        $this->delete(route('admin.colors.destroy', $color->color_id))->assertForbidden();

        $this->assertSame(1, Color::count());
        $this->assertNotSoftDeleted($color);
    }

    /* --------------------------------------------------- Customization pricing */

    public function test_staff_can_look_up_what_the_customizer_charges(): void
    {
        $this->actingAs($this->admin());
        $this->put(route('admin.customization-pricing.update'), [
            'rates' => $this->rates(['logo' => 250, 'size_large' => 120]),
        ])->assertRedirect();

        CustomizationRate::flushCache();

        $this->actingAs($this->staff());

        $this->get(route('staff.customization-pricing.index'))
            ->assertOk()
            ->assertSee('Customization Pricing')
            ->assertSee('₱250.00')   // the image rate at 1x
            ->assertSee('₱120.00')   // the large-size surcharge
            ->assertSee('View only');
    }

    public function test_the_staff_pricing_page_has_nothing_to_submit(): void
    {
        $this->actingAs($this->staff());

        $page = $this->get(route('staff.customization-pricing.index'))
            ->assertOk()
            ->assertDontSee('name="rates[', false)
            ->assertDontSee('Save Pricing')
            ->assertDontSee('pricingForm');

        // The shared layout ships its own forms (logout, search), so the check
        // that matters is that none of them point at a pricing endpoint.
        $this->assertDoesNotMatchRegularExpression(
            '/action="[^"]*customization-pricing/',
            $page->getContent(),
            'The read-only pricing page must not post anywhere.'
        );
    }

    public function test_staff_cannot_reprice_the_customizer(): void
    {
        $this->actingAs($this->staff());

        $payload = ['rates' => $this->rates(['logo' => 1])];

        // No staff writer exists — the URI reads only, so a PUT is a 405 — and
        // the admin's writer refuses them outright.
        $this->assertFalse(Route::has('staff.customization-pricing.update'));
        $this->put('/staff/customization-pricing', $payload)->assertMethodNotAllowed();
        $this->put(route('admin.customization-pricing.update'), $payload)->assertForbidden();

        CustomizationRate::flushCache();
        $this->assertSame(150.0, CustomizationRate::amountFor('logo'));
    }

    public function test_staff_are_sent_home_from_the_admin_pricing_page(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff);

        $this->get(route('admin.customization-pricing.index'))
            ->assertRedirect(route($staff->homeRoute()));
    }

    /* ------------------------------------------------------------------ Admins */

    public function test_an_admin_can_also_work_the_staff_pages(): void
    {
        // The staff group is role:staff,admin — a shop with no staff account
        // must not lock its admin out of these.
        $color = $this->color();
        $this->actingAs($this->admin());

        $this->get(route('staff.colors.index'))->assertOk();
        $this->get(route('staff.customization-pricing.index'))->assertOk();
        $this->put(route('staff.colors.update', $color->color_id), [
            'name' => 'Navy', 'hex_code' => '#1b2a4a', 'price_modifier' => 0,
        ])->assertRedirect(route('staff.colors.index'));
    }

    public function test_a_customer_reaches_neither_staff_page(): void
    {
        $customer = User::create([
            'fullname' => 'Customer', 'email' => 'customer@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09333333333', 'phone_verified' => true,
        ]);
        $this->actingAs($customer);

        $this->get(route('staff.colors.index'))->assertRedirect(route($customer->homeRoute()));
        $this->get(route('staff.customization-pricing.index'))->assertRedirect(route($customer->homeRoute()));
    }
}
