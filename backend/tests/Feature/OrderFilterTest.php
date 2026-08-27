<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The status filter on the admin and staff order lists.
 *
 * The case that matters is the empty one. Clearing the dropdown submits
 * `status=`, which ConvertEmptyStringsToNull turns into null before the
 * controller ever sees it — and a null slipping past the "is it blank" guard
 * becomes `where status is null`, which no order matches. That looked exactly
 * like every order vanishing the moment you asked for all of them.
 */
class OrderFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->user('admin', 'a@example.test');
        $this->staff = $this->user('staff', 's@example.test');
        $customer = $this->user('customer', 'c@example.test');

        foreach (['pending', 'awaiting_pr', 'approved', 'for_delivery', 'cancelled'] as $i => $status) {
            Order::create([
                'order_number' => 'ORDR-FILTER-' . $i,
                'user_id' => $customer->id,
                'status' => $status,
                'total_amount' => 100,
            ]);
        }
    }

    public static function orderScreens(): array
    {
        return [
            'admin' => ['admin', 'admin.orders.index'],
            'staff' => ['staff', 'staff.orders.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('orderScreens')]
    public function test_an_empty_status_shows_every_order(string $role, string $route): void
    {
        Sanctum::actingAs($this->{$role});

        // Exactly what the form sends once the dropdown is put back to
        // "All Statuses" — present but empty, not absent.
        $response = $this->get(route($route, [
            'per_page' => 10, 'search' => '', 'status' => '', 'date' => '',
        ]));

        $response->assertOk();
        $this->assertSame(5, $response->viewData('orders')->total());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('orderScreens')]
    public function test_no_filter_at_all_shows_every_order(string $role, string $route): void
    {
        Sanctum::actingAs($this->{$role});

        $response = $this->get(route($route));

        $response->assertOk();
        $this->assertSame(5, $response->viewData('orders')->total());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('orderScreens')]
    public function test_a_chosen_status_still_narrows_the_list(string $role, string $route): void
    {
        Sanctum::actingAs($this->{$role});

        $response = $this->get(route($route, ['status' => 'awaiting_pr']));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('orders')->total());
        $this->assertSame('awaiting_pr', $response->viewData('orders')->first()->status);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('orderScreens')]
    public function test_an_empty_search_does_not_narrow_the_list(string $role, string $route): void
    {
        Sanctum::actingAs($this->{$role});

        $response = $this->get(route($route, ['search' => '', 'status' => '', 'date' => '']));

        $response->assertOk();
        $this->assertSame(5, $response->viewData('orders')->total());
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }
}
