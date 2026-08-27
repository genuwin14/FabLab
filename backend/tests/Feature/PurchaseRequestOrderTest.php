<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The Purchase Request path, for buyers going through CSPC procurement rather
 * than the cashier. The order is held until procurement issues a PR number,
 * then the Notice of Award starts production and the Purchase Order releases
 * it for delivery. Nobody at FabLab can jump ahead of the paperwork.
 */
class PurchaseRequestOrderTest extends TestCase
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

    public function test_choosing_pr_holds_the_order_short_of_review(): void
    {
        $order = $this->checkout(Order::METHOD_PR);

        $this->assertSame('awaiting_pr', $order->status);
        $this->assertSame(Order::METHOD_PR, $order->payment_method);
        $this->assertNotNull($order->pr_deadline);
        $this->assertNull($order->pr_number);
    }

    public function test_the_cashier_path_is_untouched(): void
    {
        $order = $this->checkout(Order::METHOD_CASH);

        $this->assertSame('pending', $order->status);
        $this->assertNull($order->pr_deadline);
    }

    public function test_checkout_defaults_to_cash_when_no_method_is_sent(): void
    {
        Sanctum::actingAs($this->customer);
        $this->cartLine();

        $this->post(route('customer.cart.checkout'), [
            'selected_items' => [$this->cartKey()],
        ]);

        $this->assertSame(Order::METHOD_CASH, Order::first()->payment_method);
    }

    public function test_the_window_is_the_configured_number_of_days(): void
    {
        config(['fablab.pr_deadline_days' => 3]);
        Carbon::setTestNow('2026-08-27 09:00:00');

        $order = $this->checkout(Order::METHOD_PR);

        $this->assertSame('2026-08-30', $order->pr_deadline->toDateString());
    }

    public function test_a_held_order_does_not_disturb_staff_yet(): void
    {
        $this->checkout(Order::METHOD_PR);

        $this->assertSame(0, $this->admin->notifications()->count());
        $this->assertSame(0, $this->staff->notifications()->count());
    }

    // --------------------------------------------------------------- PR number

    public function test_the_pr_number_releases_the_order_for_review(): void
    {
        $order = $this->checkout(Order::METHOD_PR);

        Sanctum::actingAs($this->customer);
        $this->post(route('customer.orders.prNumber', $order->order_id), ['pr_number' => 'PR-2026-001']);

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('PR-2026-001', $order->pr_number);
    }

    public function test_staff_hear_about_it_only_once_it_is_reviewable(): void
    {
        $order = $this->checkout(Order::METHOD_PR);

        Sanctum::actingAs($this->customer);
        $this->post(route('customer.orders.prNumber', $order->order_id), ['pr_number' => 'PR-2026-001']);

        $this->assertSame(1, $this->admin->notifications()->count());
        $this->assertSame(1, $this->staff->notifications()->count());
    }

    public function test_a_lapsed_window_refuses_a_late_pr_number(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');
        $order = $this->checkout(Order::METHOD_PR);

        Carbon::setTestNow('2026-09-10 09:00:00');

        Sanctum::actingAs($this->customer);
        $this->post(route('customer.orders.prNumber', $order->order_id), ['pr_number' => 'PR-LATE'])
            ->assertSessionHas('error');

        $this->assertSame('awaiting_pr', $order->refresh()->status);
        $this->assertNull($order->pr_number);
    }

    public function test_one_customer_cannot_supply_another_customers_pr_number(): void
    {
        $order = $this->checkout(Order::METHOD_PR);

        Sanctum::actingAs($this->user('customer', 'other@example.test'));
        $this->post(route('customer.orders.prNumber', $order->order_id), ['pr_number' => 'PR-X'])
            ->assertNotFound();

        $this->assertSame('awaiting_pr', $order->refresh()->status);
    }

    // ------------------------------------------------------------- admin gates

    public function test_admin_cannot_approve_before_the_pr_number_arrives(): void
    {
        $order = $this->checkout(Order::METHOD_PR);

        Sanctum::actingAs($this->admin);
        $this->post(route('admin.orders.review', $order->order_id), ['status' => 'approved'])
            ->assertSessionHas('error');

        $this->assertSame('awaiting_pr', $order->refresh()->status);
    }

    public function test_the_noa_starts_production_and_the_po_releases_delivery(): void
    {
        Storage::fake('local');
        $order = $this->approvedPrOrder();

        Sanctum::actingAs($this->admin);

        $this->post(route('admin.orders.documents.upload', [$order->order_id, 'noa']), [
            'document' => UploadedFile::fake()->create('noa.pdf', 10, 'application/pdf'),
        ]);

        $order->refresh();
        $this->assertSame('processing', $order->status);
        $this->assertNotNull($order->noa_path);

        $this->post(route('admin.orders.documents.upload', [$order->order_id, 'po']), [
            'document' => UploadedFile::fake()->create('po.pdf', 10, 'application/pdf'),
        ]);

        $order->refresh();
        $this->assertSame('for_delivery', $order->status);
        $this->assertNotNull($order->po_path);
    }

    public function test_the_po_cannot_jump_ahead_of_the_noa(): void
    {
        Storage::fake('local');
        $order = $this->approvedPrOrder();

        Sanctum::actingAs($this->admin);
        $this->post(route('admin.orders.documents.upload', [$order->order_id, 'po']), [
            'document' => UploadedFile::fake()->create('po.pdf', 10, 'application/pdf'),
        ])->assertSessionHas('error');

        $this->assertSame('approved', $order->refresh()->status);
    }

    public function test_a_cashier_order_has_no_paperwork_step(): void
    {
        Storage::fake('local');

        $order = $this->checkout(Order::METHOD_CASH);
        $order->update(['status' => 'approved']);

        Sanctum::actingAs($this->admin);
        $this->post(route('admin.orders.documents.upload', [$order->order_id, 'noa']), [
            'document' => UploadedFile::fake()->create('noa.pdf', 10, 'application/pdf'),
        ])->assertSessionHas('error');

        $this->assertSame('approved', $order->refresh()->status);
    }

    public function test_documents_are_not_reachable_without_admin(): void
    {
        Storage::fake('local');
        $order = $this->approvedPrOrder();

        Sanctum::actingAs($this->admin);
        $this->post(route('admin.orders.documents.upload', [$order->order_id, 'noa']), [
            'document' => UploadedFile::fake()->create('noa.pdf', 10, 'application/pdf'),
        ]);

        // A stray GET is sent home rather than shown an error page, which is
        // what the role middleware does for any plain page visit.
        Sanctum::actingAs($this->customer);
        $this->get(route('admin.orders.documents.show', [$order->order_id, 'noa']))
            ->assertRedirect(route('customer.shop'));
    }

    // ------------------------------------------------------------- staff gates

    public function test_staff_cannot_push_a_pr_order_past_the_paperwork(): void
    {
        $order = $this->approvedPrOrder();

        Sanctum::actingAs($this->staff);
        $this->post(route('staff.orders.updateStatus', $order->order_id), [
            'status' => 'processing',
            'payment_reference' => 'CASH-1',
        ])->assertSessionHas('error');

        $this->assertSame('approved', $order->refresh()->status);
    }

    public function test_staff_complete_a_pr_order_after_delivery(): void
    {
        $order = $this->approvedPrOrder();
        $order->update(['status' => 'for_delivery']);

        Sanctum::actingAs($this->staff);
        $this->post(route('staff.orders.updateStatus', $order->order_id), ['status' => 'completed']);

        $this->assertSame('completed', $order->refresh()->status);
    }

    // ----------------------------------------------------------------- closing

    public function test_the_sweep_closes_a_lapsed_order_and_returns_stock(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');
        $order = $this->checkout(Order::METHOD_PR);

        $this->assertSame(18, $this->product->refresh()->stock);

        Carbon::setTestNow('2026-09-10 09:00:00');
        $this->artisan('orders:close-expired-prs')->assertSuccessful();

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertSame(20, $this->product->refresh()->stock);
    }

    public function test_the_sweep_leaves_a_live_window_alone(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');
        $order = $this->checkout(Order::METHOD_PR);

        Carbon::setTestNow('2026-08-29 09:00:00');
        $this->artisan('orders:close-expired-prs')->assertSuccessful();

        $this->assertSame('awaiting_pr', $order->refresh()->status);
    }

    public function test_the_dry_run_changes_nothing(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');
        $order = $this->checkout(Order::METHOD_PR);

        Carbon::setTestNow('2026-09-10 09:00:00');
        $this->artisan('orders:close-expired-prs', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('awaiting_pr', $order->refresh()->status);
        $this->assertSame(18, $this->product->refresh()->stock);
    }

    public function test_an_admin_can_close_a_held_order_early(): void
    {
        $order = $this->checkout(Order::METHOD_PR);

        Sanctum::actingAs($this->admin);
        $this->post(route('admin.orders.closePr', $order->order_id), ['reason' => 'Client withdrew.']);

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('Client withdrew.', $order->reason);
        $this->assertSame(20, $this->product->refresh()->stock);
    }

    public function test_closing_only_applies_while_the_order_waits(): void
    {
        $order = $this->approvedPrOrder();

        Sanctum::actingAs($this->admin);
        $this->post(route('admin.orders.closePr', $order->order_id), ['reason' => 'x'])
            ->assertSessionHas('error');

        $this->assertSame('approved', $order->refresh()->status);
    }

    public function test_a_customer_may_drop_a_held_order(): void
    {
        $order = $this->checkout(Order::METHOD_PR);

        Sanctum::actingAs($this->customer);
        $this->post(route('customer.orders.cancel', $order->order_id));

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertSame(20, $this->product->refresh()->stock);
    }

    // ------------------------------------------------------------- the screens

    /**
     * Every order screen has its own copy of the status map, so a new status
     * is exactly the kind of thing one of them forgets. Render each with a PR
     * order sitting in each new state.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('prStates')]
    public function test_every_order_screen_renders_a_pr_order(string $state): void
    {
        $order = $this->checkout(Order::METHOD_PR);
        $order->update(['status' => $state, 'pr_number' => $state === 'awaiting_pr' ? null : 'PR-1']);

        Sanctum::actingAs($this->customer);
        $this->get(route('customer.orders.index'))->assertOk();

        Sanctum::actingAs($this->admin);
        $this->get(route('admin.orders.index'))->assertOk();

        Sanctum::actingAs($this->staff);
        $this->get(route('staff.orders.index'))->assertOk();
    }

    public static function prStates(): array
    {
        return [
            'awaiting the PR number' => ['awaiting_pr'],
            'approved, awaiting NOA' => ['approved'],
            'in production, awaiting PO' => ['processing'],
            'out for delivery' => ['for_delivery'],
        ];
    }

    public function test_the_held_order_offers_the_customer_a_way_to_enter_the_number(): void
    {
        $this->checkout(Order::METHOD_PR);

        Sanctum::actingAs($this->customer);
        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Enter PR Number')
            ->assertSee('Awaiting PR');
    }

    public function test_the_admin_is_offered_the_document_that_is_next(): void
    {
        $order = $this->approvedPrOrder();

        Sanctum::actingAs($this->admin);
        $this->get(route('admin.orders.index'))->assertOk()->assertSee('Upload NOA');

        $order->update(['status' => 'processing']);
        $this->get(route('admin.orders.index'))->assertOk()->assertSee('Upload PO');
    }

    // ----------------------------------------------------------------- helpers

    private function user(string $role, string $email): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => '09123456789', 'phone_verified' => true,
            'notifications_enabled' => true,
        ]);
    }

    private function cartLine(): CartItem
    {
        return CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'price' => 100,
        ]);
    }

    private function cartKey(): string
    {
        return (string) CartItem::where('user_id', $this->customer->id)->first()->cart_item_id;
    }

    private function checkout(string $method): Order
    {
        Sanctum::actingAs($this->customer);
        $this->cartLine();

        $this->post(route('customer.cart.checkout'), [
            'selected_items' => [$this->cartKey()],
            'payment_method' => $method,
        ]);

        return Order::latest('order_id')->first();
    }

    /** A PR order that has its number and has cleared admin review. */
    private function approvedPrOrder(): Order
    {
        $order = $this->checkout(Order::METHOD_PR);
        $order->update(['pr_number' => 'PR-2026-001', 'status' => 'approved']);

        return $order->refresh();
    }
}
