<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomizationRate;
use App\Models\InkChannel;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\TransferSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The settings that turn a measured print into millilitres.
 *
 * Coverage off the artwork is a fraction. Three admin-set figures make it a
 * quantity: the product's printable area at Medium, how much bigger each
 * garment size is than that, and what a square centimetre of solid colour
 * costs in each of the printer's four inks. None of them is typed per order.
 */
class InkSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private RawMaterial $cyan;
    private Product $shirt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'fullname' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);

        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 's@example.test']);
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        $this->cyan = RawMaterial::create([
            'name' => 'Sublimation Ink (Cyan)', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 4.5,
            'stock_quantity' => 800, 'low_stock_threshold' => 200, 'unit' => 'ml',
        ]);

        $this->shirt = Product::create([
            'sku' => 'P-1', 'name' => 'Shirt', 'price' => 100, 'stock' => 20, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 2,
        ]);

        CustomizationRate::flushCache();
        InkChannel::flushCache();
        TransferSheet::flushCache();
    }

    /** @return array<string, mixed> */
    private function payload(array $extra = []): array
    {
        $rates = collect(CustomizationRate::DEFINITIONS)->map(fn ($d) => $d['default'])->all();

        return ['rates' => $rates] + $extra;
    }

    public function test_the_migration_creates_all_four_channels_at_the_shipped_rate(): void
    {
        $rates = InkChannel::rates();

        $this->assertSame(['cyan', 'magenta', 'yellow', 'black'], array_keys($rates));
        foreach ($rates as $rate) {
            $this->assertSame(InkChannel::DEFAULT_ML_PER_CM2, $rate['ml_per_cm2']);
        }

        // The migration ran on an empty raw_materials table, so nothing is
        // linked yet and measured ink cannot be drawn.
        $this->assertFalse(InkChannel::configured());
    }

    public function test_an_admin_can_point_a_channel_at_a_bottle_and_set_its_rate(): void
    {
        $this->actingAs($this->admin);

        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'ink' => ['cyan' => ['raw_material_id' => $this->cyan->raw_material_id, 'ml_per_cm2' => '0.004']],
        ]))->assertRedirect(route('admin.customization-pricing.index'));

        InkChannel::flushCache();

        $this->assertSame($this->cyan->raw_material_id, InkChannel::rates()['cyan']['raw_material_id']);
        $this->assertSame(0.004, InkChannel::rates()['cyan']['ml_per_cm2']);
        $this->assertSame(['cyan' => $this->cyan->raw_material_id], InkChannel::linkedMaterials());
        $this->assertTrue(InkChannel::configured());

        // The other three were not posted and keep their defaults.
        $this->assertNull(InkChannel::rates()['magenta']['raw_material_id']);
        $this->assertSame(InkChannel::DEFAULT_ML_PER_CM2, InkChannel::rates()['black']['ml_per_cm2']);
    }

    public function test_emptying_the_bottle_unlinks_the_channel(): void
    {
        InkChannel::where('channel', 'cyan')->update(['raw_material_id' => $this->cyan->raw_material_id]);
        InkChannel::flushCache();
        $this->assertTrue(InkChannel::configured());

        $this->actingAs($this->admin);
        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'ink' => ['cyan' => ['raw_material_id' => '', 'ml_per_cm2' => '0.0025']],
        ]))->assertRedirect();

        InkChannel::flushCache();
        $this->assertNull(InkChannel::rates()['cyan']['raw_material_id']);
        $this->assertFalse(InkChannel::configured());
    }

    public function test_a_negative_ink_rate_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'ink' => ['cyan' => ['raw_material_id' => '', 'ml_per_cm2' => '-1']],
        ]))->assertSessionHasErrors('ink.cyan.ml_per_cm2');
    }

    public function test_repricing_alone_leaves_the_ink_settings_untouched(): void
    {
        InkChannel::where('channel', 'cyan')->update(['raw_material_id' => $this->cyan->raw_material_id, 'ml_per_cm2' => 0.009]);
        InkChannel::flushCache();

        $this->actingAs($this->admin);
        $this->put(route('admin.customization-pricing.update'), $this->payload())->assertRedirect();

        InkChannel::flushCache();
        $this->assertSame($this->cyan->raw_material_id, InkChannel::rates()['cyan']['raw_material_id']);
        $this->assertSame(0.009, InkChannel::rates()['cyan']['ml_per_cm2']);
    }

    public function test_every_size_prints_at_the_products_own_area_out_of_the_box(): void
    {
        // The shop presses a cut transfer, and a 4×2 sticker is 4×2 on a
        // Small and on a 5XL. A shop that scales the print with the garment
        // raises these on the pricing screen.
        $factors = CustomizationRate::areaFactors();

        $this->assertSame(
            ['size_small', 'size_medium', 'size_large', 'size_xl', 'size_2xl', 'size_3xl', 'size_4xl', 'size_5xl'],
            array_keys($factors)
        );
        foreach ($factors as $key => $factor) {
            $this->assertSame(1.0, $factor, "{$key} should print at the product's own area until an admin says otherwise.");
        }

        // Elements have no factor at all — nothing to scale.
        $this->assertArrayNotHasKey('logo', $factors);
    }

    public function test_the_migration_creates_an_a4_transfer_sheet_with_nothing_linked(): void
    {
        $sheet = TransferSheet::current();

        $this->assertSame('A4', $sheet['name']);
        $this->assertSame(21.0, $sheet['width_cm']);
        $this->assertSame(29.7, $sheet['height_cm']);
        $this->assertSame(0.5, $sheet['margin_cm']);
        // Migrated on an empty raw_materials table, so measured paper cannot be drawn yet.
        $this->assertNull($sheet['raw_material_id']);
        $this->assertFalse(TransferSheet::configured());
    }

    public function test_an_admin_can_link_the_transfer_paper_and_set_the_sheet(): void
    {
        $paper = RawMaterial::create([
            'name' => 'Sublimation Transfer Paper (A4)', 'supplier_id' => $this->cyan->supplier_id, 'cost_per_unit' => 3,
            'stock_quantity' => 500, 'low_stock_threshold' => 50, 'unit' => 'pcs',
        ]);

        $this->actingAs($this->admin);
        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'paper' => ['raw_material_id' => $paper->raw_material_id, 'width_cm' => '21', 'height_cm' => '29.7', 'margin_cm' => '1'],
        ]))->assertRedirect(route('admin.customization-pricing.index'));

        TransferSheet::flushCache();
        $sheet = TransferSheet::current();
        $this->assertSame($paper->raw_material_id, $sheet['raw_material_id']);
        $this->assertSame(1.0, $sheet['margin_cm']);
        $this->assertTrue(TransferSheet::configured());

        // Repricing alone leaves it be; emptying the select unlinks it.
        $this->put(route('admin.customization-pricing.update'), $this->payload())->assertRedirect();
        TransferSheet::flushCache();
        $this->assertSame($paper->raw_material_id, TransferSheet::materialId());

        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'paper' => ['raw_material_id' => '', 'width_cm' => '21', 'height_cm' => '29.7', 'margin_cm' => '1'],
        ]))->assertRedirect();
        TransferSheet::flushCache();
        $this->assertNull(TransferSheet::materialId());
        $this->assertFalse(TransferSheet::configured());
    }

    public function test_a_sheet_with_no_width_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'paper' => ['raw_material_id' => '', 'width_cm' => '0', 'height_cm' => '29.7', 'margin_cm' => '0.5'],
        ]))->assertSessionHasErrors('paper.width_cm');
    }

    public function test_an_admin_can_change_how_much_bigger_a_size_prints(): void
    {
        $this->actingAs($this->admin);

        $this->put(route('admin.customization-pricing.update'), $this->payload([
            'area_factors' => ['size_5xl' => '2.25', 'logo' => '7'],
        ]))->assertRedirect();

        CustomizationRate::flushCache();

        $this->assertSame(2.25, CustomizationRate::areaFactorForSize('5xl'));
        // An element is not a size; a factor posted against one is ignored.
        $this->assertSame(1.0, (float) CustomizationRate::where('key', 'logo')->value('print_area_factor'));
    }

    public function test_an_unknown_size_prints_at_the_products_own_area(): void
    {
        $this->assertSame(1.0, CustomizationRate::areaFactorForSize('enormous'));
        $this->assertSame(1.0, CustomizationRate::areaFactorForSize(null));
    }

    public function test_a_products_print_area_scales_with_the_ordered_size(): void
    {
        $this->assertNull($this->shirt->printAreaFor('large'), 'Unmeasured until an admin fills it in.');

        $this->shirt->update(['print_area_cm2' => 2400]);

        $this->assertSame(2400.0, $this->shirt->printAreaFor('medium'));
        $this->assertSame(round(2400.0 * CustomizationRate::areaFactorForSize('5xl'), 2), $this->shirt->printAreaFor('5xl'));
    }

    public function test_an_admin_can_record_a_products_print_area(): void
    {
        $this->actingAs($this->admin);

        $this->put(route('admin.products.update', $this->shirt->product_id), [
            'name' => 'Shirt', 'sku' => 'P-1', 'category_id' => $this->shirt->category_id,
            'price' => 100, 'stock' => 20, 'unit' => 'pcs', 'print_area_cm2' => '2400.5',
        ])->assertRedirect();

        $this->assertSame(2400.5, $this->shirt->refresh()->print_area_cm2);

        // Clearing the field clears the figure, rather than failing on ''.
        $this->put(route('admin.products.update', $this->shirt->product_id), [
            'name' => 'Shirt', 'sku' => 'P-1', 'category_id' => $this->shirt->category_id,
            'price' => 100, 'stock' => 20, 'unit' => 'pcs', 'print_area_cm2' => '',
        ])->assertRedirect();

        $this->assertNull($this->shirt->refresh()->print_area_cm2);
    }

    public function test_both_pricing_screens_show_the_ink_card(): void
    {
        InkChannel::where('channel', 'cyan')->update(['raw_material_id' => $this->cyan->raw_material_id]);

        $this->actingAs($this->admin);
        $this->get(route('admin.customization-pricing.index'))
            ->assertOk()
            ->assertSee('Sublimation ink')
            ->assertSee('name="ink[cyan][ml_per_cm2]"', false)
            ->assertSee('name="area_factors[size_5xl]"', false);

        $staff = User::create([
            'fullname' => 'Staff', 'email' => 'staff@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);
        $this->actingAs($staff);
        $this->get(route('staff.customization-pricing.index'))
            ->assertOk()
            ->assertSee('Sublimation ink')
            ->assertSee('Draws from Sublimation Ink (Cyan)')
            ->assertSee('Transfer paper')
            ->assertSee('21 × 29.7 cm')
            ->assertDontSee('name="ink[cyan][ml_per_cm2]"', false)
            ->assertDontSee('name="paper[width_cm]"', false);

        $this->actingAs($this->admin);
        $this->get(route('admin.customization-pricing.index'))
            ->assertSee('Transfer paper')
            ->assertSee('name="paper[width_cm]"', false);
    }
}
