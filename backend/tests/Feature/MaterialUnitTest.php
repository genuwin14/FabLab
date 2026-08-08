<?php

namespace Tests\Feature;

use App\Enums\MaterialUnit;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A raw material's unit was free text, so the same measure got typed several
 * ways — "m", "meter", "metre" — and a bill of materials asking for 0.9 of one
 * had no way to know which was meant. It is now a fixed list, enforced on the
 * server as well as in the dropdown.
 */
class MaterialUnitTest extends TestCase
{
    use RefreshDatabase;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::create(['name' => 'Supplier', 'email' => 'sup@example.test']);

        Sanctum::actingAs(User::create([
            'fullname' => 'Admin', 'email' => 'a@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sublimation Ink (Cyan)',
            'supplier_id' => $this->supplier->supplier_id,
            'cost_per_unit' => 1200,
            'stock_quantity' => 500,
            'low_stock_threshold' => 100,
            'unit' => 'gram',
        ], $overrides);
    }

    private function material(string $unit): RawMaterial
    {
        return RawMaterial::create([
            'name' => 'Legacy Material', 'supplier_id' => $this->supplier->supplier_id,
            'cost_per_unit' => 10, 'stock_quantity' => 40,
            'low_stock_threshold' => 5, 'unit' => $unit,
        ]);
    }

    public function test_ink_can_be_added_in_grams(): void
    {
        $this->post('/admin/raw-materials', $this->payload())->assertRedirect();

        $this->assertSame('gram', RawMaterial::where('name', 'Sublimation Ink (Cyan)')->firstOrFail()->unit);
    }

    public function test_a_unit_outside_the_list_is_rejected(): void
    {
        $this->post('/admin/raw-materials', $this->payload(['unit' => 'grammes']))
            ->assertSessionHasErrors('unit');

        $this->assertSame(0, RawMaterial::where('name', 'Sublimation Ink (Cyan)')->count());
    }

    public function test_every_offered_unit_is_actually_accepted(): void
    {
        // The dropdown and the validator read the same list; this fails loudly
        // if one is ever narrowed without the other.
        foreach (MaterialUnit::values() as $i => $unit) {
            $this->post('/admin/raw-materials', $this->payload([
                'name' => "Material {$i}",
                'unit' => $unit,
            ]))->assertSessionHasNoErrors();
        }

        $this->assertSame(count(MaterialUnit::values()), RawMaterial::count());
    }

    public function test_a_legacy_unit_survives_an_unrelated_edit(): void
    {
        $material = $this->material('m');

        // Changing the cost shouldn't force a unit nobody asked to change.
        $this->put("/admin/raw-materials/{$material->raw_material_id}", [
            'name' => 'Legacy Material', 'supplier_id' => $this->supplier->supplier_id,
            'cost_per_unit' => 99, 'low_stock_threshold' => 5, 'unit' => 'm',
        ])->assertSessionHasNoErrors();

        $material->refresh();
        $this->assertSame('m', $material->unit);
        $this->assertEquals(99, $material->cost_per_unit);
    }

    public function test_a_legacy_unit_can_be_corrected_to_a_listed_one(): void
    {
        $material = $this->material('m');

        $this->put("/admin/raw-materials/{$material->raw_material_id}", [
            'name' => 'Legacy Material', 'supplier_id' => $this->supplier->supplier_id,
            'cost_per_unit' => 10, 'low_stock_threshold' => 5, 'unit' => 'meter',
        ])->assertSessionHasNoErrors();

        $this->assertSame('meter', $material->refresh()->unit);
    }

    public function test_one_legacy_unit_does_not_whitelist_another(): void
    {
        $material = $this->material('m');

        $this->put("/admin/raw-materials/{$material->raw_material_id}", [
            'name' => 'Legacy Material', 'supplier_id' => $this->supplier->supplier_id,
            'cost_per_unit' => 10, 'low_stock_threshold' => 5, 'unit' => 'furlong',
        ])->assertSessionHasErrors('unit');

        $this->assertSame('m', $material->refresh()->unit);
    }

    // ------------------------------------------- textures and products share it

    public function test_a_texture_rejects_a_unit_outside_the_list(): void
    {
        $this->post('/admin/textures', [
            'name' => 'Weave', 'cost_per_unit' => 5, 'stock_quantity' => 10,
            'low_stock_threshold' => 2, 'unit' => 'metres', 'price_modifier' => 0,
        ])->assertSessionHasErrors('unit');

        $this->assertSame(0, \App\Models\Texture::count());
    }

    public function test_an_empty_texture_unit_falls_back_to_the_column_default(): void
    {
        // textures.unit is NOT NULL DEFAULT 'pcs', and Laravel turns an empty
        // field into null — which used to fail the insert outright.
        $this->post('/admin/textures', [
            'name' => 'Weave', 'cost_per_unit' => 5, 'stock_quantity' => 10,
            'low_stock_threshold' => 2, 'unit' => '', 'price_modifier' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertSame('pcs', \App\Models\Texture::firstOrFail()->unit);
    }

    public function test_a_texture_keeps_a_listed_unit(): void
    {
        $this->post('/admin/textures', [
            'name' => 'Weave', 'cost_per_unit' => 5, 'stock_quantity' => 10,
            'low_stock_threshold' => 2, 'unit' => 'meter', 'price_modifier' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertSame('meter', \App\Models\Texture::firstOrFail()->unit);
    }

    public function test_a_product_rejects_a_unit_outside_the_list(): void
    {
        $category = \App\Models\Category::create(['name' => 'Cat', 'description' => 'x']);

        $this->post('/admin/products', [
            'name' => 'Tarpaulin', 'sku' => 'TRP-1', 'category_id' => $category->category_id,
            'price' => 500, 'stock' => 4, 'unit' => 'sqm',
        ])->assertSessionHasErrors('unit');

        $this->assertSame(0, \App\Models\Product::count());
    }

    public function test_a_product_accepts_a_listed_unit(): void
    {
        $category = \App\Models\Category::create(['name' => 'Cat', 'description' => 'x']);

        $this->post('/admin/products', [
            'name' => 'Tarpaulin', 'sku' => 'TRP-1', 'category_id' => $category->category_id,
            'price' => 500, 'stock' => 4, 'unit' => 'sq m',
        ])->assertSessionHasNoErrors();

        $this->assertSame('sq m', \App\Models\Product::firstOrFail()->unit);
    }

    public function test_a_products_legacy_unit_survives_an_unrelated_edit(): void
    {
        $category = \App\Models\Category::create(['name' => 'Cat', 'description' => 'x']);

        // "kg" was offered by the old hardcoded product dropdown, which the
        // shared list replaced with "kilogram".
        $product = \App\Models\Product::create([
            'sku' => 'P-1', 'name' => 'Resin Batch', 'price' => 100, 'stock' => 5,
            'unit' => 'kg', 'category_id' => $category->category_id, 'status' => 'active',
            'low_stock_threshold' => 1,
        ]);

        $this->put("/admin/products/{$product->product_id}", [
            'name' => 'Resin Batch', 'sku' => 'P-1', 'category_id' => $category->category_id,
            'price' => 250, 'stock' => 5, 'unit' => 'kg',
        ])->assertSessionHasNoErrors();

        $this->assertSame('kg', $product->refresh()->unit);
        $this->assertEquals(250, $product->price);
    }

    public function test_the_add_form_offers_grouped_units_with_examples(): void
    {
        $this->get('/admin/raw-materials')
            ->assertOk()
            ->assertSee('gram — ink, resin powder, pigment')
            ->assertSee('meter — lanyard strap, fabric, cabling')
            ->assertSee('<optgroup label="Weight">', false)
            ->assertSee('<optgroup label="Volume">', false);
    }

    public function test_the_texture_and_product_forms_offer_the_same_list(): void
    {
        foreach (['/admin/textures', '/admin/products'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('gram — ink, resin powder, pigment')
                ->assertSee('<optgroup label="Weight">', false);
        }
    }

    public function test_staff_get_the_same_list(): void
    {
        $material = $this->material('pcs');

        Sanctum::actingAs(User::create([
            'fullname' => 'Staff', 'email' => 'st@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]));

        $this->put("/staff/raw-materials/{$material->raw_material_id}", [
            'name' => 'Legacy Material', 'supplier_id' => $this->supplier->supplier_id,
            'cost_per_unit' => 10, 'low_stock_threshold' => 5, 'unit' => 'grammes',
        ])->assertSessionHasErrors('unit');

        $this->assertSame('pcs', $material->refresh()->unit);
    }
}
