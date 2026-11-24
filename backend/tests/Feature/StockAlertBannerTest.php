<?php

namespace Tests\Feature;

use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\Texture;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OutOfStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The panel: "The low stock alert must appear at the top of the screen and
 * remain visible until the user dismisses it." A toast closes itself after a
 * few seconds, so the alert is drawn as a banner under the navbar of every
 * admin and staff page, from the reader's unread stock notifications, and only
 * the dismiss button takes it away.
 */
class StockAlertBannerTest extends TestCase
{
    use RefreshDatabase;

    public static function team(): array
    {
        return [
            'admin' => ['admin', 'admin.dashboard'],
            'staff' => ['staff', 'staff.dashboard'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('team')]
    public function test_an_unread_stock_alert_shows_at_the_top_of_the_page(string $role, string $page): void
    {
        $reader = $this->user($role);
        $this->stored($reader, LowStockAlert::class, 'Low stock: Sublimation Paper', 'Only 4 sheets left (threshold 10).', 'Raw Material', 7);

        Sanctum::actingAs($reader);

        $html = $this->get(route($page))->assertOk()->getContent();
        $banner = $this->banner($html);

        $this->assertStringContainsString('Stock alert: 1 item needs restocking', $banner);
        $this->assertStringContainsString('Low stock: Sublimation Paper', $banner);
        $this->assertStringContainsString('Only 4 sheets left (threshold 10).', $banner);
        $this->assertMatchesRegularExpression('/data-stock-alert-banner\s+role="alert"\s*>/', $banner, 'The banner must not be hidden.');

        // Above the page: it sits in the header, straight after the navbar.
        $this->assertLessThan(strpos($html, '<main'), strpos($html, 'data-stock-alert-banner'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('team')]
    public function test_with_nothing_unread_the_banner_stays_hidden(string $role, string $page): void
    {
        $reader = $this->user($role);
        $this->stored($reader, LowStockAlert::class, 'Low stock: Old news', 'x', 'Product', 1, read: true);

        Sanctum::actingAs($reader);

        $banner = $this->banner($this->get(route($page))->assertOk()->getContent());

        $this->assertMatchesRegularExpression('/data-stock-alert-banner\s+role="alert"\s+hidden\s*>/', $banner);
        // The bell still lists it, as read; the banner does not.
        $this->assertStringNotContainsString('Low stock: Old news', $banner);
    }

    public function test_dismissing_marks_only_the_stock_alerts_read(): void
    {
        $admin = $this->user('admin');
        $this->stored($admin, LowStockAlert::class, 'Low stock: A', 'x', 'Product', 1);
        $this->stored($admin, OutOfStockAlert::class, 'Out of stock: B', 'x', 'Texture', 2);
        $this->stored($admin, NewOrderPlaced::class, 'New order', 'x', null, null, category: 'order');

        Sanctum::actingAs($admin);

        $this->postJson(route('notifications.stockAlerts.dismiss'))
            ->assertOk()
            ->assertJson(['unread_count' => 1]);

        $this->assertSame([NewOrderPlaced::class], $admin->unreadNotifications()->pluck('type')->all());

        // Gone on the next page too: dismissal is stored, not remembered by a tab.
        $banner = $this->banner($this->get(route('admin.dashboard'))->getContent());
        $this->assertStringNotContainsString('Low stock: A', $banner);
        $this->assertMatchesRegularExpression('/data-stock-alert-banner\s+role="alert"\s+hidden\s*>/', $banner);
    }

    public function test_a_new_alert_after_dismissing_brings_the_banner_back(): void
    {
        $staff = $this->user('staff');
        $this->stored($staff, LowStockAlert::class, 'Low stock: First', 'x', 'Product', 1);

        Sanctum::actingAs($staff);
        $this->postJson(route('notifications.stockAlerts.dismiss'))->assertOk();

        $this->stored($staff, LowStockAlert::class, 'Low stock: Second', 'x', 'Product', 2);

        $feed = $this->getJson(route('notifications.poll'))->assertOk()->json('stock_alerts');

        $this->assertSame(1, $feed['count']);
        $this->assertSame('Low stock: Second', $feed['items'][0]['title']);
    }

    public function test_an_item_that_ran_low_then_out_is_one_line_saying_out(): void
    {
        $admin = $this->user('admin');
        $this->stored($admin, LowStockAlert::class, 'Low stock: Navy Polo', 'x', 'Product variant', 5, minutesAgo: 10);
        $this->stored($admin, OutOfStockAlert::class, 'Out of stock: Navy Polo', 'x', 'Product variant', 5);

        Sanctum::actingAs($admin);
        $feed = $this->getJson(route('notifications.poll'))->json('stock_alerts');

        $this->assertSame(1, $feed['count']);
        $this->assertTrue($feed['out_of_stock']);
        $this->assertSame(['Out of stock: Navy Polo'], array_column($feed['items'], 'title'));
        $this->assertSame('danger', $feed['items'][0]['tone']);
    }

    public function test_the_banner_names_three_and_counts_the_rest(): void
    {
        $admin = $this->user('admin');
        foreach (range(1, 5) as $i) {
            $this->stored($admin, LowStockAlert::class, "Low stock: Item {$i}", 'x', 'Product', $i, minutesAgo: 10 - $i);
        }

        Sanctum::actingAs($admin);
        $feed = $this->getJson(route('notifications.poll'))->json('stock_alerts');

        $this->assertSame(5, $feed['count']);
        $this->assertCount(3, $feed['items']);
        $this->assertSame(2, $feed['more']);
        $this->assertSame('Low stock: Item 5', $feed['items'][0]['title'], 'Newest first.');
    }

    public function test_stock_that_was_already_low_shows_without_any_alert(): void
    {
        // Created low, so no level ever crossed and no notification exists.
        $this->material('Sublimation Paper', stock: 4, threshold: 10);
        $admin = $this->user('admin');

        Sanctum::actingAs($admin);

        $banner = $this->banner($this->get(route('admin.dashboard'))->assertOk()->getContent());

        $this->assertStringContainsString('Low stock: Sublimation Paper', $banner);
        $this->assertStringContainsString('Only 4 sheets left (threshold 10).', $banner);
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_dismissed_stock_stays_dismissed_until_something_new_runs_low(): void
    {
        $this->material('Sublimation Paper', stock: 4, threshold: 10);
        $staff = $this->user('staff');

        Sanctum::actingAs($staff);
        $this->postJson(route('notifications.stockAlerts.dismiss'))->assertOk();

        $this->assertSame(0, $this->getJson(route('notifications.poll'))->json('stock_alerts.count'), 'Still low, but already seen.');

        $this->texture('Carbon Weave', stock: 0, threshold: 5);

        $feed = $this->getJson(route('notifications.poll'))->json('stock_alerts');
        $this->assertSame(1, $feed['count']);
        $this->assertSame('Out of stock: Carbon Weave', $feed['items'][0]['title']);
        $this->assertTrue($feed['out_of_stock']);
    }

    public function test_an_item_that_recovers_and_runs_low_again_is_news_again(): void
    {
        $paper = $this->material('Sublimation Paper', stock: 4, threshold: 10);
        $admin = $this->user('admin');

        Sanctum::actingAs($admin);
        $this->postJson(route('notifications.stockAlerts.dismiss'))->assertOk();

        $paper->update(['stock_quantity' => 50]);
        $this->getJson(route('notifications.poll'))->assertOk();
        $paper->update(['stock_quantity' => 3]);

        $feed = $this->getJson(route('notifications.poll'))->json('stock_alerts');
        $this->assertSame(1, $feed['count']);
        $this->assertSame('Only 3 sheets left (threshold 10).', $feed['items'][0]['body'], 'Described as it stands now.');
    }

    public function test_out_of_stock_items_are_named_first(): void
    {
        $this->material('Sublimation Paper', stock: 4, threshold: 10);
        $this->texture('Carbon Weave', stock: 0, threshold: 5);

        Sanctum::actingAs($this->user('admin'));
        $feed = $this->getJson(route('notifications.poll'))->json('stock_alerts');

        $this->assertSame(['Out of stock: Carbon Weave', 'Low stock: Sublimation Paper'], array_column($feed['items'], 'title'));
    }

    public function test_customers_get_no_banner(): void
    {
        $customer = $this->user('customer');

        Sanctum::actingAs($customer);

        $this->get(route('customer.orders.index'))->assertOk()->assertDontSee('class="stock-alert-banner', false);
        $this->assertNull($this->getJson(route('notifications.poll'))->json('stock_alerts'));
    }

    public function test_stock_alerts_do_not_also_toast_where_the_banner_shows(): void
    {
        Sanctum::actingAs($this->user('admin'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee("fresh = fresh.filter(function (it) { return it.category !== 'stock'; });", false)
            ->assertSee('window.renderStockAlerts(data.stock_alerts)', false);
    }

    // ---------------------------------------------------------------- helpers

    /** The banner's own markup, so a title the bell also lists can't pass for it. */
    private function banner(string $html): string
    {
        $start = strpos($html, '<div class="stock-alert-banner');
        $this->assertNotFalse($start, 'The page has no stock alert banner.');

        return substr($html, $start, strpos($html, 'data-stock-alert-dismiss', $start) - $start);
    }

    private function stored(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $itemType,
        ?int $itemId,
        bool $read = false,
        string $category = 'stock',
        int $minutesAgo = 0,
    ): void {
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => array_filter([
                'category' => $category, 'icon' => 'bi-bell', 'title' => $title, 'body' => $body, 'url' => '/',
                'item_type' => $itemType, 'item_id' => $itemId,
            ], fn ($value) => $value !== null),
            'read_at' => $read ? now() : null,
        ]);

        if ($minutesAgo > 0) {
            $notification->forceFill(['created_at' => now()->subMinutes($minutesAgo)])->save();
        }
    }

    private function material(string $name, float $stock, float $threshold): RawMaterial
    {
        $supplier = Supplier::firstOrCreate(['name' => 'Supplier'], ['email' => 'sup@example.test']);

        return RawMaterial::create([
            'name' => $name, 'supplier_id' => $supplier->supplier_id, 'cost_per_unit' => 10,
            'stock_quantity' => $stock, 'low_stock_threshold' => $threshold, 'unit' => 'sheets',
        ]);
    }

    private function texture(string $name, float $stock, float $threshold): Texture
    {
        return Texture::create([
            'name' => $name, 'cost_per_unit' => 5, 'stock_quantity' => $stock,
            'low_stock_threshold' => $threshold, 'unit' => 'pcs', 'price_modifier' => 0,
        ]);
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
