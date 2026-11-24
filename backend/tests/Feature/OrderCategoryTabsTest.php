<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The panel: "Categorize orders directory based on their status: Pickup,
 * Ongoing, Upcoming, or For Delivery."
 *
 * The order lists open on category tabs, each with a count: Upcoming (not in
 * production yet), Ongoing, Pickup, For Delivery, then Completed and
 * Cancelled, so every order sits under exactly one.
 */
class OrderCategoryTabsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->user('admin', 'a@example.test');
        $this->staff = $this->user('staff', 's@example.test');
        $this->customer = $this->user('customer', 'c@example.test');

        foreach (['pending', 'awaiting_pr', 'approved', 'paid', 'processing', 'ready_for_pickup', 'for_delivery', 'completed', 'cancelled'] as $status) {
            Order::create([
                'order_number' => 'ORDR-TAB-' . strtoupper($status),
                'user_id' => $this->customer->id,
                'status' => $status,
                'total_amount' => 100,
            ]);
        }
    }

    public static function lists(): array
    {
        return [
            'admin' => ['admin', 'admin.orders.index'],
            'staff' => ['staff', 'staff.orders.index'],
        ];
    }

    public function test_every_status_falls_under_exactly_one_category(): void
    {
        $covered = collect(Order::GROUPS)->flatMap(fn ($group) => $group['statuses']);

        $this->assertEqualsCanonicalizing(Order::STATUSES, $covered->all());
        $this->assertSame($covered->count(), $covered->unique()->count());
        $this->assertSame(['Upcoming', 'Ongoing', 'Pickup', 'For Delivery'], array_slice(array_column(Order::GROUPS, 'label'), 0, 4));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('lists')]
    public function test_the_list_opens_on_the_category_tabs_with_their_counts(string $role, string $route): void
    {
        Sanctum::actingAs($this->{$role});

        $response = $this->get(route($route))->assertOk();

        $this->assertSame(['all' => 9, 'upcoming' => 4, 'ongoing' => 1, 'pickup' => 1, 'delivery' => 1, 'completed' => 1, 'cancelled' => 1], $response->viewData('groupCounts'));
        $response->assertSeeInOrder(['Upcoming', 'Ongoing', 'Pickup', 'For Delivery', 'Completed', 'Cancelled']);
        $response->assertSee('aria-label="Order categories"', false);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('lists')]
    public function test_a_tab_lists_only_its_orders(string $role, string $route): void
    {
        Sanctum::actingAs($this->{$role});

        $upcoming = $this->get(route($route, ['group' => 'upcoming']))->assertOk();
        $this->assertEqualsCanonicalizing(
            ['pending', 'awaiting_pr', 'approved', 'paid'],
            collect($upcoming->viewData('orders')->items())->pluck('status')->all()
        );

        $pickup = $this->get(route($route, ['group' => 'pickup']))->assertOk();
        $this->assertSame(['ready_for_pickup'], collect($pickup->viewData('orders')->items())->pluck('status')->all());

        // Its status dropdown offers only that tab's statuses.
        $upcoming->assertSee('<option value="paid"', false)->assertDontSee('<option value="processing"', false);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('lists')]
    public function test_a_status_narrows_within_the_tab(string $role, string $route): void
    {
        Sanctum::actingAs($this->{$role});

        $response = $this->get(route($route, ['group' => 'upcoming', 'status' => 'approved']))->assertOk();

        $this->assertSame(['approved'], collect($response->viewData('orders')->items())->pluck('status')->all());
    }

    public function test_an_unknown_category_shows_everything(): void
    {
        Sanctum::actingAs($this->admin);

        $this->assertSame(9, $this->get(route('admin.orders.index', ['group' => 'nonsense']))->viewData('orders')->total());
    }

    public function test_the_counts_follow_the_search(): void
    {
        $list = app(OrderList::class);
        $filters = $list->filters(Request::create('/', 'GET', ['search' => 'PICKUP']));

        $this->assertSame(['all' => 1, 'upcoming' => 0, 'ongoing' => 0, 'pickup' => 1, 'delivery' => 0, 'completed' => 0, 'cancelled' => 0], $list->groupCounts($filters));
    }

    public function test_the_export_holds_the_tab_it_was_taken_from(): void
    {
        Sanctum::actingAs($this->admin);

        $this->get(route('admin.orders.index', ['group' => 'delivery']))
            ->assertSee(route('admin.orders.export', ['format' => 'csv', 'group' => 'delivery']), false);

        $csv = $this->get(route('admin.orders.export', ['format' => 'csv', 'group' => 'delivery']))->assertOk()->streamedContent();
        $this->assertStringContainsString('ORDR-TAB-FOR_DELIVERY', $csv);
        $this->assertStringNotContainsString('ORDR-TAB-PENDING', $csv);

        $filters = app(OrderList::class)->filters(Request::create('/', 'GET', ['group' => 'delivery']));
        $this->assertStringStartsWith('For Delivery · All statuses', app(OrderList::class)->describe($filters));
    }

    public function test_the_customers_orders_have_the_same_tabs(): void
    {
        Sanctum::actingAs($this->customer);

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('data-group-tab="upcoming"', false)
            ->assertSee('data-group-tab="delivery"', false)
            ->assertSee('data-group="ongoing"', false)
            ->assertSeeInOrder(['Upcoming', 'Ongoing', 'Pickup', 'For Delivery']);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }
}
