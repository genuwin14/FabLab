<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Products page's Export button was a <button> with no action, so it did
 * nothing. Exporting lives in Reports › Materials, which builds the PDF and
 * Word files, so the button now opens that report on the products.
 */
class ProductsExportLinkTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'fullname' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    public function test_export_opens_the_materials_report_on_the_products(): void
    {
        $url = route('admin.reports.materials', ['group' => 'products']);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<a href="' . e($url) . '"', $html);
    }

    public function test_the_report_it_opens_shows_products_only(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.materials', ['group' => 'products']))
            ->assertOk()
            ->assertSee('<option value="products" selected>', false);
    }
}
