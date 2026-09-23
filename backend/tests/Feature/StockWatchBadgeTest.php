<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use App\Services\StockWatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The Stock Monitoring link in the admin and staff sidebars carries a count
 * of what the page lists: every product, raw material and texture at or below
 * its low-stock line. The bell's poll keeps the count current, so a stock
 * alert toast and the badge move together.
 */
class StockWatchBadgeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Product $healthy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->user('admin');
        $this->staff = $this->user('staff');

        $category = Category::create(['name' => 'Mugs', 'description' => 'x']);
        $product = fn (string $sku, int $stock) => Product::create([
            'name' => "Mug {$sku}", 'sku' => $sku, 'category_id' => $category->category_id,
            'price' => 100, 'stock' => $stock, 'low_stock_threshold' => 5, 'is_customizable' => false,
        ]);

        $product('LOW', 2);                 // on the watchlist
        $this->healthy = $product('OK', 10); // not
        $product('GONE', 0)->delete();      // retired: not

        $supplier = Supplier::create(['name' => 'Supplier', 'email' => 'sup@example.test']);
        RawMaterial::create([
            'name' => 'Fabric', 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => 1, 'low_stock_threshold' => 10, 'unit' => 'm',
        ]);

        // Exactly at the line counts.
        Texture::create([
            'name' => 'Weave', 'cost_per_unit' => 5, 'stock_quantity' => 5,
            'low_stock_threshold' => 5, 'unit' => 'pcs', 'price_modifier' => 0,
        ]);
    }

    public function test_the_badge_counts_what_stock_monitoring_lists(): void
    {
        $this->assertSame(3, app(StockWatch::class)->count());

        Sanctum::actingAs($this->admin);

        $this->assertSame(3, $this->get(route('admin.inventory.index'))->viewData('allLowStockItems')->count());
        $this->assertSame(3, $this->get(route('admin.dashboard'))->viewData('lowStockCount'));
    }

    public static function teamPages(): array
    {
        return [
            'admin' => ['admin', 'admin.dashboard'],
            'staff' => ['staff', 'staff.dashboard'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('teamPages')]
    public function test_the_sidebar_shows_the_count(string $role, string $page): void
    {
        Sanctum::actingAs($this->{$role});

        $html = $this->get(route($page))->assertOk()->getContent();

        // Both copies of the sidebar: the desktop rail and the phone drawer.
        $this->assertSame(2, preg_match_all('/data-stock-badge\s*>\s*<span data-stock-count>3<\/span>/', $html));
    }

    public function test_the_badge_hides_when_nothing_is_short(): void
    {
        Product::query()->update(['stock' => 50]);
        RawMaterial::query()->update(['stock_quantity' => 50]);
        Texture::query()->update(['stock_quantity' => 50]);

        Sanctum::actingAs($this->admin);

        $html = $this->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertSame(2, preg_match_all('/data-stock-badge\s+hidden\s*>/', $html));
    }

    public function test_the_poll_keeps_the_badge_current(): void
    {
        Sanctum::actingAs($this->staff);
        $this->getJson(route('notifications.poll'))->assertJsonPath('stock_watch', 3);

        // Crossing the line raises the stock alert and moves the count with it.
        $this->healthy->update(['stock' => 4]);

        $this->getJson(route('notifications.poll'))
            ->assertJsonPath('stock_watch', 4)
            ->assertJsonPath('items.0.tone', 'warning');
    }

    public function test_a_customer_poll_carries_no_stock_count(): void
    {
        Sanctum::actingAs($this->user('customer'));

        $this->getJson(route('notifications.poll'))->assertOk()->assertJsonPath('stock_watch', null);
    }

    private function user(string $role): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => "{$role}@example.test", 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
            'status' => 'active', 'notifications_enabled' => true,
        ]);
    }
}
