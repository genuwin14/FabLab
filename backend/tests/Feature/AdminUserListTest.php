<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The user list used to load every account at once, with search as its only filter. */
class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::create([
            'fullname' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'role' => 'admin', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]));

        foreach (range(1, 15) as $i) {
            User::create([
                'fullname' => "Customer {$i}", 'email' => "c{$i}@example.test", 'password' => 'password',
                'role' => 'customer', 'contact_number' => '0912345' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'phone_verified' => true, 'status' => $i % 3 === 0 ? 'disabled' : 'active',
            ]);
        }

        User::create([
            'fullname' => 'Staff One', 'email' => 'staff@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09998887777', 'phone_verified' => true,
        ]);
    }

    private function listed($response): int
    {
        return $response->viewData('users')->count();
    }

    public function test_the_list_is_paginated(): void
    {
        $response = $this->get('/admin/users')->assertOk();

        $this->assertSame(10, $this->listed($response));
        $this->assertSame(17, $response->viewData('users')->total());

        $this->assertSame(7, $this->listed($this->get('/admin/users?page=2')));
    }

    public function test_page_size_is_selectable(): void
    {
        $this->assertSame(17, $this->listed($this->get('/admin/users?per_page=25')));

        // An unsupported size falls back to the default rather than trusting it.
        $this->assertSame(10, $this->listed($this->get('/admin/users?per_page=9999')));
    }

    public function test_filtering_by_role(): void
    {
        $response = $this->get('/admin/users?role=staff')->assertOk();

        $this->assertSame(1, $this->listed($response));
        $this->assertSame('Staff One', $response->viewData('users')->first()->fullname);
    }

    public function test_filtering_by_status(): void
    {
        $response = $this->get('/admin/users?status=disabled&per_page=25')->assertOk();

        $this->assertSame(5, $this->listed($response)); // every third customer
    }

    public function test_filters_combine_with_search(): void
    {
        $response = $this->get('/admin/users?role=customer&search=Customer%201&per_page=25')->assertOk();

        // "Customer 1", "Customer 10" … "Customer 15"
        $this->assertSame(7, $this->listed($response));
    }
}
