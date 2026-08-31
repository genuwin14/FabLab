<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\CustomizationRate;
use App\Models\CustomizationRateMaterial;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The screens that decide what a customization costs the shop.
 *
 * Mostly a guard on the wiring: each of these pages grew a material picker
 * that needs a variable the controller has to pass, and a Blade that renders
 * fine with rows can still fatal on an empty catalogue. Both are cheap to get
 * wrong and invisible until someone opens the page.
 */
class FinishMaterialScreenTest extends TestCase
{
    use RefreshDatabase;

    private RawMaterial $material;

    protected function setUp(): void
    {
        parent::setUp();

        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 's@example.test']);

        $this->material = RawMaterial::create([
            'name' => 'Ink (Red)', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 100, 'low_stock_threshold' => 5, 'unit' => 'ml',
        ]);

        CustomizationRate::flushCache();
    }

    private function user(string $role): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => "{$role}@example.test", 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    /** @return array<int, string> */
    private function pagesFor(string $role): array
    {
        return [
            "/{$role}/customization-pricing",
            "/{$role}/colors",
            "/{$role}/textures",
        ];
    }

    public function test_every_screen_renders_for_admin_and_staff(): void
    {
        Color::create([
            'name' => 'Red', 'hex_code' => '#ff0000',
            'raw_material_id' => $this->material->raw_material_id, 'material_quantity' => 5,
        ]);
        Texture::create(['name' => 'Camo', 'stock_quantity' => 20, 'low_stock_threshold' => 2]);
        CustomizationRateMaterial::create([
            'rate_key' => 'text',
            'raw_material_id' => $this->material->raw_material_id,
            'quantity_required' => 2,
        ]);
        CustomizationRate::flushCache();

        foreach (['admin', 'staff'] as $role) {
            Sanctum::actingAs($this->user($role));

            foreach ($this->pagesFor($role) as $page) {
                $this->get($page)->assertOk()->assertSee('Ink (Red)');
            }
        }
    }

    public function test_the_screens_render_with_no_raw_materials_on_file(): void
    {
        // A fresh install has an empty catalogue, and the pricing screen's
        // "Add material" button has no template row to clone from. It must
        // still open rather than fatal.
        $this->material->forceDelete();

        Color::create(['name' => 'Plain', 'hex_code' => '#ffffff']);
        Texture::create(['name' => 'Camo', 'stock_quantity' => 20, 'low_stock_threshold' => 2]);

        foreach (['admin', 'staff'] as $role) {
            Sanctum::actingAs($this->user($role));

            foreach ($this->pagesFor($role) as $page) {
                $this->get($page)->assertOk();
            }
        }
    }

    public function test_an_admin_maps_a_material_to_an_option(): void
    {
        Sanctum::actingAs($this->user('admin'));

        $this->put('/admin/customization-pricing', [
            'rates' => array_map(fn ($d) => $d['default'], CustomizationRate::DEFINITIONS),
            'materials' => [
                'text' => [['raw_material_id' => $this->material->raw_material_id, 'quantity' => 2.5]],
                // A row the admin added but never filled in must not fail the
                // save — the form offers one and leaving it alone is normal.
                'logo' => [['raw_material_id' => '', 'quantity' => '']],
            ],
        ])->assertRedirect()->assertSessionHas('success');

        CustomizationRate::flushCache();

        $this->assertSame([$this->material->raw_material_id => 2.5], CustomizationRate::materialsFor('text'));
        $this->assertSame([], CustomizationRate::materialsFor('logo'));
    }

    public function test_saving_replaces_the_previous_mapping_rather_than_adding_to_it(): void
    {
        CustomizationRateMaterial::create([
            'rate_key' => 'text',
            'raw_material_id' => $this->material->raw_material_id,
            'quantity_required' => 9,
        ]);

        Sanctum::actingAs($this->user('admin'));

        // The option is posted with no material rows at all — the admin removed
        // the only one. It has to disappear, not linger at 9.
        $this->put('/admin/customization-pricing', [
            'rates' => array_map(fn ($d) => $d['default'], CustomizationRate::DEFINITIONS),
        ])->assertRedirect();

        CustomizationRate::flushCache();

        $this->assertSame([], CustomizationRate::materialsFor('text'));
        $this->assertSame(0, CustomizationRateMaterial::count());
    }

    public function test_the_same_material_twice_on_one_option_is_refused(): void
    {
        Sanctum::actingAs($this->user('admin'));

        $this->put('/admin/customization-pricing', [
            'rates' => array_map(fn ($d) => $d['default'], CustomizationRate::DEFINITIONS),
            'materials' => [
                'text' => [
                    ['raw_material_id' => $this->material->raw_material_id, 'quantity' => 2],
                    ['raw_material_id' => $this->material->raw_material_id, 'quantity' => 3],
                ],
            ],
        ])->assertSessionHasErrors('materials');

        $this->assertSame(0, CustomizationRateMaterial::count());
    }

    public function test_a_colour_saved_without_a_quantity_keeps_no_material_link(): void
    {
        Sanctum::actingAs($this->user('admin'));

        $this->post('/admin/colors', [
            'name' => 'Red', 'hex_code' => '#FF0000',
            'raw_material_id' => $this->material->raw_material_id,
            // Picked a material but never said how much. Half a pair describes
            // nothing, so the link is dropped rather than saved as a draw of 0.
            'material_quantity' => '',
        ])->assertRedirect();

        $color = Color::where('name', 'Red')->sole();
        $this->assertNull($color->raw_material_id);
        $this->assertEquals(0, $color->material_quantity);
    }

    public function test_clearing_a_colours_material_clears_its_quantity_too(): void
    {
        $color = Color::create([
            'name' => 'Red', 'hex_code' => '#ff0000',
            'raw_material_id' => $this->material->raw_material_id, 'material_quantity' => 5,
        ]);

        Sanctum::actingAs($this->user('admin'));

        $this->put("/admin/colors/{$color->color_id}", [
            'name' => 'Red', 'hex_code' => '#FF0000',
            'raw_material_id' => '', 'material_quantity' => '5',
        ])->assertRedirect();

        $color->refresh();
        $this->assertNull($color->raw_material_id);
        $this->assertEquals(0, $color->material_quantity);
    }
}
