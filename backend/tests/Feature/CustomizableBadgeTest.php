<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The product tables say on the row whether a product opens in the 3D studio.
 * Nothing else on the page carried that flag, so the only way to tell was to
 * open the edit modal — and the customizer being unreachable looks exactly like
 * a broken link from the outside.
 */
class CustomizableBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $sku, string $name, bool $customizable): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Apparel'],
            ['description' => 'Test category']
        );

        return Product::create([
            'sku' => $sku, 'name' => $name, 'price' => 1000, 'stock' => 10, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active',
            'is_customizable' => $customizable, 'low_stock_threshold' => 2,
        ]);
    }

    private function user(string $role): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => "{$role}@example.test", 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    public static function productTables(): array
    {
        return [
            'admin' => ['admin', 'admin.products.index'],
            'staff' => ['staff', 'staff.products.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('productTables')]
    public function test_the_table_badges_each_product_with_its_customizable_flag(string $role, string $route): void
    {
        $this->product('CUS-001', 'Custom Shirt', true);
        $this->product('STD-001', 'Plain Shirt', false);

        $html = $this->actingAs($this->user($role))
            ->get(route($route))
            ->assertOk()
            ->getContent();

        // Counted, and matched on the badge's own markup: the word alone also
        // appears in a stylesheet comment and in the edit modal's toggle label.
        $this->assertSame(1, substr_count($html, '</i> Customizable'));
        $this->assertSame(1, substr_count($html, '</i> Not customizable'));
    }
}
