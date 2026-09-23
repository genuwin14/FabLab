<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OrderStatusChanged;
use App\Notifications\OutOfStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * New notifications pop up as toasts on every page, for every role, not only
 * in the bell. The bell's poll carries what a toast needs to say it: how
 * loudly (running out of stock is worse than running low) and which page it
 * is about, named the way the reader's own sidebar names it.
 */
class NotificationToastTest extends TestCase
{
    use RefreshDatabase;

    public static function readers(): array
    {
        return [
            'admin' => ['admin', 'admin.dashboard'],
            'staff' => ['staff', 'staff.dashboard'],
            'customer' => ['customer', 'customer.orders.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('readers')]
    public function test_every_role_gets_toasts_on_its_pages(string $role, string $page): void
    {
        $reader = $this->user($role);
        Sanctum::actingAs($reader);

        $this->get(route($page))
            ->assertOk()
            ->assertSee('window.showNotificationToast = function', false)
            ->assertSee("'fablab:notifications-seen:' + {$reader->id};", false);
    }

    public function test_the_poll_says_how_loudly_to_toast_and_which_page_it_is_about(): void
    {
        $admin = $this->user('admin');
        $this->stored($admin, OutOfStockAlert::class, 'stock');
        $this->stored($admin, LowStockAlert::class, 'stock');
        $this->stored($admin, NewOrderPlaced::class, 'order');

        Sanctum::actingAs($admin);

        $items = collect($this->getJson(route('notifications.poll'))->assertOk()->json('items'))->keyBy('title');

        $this->assertSame(['danger', 'Stock Monitoring'], [$items[OutOfStockAlert::class]['tone'], $items[OutOfStockAlert::class]['category_label']]);
        $this->assertSame(['warning', 'Stock Monitoring'], [$items[LowStockAlert::class]['tone'], $items[LowStockAlert::class]['category_label']]);
        $this->assertSame(['info', 'All Orders'], [$items[NewOrderPlaced::class]['tone'], $items[NewOrderPlaced::class]['category_label']]);
    }

    public function test_each_role_hears_its_own_name_for_the_page(): void
    {
        $staff = $this->user('staff');
        $this->stored($staff, LowStockAlert::class, 'stock');
        $this->stored($staff, NewOrderPlaced::class, 'order');

        Sanctum::actingAs($staff);
        $labels = collect($this->getJson(route('notifications.poll'))->json('items'))->pluck('category_label', 'title');

        $this->assertSame('Inventory Logs', $labels[LowStockAlert::class]);
        $this->assertSame('Orders', $labels[NewOrderPlaced::class]);

        $customer = $this->user('customer');
        $this->stored($customer, OrderStatusChanged::class, 'order');

        Sanctum::actingAs($customer);
        $item = $this->getJson(route('notifications.poll'))->json('items.0');

        $this->assertSame(['info', 'My Orders'], [$item['tone'], $item['category_label']]);
    }

    // ---------------------------------------------------------------- helpers

    /** A stored notification, titled after its class so the test can find it. */
    private function stored(User $user, string $type, string $category): void
    {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => ['category' => $category, 'icon' => 'bi-bell', 'title' => $type, 'body' => 'x', 'url' => '/'],
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
