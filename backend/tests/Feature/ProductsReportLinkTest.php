<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Products pages had an Export button with no action, so it did nothing.
 * Exporting lives in Reports › Materials, which builds the PDF and Word files,
 * so the admin's button now opens that report on the products and is named
 * for it. Staff can't open the reports, so their page has no button at all.
 */
class ProductsReportLinkTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => "{$role}@example.test", 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    public function test_the_admin_button_opens_the_materials_report_on_the_products(): void
    {
        $url = route('admin.reports.materials', ['group' => 'products']);

        $html = $this->actingAs($this->user('admin'))
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Materials Report')
            ->getContent();

        $this->assertStringContainsString('<a href="' . e($url) . '"', $html);
        $this->assertStringNotContainsString('>Export<', $html);
    }

    public function test_the_report_it_opens_shows_products_only(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.reports.materials', ['group' => 'products']))
            ->assertOk()
            ->assertSee('<option value="products" selected>', false);
    }

    public function test_the_staff_page_has_no_export_button(): void
    {
        $html = $this->actingAs($this->user('staff'))
            ->get(route('staff.products.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('>Export<', $html);
        $this->assertStringNotContainsString('bi-download', $html);
        $this->assertStringNotContainsString('/admin/reports', $html);
    }
}
