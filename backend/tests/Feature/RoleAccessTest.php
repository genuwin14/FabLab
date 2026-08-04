<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Every route lived behind `auth:sanctum` alone, so any signed-in customer
 * could reach the admin and staff areas — approving their own orders,
 * disabling admins, deleting products. These tests pin the role boundaries.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role) . ' User',
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'contact_number' => '09123456789',
            'phone_verified' => true,
        ]);
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Cat', 'description' => 'x']);

        return Product::create([
            'sku' => 'P-1', 'name' => 'P', 'price' => 100, 'stock' => 5, 'unit' => 'pcs',
            'category_id' => $category->category_id, 'status' => 'active', 'low_stock_threshold' => 1,
        ]);
    }

    private function order(User $customer): Order
    {
        return Order::create([
            'order_number' => 'ORDR-TEST',
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_amount' => 100,
        ]);
    }

    // ---------------------------------------------------------------- guests

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/staff/orders')->assertRedirect('/login');
        $this->get('/customer/shop')->assertRedirect('/login');
    }

    // ------------------------------------------------------------- customers

    public function test_customer_browsing_an_admin_page_is_sent_home(): void
    {
        Sanctum::actingAs($this->user('customer', 'c@example.test'));

        $this->get('/admin/dashboard')->assertRedirect(route('customer.shop'));
        $this->get('/admin/users')->assertRedirect(route('customer.shop'));
        $this->get('/staff/orders')->assertRedirect(route('customer.shop'));
    }

    public function test_customer_cannot_approve_their_own_order(): void
    {
        $customer = $this->user('customer', 'c@example.test');
        $order = $this->order($customer);
        Sanctum::actingAs($customer);

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertForbidden();

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_customer_cannot_disable_another_account(): void
    {
        $admin = $this->user('admin', 'a@example.test');
        Sanctum::actingAs($this->user('customer', 'c@example.test'));

        $this->post("/admin/users/{$admin->id}/status", ['status' => 'disabled'])
            ->assertForbidden();

        $this->assertSame('active', $admin->refresh()->status);
    }

    public function test_customer_cannot_delete_a_product(): void
    {
        $product = $this->product();
        Sanctum::actingAs($this->user('customer', 'c@example.test'));

        $this->delete("/admin/products/{$product->product_id}")->assertForbidden();

        $this->assertNotNull(Product::find($product->product_id));
    }

    public function test_customer_keeps_their_own_area(): void
    {
        Sanctum::actingAs($this->user('customer', 'c@example.test'));

        $this->get('/customer/shop')->assertOk();
        $this->get('/customer/orders')->assertOk();
        $this->get('/customer/my-designs')->assertOk();
    }

    // ----------------------------------------------------------------- staff

    public function test_staff_cannot_reach_the_admin_area(): void
    {
        $customer = $this->user('customer', 'c@example.test');
        $order = $this->order($customer);
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->get('/admin/dashboard')->assertRedirect(route('staff.dashboard'));
        $this->get('/admin/reports/materials')->assertRedirect(route('staff.dashboard'));

        $this->post("/admin/orders/{$order->order_id}/review", ['status' => 'approved'])
            ->assertForbidden();

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_staff_keep_their_own_area(): void
    {
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->get('/staff/dashboard')->assertOk();
        $this->get('/staff/orders')->assertOk();
        $this->get('/staff/purchase')->assertOk();
    }

    public function test_staff_cannot_shop(): void
    {
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->get('/customer/shop')->assertRedirect(route('staff.dashboard'));
        $this->post('/customer/cart/add', ['product_id' => 1, 'quantity' => 1])->assertForbidden();
    }

    // ----------------------------------------------------------------- admin

    public function test_admin_reaches_the_admin_area(): void
    {
        Sanctum::actingAs($this->user('admin', 'a@example.test'));

        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/orders')->assertOk();
    }

    /** Fulfilment lives only under /staff, so admins are allowed in there too. */
    public function test_admin_may_also_use_the_staff_screens(): void
    {
        Sanctum::actingAs($this->user('admin', 'a@example.test'));

        $this->get('/staff/orders')->assertOk();
        $this->get('/staff/dashboard')->assertOk();
    }

    public function test_admin_cannot_shop(): void
    {
        Sanctum::actingAs($this->user('admin', 'a@example.test'));

        $this->get('/customer/shop')->assertRedirect(route('admin.dashboard'));
    }

    // ---------------------------------------------------------------- shared

    public function test_every_role_keeps_notifications(): void
    {
        foreach ([['customer', 'c@example.test'], ['staff', 's@example.test'], ['admin', 'a@example.test']] as [$role, $email]) {
            Sanctum::actingAs($this->user($role, $email));

            $this->get('/notifications')->assertOk();
            $this->get('/notifications/poll')->assertOk();
        }
    }
}
