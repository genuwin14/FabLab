<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Every order says who it is for — the customer themself, or a CSPC office —
 * and whether it is paid at PAXS or through procurement. The customer,
 * the admin and staff all see the same two facts.
 *
 * A Purchase Request is filed by an office, so checkout will not take one
 * without an office named, whatever the form claims.
 */
class OrderOfficeTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private User $staff;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->customer = $this->user('customer', 'c@example.test');
        $this->admin = $this->user('admin', 'a@example.test');
        $this->staff = $this->user('staff', 's@example.test');

        $category = Category::create(['name' => 'Mugs', 'description' => 'x']);

        $this->product = Product::create([
            'name' => 'Mug', 'sku' => 'MG-1', 'category_id' => $category->category_id,
            'price' => 100, 'stock' => 20, 'is_customizable' => false,
        ]);
    }

    // ---------------------------------------------------------------- checkout

    public function test_a_personal_paxs_order_names_no_office(): void
    {
        $this->checkout(['payment_method' => Order::METHOD_CASH, 'ordered_for' => 'personal'])
            ->assertOk()->assertJson(['success' => true]);

        $order = Order::firstOrFail();
        $this->assertNull($order->office);
        $this->assertSame('Personal', $order->ordered_for);
        $this->assertSame('PAXS', $order->channel_label);
    }

    public function test_an_office_can_pay_at_paxs(): void
    {
        $this->checkout([
            'payment_method' => Order::METHOD_CASH,
            'ordered_for' => 'office',
            'office' => "  Office of the   Registrar \n",
        ])->assertOk();

        $order = Order::firstOrFail();
        $this->assertSame('Office of the Registrar', $order->office);
        $this->assertSame('Office of the Registrar', $order->ordered_for);
        $this->assertSame('PAXS', $order->channel_label);
    }

    public function test_a_purchase_request_records_its_office(): void
    {
        $this->checkout([
            'payment_method' => Order::METHOD_PR,
            'ordered_for' => 'office',
            'office' => 'College of Computer Studies',
        ])->assertOk();

        $order = Order::firstOrFail();
        $this->assertSame('College of Computer Studies', $order->office);
        $this->assertSame('Procurement', $order->channel_label);
    }

    public function test_a_purchase_request_without_an_office_is_turned_down(): void
    {
        $this->checkout(['payment_method' => Order::METHOD_PR, 'ordered_for' => 'office', 'office' => '   '])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'A Purchase Request is filed by an office. Enter which office this order is for.',
            ]);

        $this->assertSame(0, Order::count());
        $this->assertSame(1, CartItem::count(), 'nothing was checked out, so the cart keeps its line');
        $this->assertSame(20, $this->product->fresh()->stock);
    }

    public function test_a_purchase_request_is_an_offices_even_if_the_form_says_personal(): void
    {
        // Personal is locked out on the page once PR is picked; a request
        // that claims it anyway still has to name the office.
        $this->checkout(['payment_method' => Order::METHOD_PR, 'ordered_for' => 'personal'])
            ->assertStatus(422);

        $this->checkout([
            'payment_method' => Order::METHOD_PR,
            'ordered_for' => 'personal',
            'office' => 'Office of the President',
        ])->assertOk();

        $this->assertSame('Office of the President', Order::firstOrFail()->office);
    }

    public function test_choosing_office_without_naming_one_is_turned_down(): void
    {
        $this->checkout(['payment_method' => Order::METHOD_CASH, 'ordered_for' => 'office'])
            ->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Enter which office this order is for.']);

        $this->assertSame(0, Order::count());
    }

    public function test_a_personal_order_drops_an_office_typed_before_switching_back(): void
    {
        $this->checkout([
            'payment_method' => Order::METHOD_CASH,
            'ordered_for' => 'personal',
            'office' => 'Office of the Registrar',
        ])->assertOk();

        $this->assertNull(Order::firstOrFail()->office);
    }

    public function test_an_overlong_office_is_turned_down(): void
    {
        $this->checkout([
            'payment_method' => Order::METHOD_CASH,
            'ordered_for' => 'office',
            'office' => str_repeat('A', 256),
        ])->assertStatus(422)->assertJsonValidationErrors('office');

        $this->assertSame(0, Order::count());
    }

    public function test_the_cart_offers_back_the_offices_this_customer_used(): void
    {
        $this->order(['office' => 'Office of the Registrar']);
        $this->order(['office' => 'Somebody Else Office', 'user_id' => $this->admin->id]);

        Sanctum::actingAs($this->customer);

        $this->get(route('customer.cart.index'))
            ->assertOk()
            ->assertSee('<option value="Office of the Registrar"></option>', false)
            ->assertDontSee('Somebody Else Office');
    }

    // ------------------------------------------------------------ order lists

    public static function orderLists(): array
    {
        return [
            'customer' => ['customer', 'customer.orders.index'],
            'admin' => ['admin', 'admin.orders.index'],
            'staff' => ['staff', 'staff.orders.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('orderLists')]
    public function test_every_order_list_says_who_each_order_is_for(string $role, string $route): void
    {
        $this->order(['order_number' => 'ORDR-OFFICE-1', 'payment_method' => Order::METHOD_PR, 'office' => 'College of Computer Studies']);
        $this->order(['order_number' => 'ORDR-OFFICE-2', 'payment_method' => Order::METHOD_CASH, 'office' => null]);

        Sanctum::actingAs($this->{$role});

        $response = $this->get(route($route))
            ->assertOk()
            ->assertSee('College of Computer Studies')
            ->assertSee('Procurement')
            ->assertSee('Personal')
            ->assertSee('PAXS');

        // Only the customer's list has room for a column of its own; the
        // admin and staff lists carry it under the customer's email.
        if ($role === 'customer') {
            $response->assertSee('Ordered For');
        }
    }

    public static function staffLists(): array
    {
        return [
            'admin' => ['admin', 'admin.orders.index'],
            'staff' => ['staff', 'staff.orders.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('staffLists')]
    public function test_the_order_lists_search_by_office(string $role, string $route): void
    {
        $this->order(['order_number' => 'ORDR-OFFICE-1', 'office' => 'College of Computer Studies']);
        $this->order(['order_number' => 'ORDR-OFFICE-2', 'office' => 'Office of the Registrar']);

        Sanctum::actingAs($this->{$role});

        $orders = $this->get(route($route, ['search' => 'Computer']))->assertOk()->viewData('orders');

        $this->assertSame(['ORDR-OFFICE-1'], $orders->pluck('order_number')->all());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('orderLists')]
    public function test_an_office_name_is_shown_as_text_not_markup(string $role, string $route): void
    {
        // Customers type the name, and admins and staff read it.
        $this->order(['office' => '<b onmouseover="alert(1)">Registrar</b>']);

        Sanctum::actingAs($this->{$role});

        $this->get(route($route))
            ->assertOk()
            ->assertDontSee('<b onmouseover="alert(1)">', false)
            ->assertSee('&lt;b onmouseover=&quot;alert(1)&quot;&gt;Registrar&lt;/b&gt;', false);
    }

    public function test_the_modals_get_both_labels_with_the_order(): void
    {
        $order = $this->order(['payment_method' => Order::METHOD_PR, 'office' => 'Office of the President']);

        $json = $order->fresh()->toArray();

        $this->assertSame('Office of the President', $json['ordered_for']);
        $this->assertSame('Procurement', $json['channel_label']);
    }

    public function test_a_purchase_request_from_before_the_question_is_not_called_personal(): void
    {
        $order = $this->order(['payment_method' => Order::METHOD_PR, 'office' => null]);

        $this->assertSame('Office not recorded', $order->ordered_for);
    }

    // ---------------------------------------------------------------- helpers

    private function checkout(array $fields): \Illuminate\Testing\TestResponse
    {
        Sanctum::actingAs($this->customer);

        $line = CartItem::firstOrCreate(
            ['user_id' => $this->customer->id, 'product_id' => $this->product->product_id],
            ['quantity' => 2, 'price' => 100],
        );

        // The page checks out over AJAX and reads the answer as JSON.
        return $this->postJson(route('customer.cart.checkout'), $fields + [
            'selected_items' => [$line->lineKey()],
        ]);
    }

    private function order(array $attributes): Order
    {
        return Order::create($attributes + [
            'order_number' => 'ORDR-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_method' => Order::METHOD_CASH,
            'total_amount' => 100,
        ]);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }
}
