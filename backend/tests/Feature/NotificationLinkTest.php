<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\CustomDesign;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\CustomDesignSubmitted;
use App\Notifications\LowStockAlert;
use App\Notifications\NewCustomerRegistered;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderStatusChanged;
use App\Notifications\OutOfStockAlert;
use App\Notifications\PaymentRecorded;
use App\Notifications\PurchaseOrderOverdue;
use App\Notifications\PurchaseOrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Clicking a notification used to land on the login page whenever the reader
 * was on a different address from whoever raised it: links were stored
 * whole, host and all, taken from the customer's checkout request. They are
 * stored as paths now, and every click goes through notifications.open,
 * which redirects on the reader's own host — including for links stored
 * whole before the fix.
 */
class NotificationLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = $this->user('admin', 'a@example.test');
        $this->staff = $this->user('staff', 's@example.test');
        $this->customer = $this->user('customer', 'c@example.test');
    }

    public function test_a_new_order_links_by_path_not_by_the_host_the_customer_used(): void
    {
        $category = Category::create(['name' => 'Mugs', 'description' => 'x']);
        $product = Product::create([
            'name' => 'Mug', 'sku' => 'MG-1', 'category_id' => $category->category_id,
            'price' => 100, 'stock' => 20, 'is_customizable' => false,
        ]);
        $line = CartItem::create([
            'user_id' => $this->customer->id, 'product_id' => $product->product_id, 'quantity' => 1, 'price' => 100,
        ]);

        // The customer checks out on one address...
        Sanctum::actingAs($this->customer);
        $this->postJson('http://127.0.0.1:8000/customer/cart/checkout', ['selected_items' => [$line->lineKey()]])->assertOk();
        $order = Order::firstOrFail();

        // ...and nothing about that address is kept.
        $this->assertSame('/admin/orders?search=' . $order->order_number, $this->admin->notifications()->first()->data['url']);
        $this->assertSame('/staff/orders?search=' . $order->order_number, $this->staff->notifications()->first()->data['url']);
        $this->assertSame('/customer/orders#order-' . $order->order_id, $this->customer->notifications()->first()->data['url']);

        // The admin, signed in on another address, stays on it.
        Sanctum::actingAs($this->admin);
        $id = $this->admin->notifications()->first()->id;

        $this->get("http://localhost:8000/notifications/{$id}/open")
            ->assertRedirect('http://localhost:8000/admin/orders?search=' . $order->order_number);
    }

    public function test_every_notification_stores_a_path_for_the_page_it_is_about(): void
    {
        $order = new Order(['order_number' => 'ORDR-20260923-0001', 'total_amount' => 95, 'payment_reference' => 'OR-1']);
        $order->order_id = 5;
        $order->setRelation('user', $this->customer);

        $design = new CustomDesign();
        $design->custom_design_id = 3;
        $design->setRelation('user', $this->customer);
        $design->setRelation('product', new Product(['name' => 'Mug']));

        $item = new Product(['name' => 'Mug', 'stock' => 2, 'low_stock_threshold' => 5]);
        $item->product_id = 9;

        $po = new PurchaseOrder(['po_number' => 'PO-1', 'status' => 'sent']);
        $po->purchase_order_id = 7;

        $expected = [
            [new NewOrderPlaced($order), $this->admin, '/admin/orders?search=ORDR-20260923-0001'],
            [new NewOrderPlaced($order), $this->staff, '/staff/orders?search=ORDR-20260923-0001'],
            [new CustomDesignSubmitted($design), $this->admin, '/admin/dashboard'],
            [new CustomDesignSubmitted($design), $this->staff, '/staff/dashboard'],
            [new NewCustomerRegistered($this->customer), $this->admin, '/admin/users?search=c%40example.test'],
            [new NewCustomerRegistered($this->customer), $this->staff, '/staff/dashboard'],
            [new LowStockAlert($item), $this->admin, '/admin/inventory'],
            [new OutOfStockAlert($item), $this->staff, '/staff/inventory'],
            [new PurchaseOrderOverdue($po), $this->admin, '/admin/purchase/7'],
            [new PurchaseOrderStatusChanged($po, 'draft', 'sent'), $this->staff, '/staff/purchase/7'],
            [new OrderPlaced($order), $this->customer, '/customer/orders#order-5'],
            [new OrderStatusChanged($order, 'pending', 'approved'), $this->customer, '/customer/orders#order-5'],
            [new PaymentRecorded($order), $this->customer, '/customer/orders#order-5'],
        ];

        foreach ($expected as [$notification, $reader, $path]) {
            $this->assertSame($path, $notification->toArray($reader)['url'], class_basename($notification) . ' for ' . $reader->role);
        }
    }

    public function test_a_link_stored_whole_before_the_fix_is_followed_on_the_readers_host(): void
    {
        $notification = $this->stored($this->admin, 'http://127.0.0.1:8000/admin/orders?search=ORDR-1');

        Sanctum::actingAs($this->admin);

        $this->get("http://localhost:8000/notifications/{$notification->id}/open")
            ->assertRedirect('http://localhost:8000/admin/orders?search=ORDR-1');
    }

    public function test_opening_marks_it_read(): void
    {
        $notification = $this->stored($this->customer, '/customer/orders#order-5');

        Sanctum::actingAs($this->customer);

        $this->get(route('notifications.open', $notification->id))
            ->assertRedirect(url('/customer/orders') . '#order-5');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public static function unusableLinks(): array
    {
        return [
            'missing' => [null],
            'script' => ['javascript:alert(document.cookie)'],
            'not a path' => ['admin/orders'],
            'another site, path doubled' => ['https://example.test//evil.example/login'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unusableLinks')]
    public function test_a_link_that_is_not_a_path_on_this_site_opens_the_list(?string $url): void
    {
        $notification = $this->stored($this->admin, $url);

        Sanctum::actingAs($this->admin);

        $this->get(route('notifications.open', $notification->id))->assertRedirect(route('notifications.index'));
    }

    public function test_a_foreign_host_is_dropped_and_its_path_followed_here(): void
    {
        $notification = $this->stored($this->admin, 'https://evil.example/admin/orders');

        Sanctum::actingAs($this->admin);

        $this->get('http://localhost:8000/notifications/' . $notification->id . '/open')
            ->assertRedirect('http://localhost:8000/admin/orders');
    }

    public function test_nobody_opens_someone_elses_notification(): void
    {
        $notification = $this->stored($this->admin, '/admin/orders');

        Sanctum::actingAs($this->staff);

        $this->get(route('notifications.open', $notification->id))->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_the_bell_the_list_and_the_poll_all_link_through_open(): void
    {
        $notification = $this->stored($this->admin, 'http://old-address.test/admin/orders');
        $open = route('notifications.open', $notification->id);

        Sanctum::actingAs($this->admin);

        $this->get(route('admin.dashboard'))->assertOk()
            ->assertSee('href="' . $open . '"', false)
            ->assertDontSee('old-address.test');
        $this->get(route('notifications.index'))->assertOk()
            ->assertSee('href="' . $open . '"', false)
            ->assertDontSee('old-address.test');
        $this->getJson(route('notifications.poll'))->assertOk()->assertJsonPath('items.0.url', $open);
    }

    // ---------------------------------------------------------------- helpers

    /** A notification as it sits in the table, link and all. */
    private function stored(User $user, ?string $url): DatabaseNotification
    {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => NewOrderPlaced::class,
            'data' => array_filter(['category' => 'order', 'icon' => 'bi-cart-plus', 'title' => 'New order ORDR-1', 'body' => 'x', 'url' => $url]),
        ]);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
            'status' => 'active', 'notifications_enabled' => true,
        ]);
    }
}
