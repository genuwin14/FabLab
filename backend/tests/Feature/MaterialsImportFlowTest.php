<?php

namespace Tests\Feature;

use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Reports\MaterialsDocxGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * The three steps an admin actually walks: upload, review, confirm.
 *
 * The point of the middle step is that the first one writes nothing, so that is
 * asserted rather than assumed.
 */
class MaterialsImportFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'fullname' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function material(): RawMaterial
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier', 'contact_person' => 'Someone',
            'email' => 'supplier@example.test', 'phone' => '09000000000', 'address' => 'Nabua',
        ]);

        return RawMaterial::create([
            'name' => 'Lanyard Metal Clip', 'supplier_id' => $supplier->supplier_id,
            'cost_per_unit' => 6.50, 'stock_quantity' => 1000, 'low_stock_threshold' => 200,
            'unit' => 'pcs', 'department' => 'Digital Customization Center',
        ]);
    }

    private function report(): UploadedFile
    {
        $path = (new MaterialsDocxGenerator([
            'Digital Customization Center' => [
                ['name' => 'Lanyard Metal Clip', 'unit' => 'pcs', 'on_display' => 0.0, 'sponsored' => 0.0,
                 'damaged' => 12.0, 'consumed' => 1180.0, 'available' => 1008.0],
            ],
        ], 'all', now()))->save();

        return new UploadedFile($path, 'inventory-2024.docx', null, null, true);
    }

    public function test_upload_shows_a_preview_and_writes_nothing(): void
    {
        $material = $this->material();

        $this->actingAs($this->admin)
            ->post(route('admin.reports.materials.import'), ['report' => $this->report()])
            ->assertRedirect(route('admin.reports.materials.import.preview'));

        $this->actingAs($this->admin)
            ->get(route('admin.reports.materials.import.preview'))
            ->assertOk()
            ->assertSee('Lanyard Metal Clip')
            ->assertSee('inventory-2024.docx')
            ->assertSee('Will update');

        $this->assertSame(1000.0, (float) $material->refresh()->stock_quantity);
    }

    public function test_confirming_applies_the_report(): void
    {
        $material = $this->material();

        $this->actingAs($this->admin)
            ->post(route('admin.reports.materials.import'), ['report' => $this->report()]);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.materials.import.confirm'))
            ->assertRedirect(route('admin.reports.materials'))
            ->assertSessionHas('success');

        $material->refresh();
        $this->assertSame(1008.0, (float) $material->stock_quantity);
        $this->assertSame(1180.0, (float) $material->units_consumed);
    }

    public function test_discarding_leaves_the_inventory_alone(): void
    {
        $material = $this->material();

        $this->actingAs($this->admin)
            ->post(route('admin.reports.materials.import'), ['report' => $this->report()]);

        $this->actingAs($this->admin)
            ->post(route('admin.reports.materials.import.discard'))
            ->assertRedirect(route('admin.reports.materials'));

        $this->assertSame(1000.0, (float) $material->refresh()->stock_quantity);

        // And the held upload is gone, so confirm has nothing left to apply.
        $this->actingAs($this->admin)
            ->post(route('admin.reports.materials.import.confirm'))
            ->assertRedirect(route('admin.reports.materials'))
            ->assertSessionHas('error');
    }

    public function test_a_legacy_doc_is_turned_away_with_a_reason(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'legacy') . '.doc';
        file_put_contents($path, 'old binary word content');

        $this->actingAs($this->admin)
            ->post(route('admin.reports.materials.import'), [
                'report' => new UploadedFile($path, 'inventory-2019.doc', null, null, true),
            ])
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'saved as .docx'));

        unlink($path);
    }

    /**
     * The first real import matched nothing at all, and the screen reported
     * every row as "No change" — hiding the only information it had.
     */
    public function test_an_unmatched_row_shows_the_figures_the_report_carried(): void
    {
        $path = (new MaterialsDocxGenerator([
            'Woodworks' => [
                ['name' => '3/4 MARINE PLYWOOD', 'unit' => 'sheet', 'on_display' => 0.0, 'sponsored' => 0.0,
                 'damaged' => 0.0, 'consumed' => 7.0, 'available' => 23.0],
            ],
        ], 'all', now()))->save();

        $this->actingAs($this->admin)->post(route('admin.reports.materials.import'), [
            'report' => new UploadedFile($path, 'inventory-2019.docx', null, null, true),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.materials.import.preview'));

        $response->assertOk()
            ->assertSee('3/4 MARINE PLYWOOD')
            ->assertSee('Not found')
            // The report said 23 available and 7 consumed; both must be legible.
            ->assertSee('import-change-reported-value')
            ->assertSeeInOrder(['Consumed', '7', 'Available', '23'], false)
            ->assertDontSee('No change')
            // And the footer must not call an import that matched nothing clean.
            ->assertDontSee('Every item in this report already matches')
            ->assertSee('None of these 1 rows matched an existing item');
    }

    public function test_the_preview_is_not_reachable_without_an_upload(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.materials.import.preview'))
            ->assertRedirect(route('admin.reports.materials'))
            ->assertSessionHas('error');
    }
}
