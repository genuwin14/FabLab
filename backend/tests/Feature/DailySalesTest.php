<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The panel: "Remove Point-of-sale, and Sales" and "Add daily sales".
 *
 * There is no point of sale, and what was the Sales module is now the Daily
 * Sales report: named so in the sidebar and on the page, with a row per day
 * (per month over a wide range) under the chart.
 */
class DailySalesTest extends TestCase
{
    use RefreshDatabase;

    public static function screens(): array
    {
        return [
            'admin' => ['admin', 'admin.sales.index'],
            'staff' => ['staff', 'staff.sales.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('screens')]
    public function test_the_sales_page_is_the_daily_sales_report(string $role, string $route): void
    {
        $customer = $this->user('customer');
        $this->completedOrder($customer, 350, now()->startOfDay()->addHours(2));
        $this->completedOrder($customer, 150, now()->subDays(2)->startOfDay()->addHours(3));

        Sanctum::actingAs($this->user($role));

        $response = $this->get(route($route))->assertOk();

        $response->assertSee('<h4 class="fw-bold text-dark mb-1">Daily Sales</h4>', false)
            ->assertSee('<span class="sidebar-label">Daily Sales</span>', false)
            ->assertDontSee('Sales & Orders')
            ->assertSee('Sales by Day')
            ->assertSeeInOrder([now()->format('D, M j, Y'), 'Today', '₱350.00', now()->subDays(1)->format('D, M j, Y'), '₱0.00', now()->subDays(2)->format('D, M j, Y'), '₱150.00']);
    }

    public function test_a_wide_range_is_tabled_by_month(): void
    {
        Sanctum::actingAs($this->user('admin'));

        $this->get(route('admin.sales.index', ['range' => '12months']))
            ->assertOk()
            ->assertSee('Sales by Month')
            ->assertSee(now()->format('M Y'));
    }

    public function test_there_is_no_point_of_sale(): void
    {
        $names = collect(app('router')->getRoutes())->map(fn ($route) => (string) $route->getName());

        $this->assertFalse($names->contains(fn ($name) => str_contains($name, 'pos')), 'No point-of-sale route.');
    }

    private function completedOrder(User $customer, float $total, $when): void
    {
        $order = Order::create([
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $customer->id,
            'status' => 'completed',
            'total_amount' => $total,
        ]);
        $order->forceFill(['created_at' => $when])->save();
    }

    private function user(string $role): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => "{$role}@example.test", 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }
}
