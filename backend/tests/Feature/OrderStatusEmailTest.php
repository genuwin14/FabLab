<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Every step of an order emails the customer as well as ringing the in-app
 * bell, so they stay up to date without opening the system. Approval is the
 * one exception: the transaction-slip email already announces it.
 */
class OrderStatusEmailTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'fullname' => 'Customer', 'email' => 'c@example.test', 'password' => 'password',
            'role' => 'customer', 'contact_number' => '09123456789', 'phone_verified' => true,
        ]);
    }

    private function order(string $status, ?string $reason = null): Order
    {
        return Order::create([
            'order_number' => 'ORDR-MAIL', 'user_id' => $this->customer->id,
            'status' => $status, 'total_amount' => 100, 'reason' => $reason,
        ]);
    }

    public function test_a_status_change_emails_the_customer_as_well_as_the_bell(): void
    {
        $order = $this->order('processing');

        $channels = (new OrderStatusChanged($order, 'approved', 'processing'))->via($this->customer);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_approval_leaves_the_email_to_the_transaction_slip(): void
    {
        $order = $this->order('approved');

        $channels = (new OrderStatusChanged($order, 'pending', 'approved'))->via($this->customer);

        $this->assertSame(['database'], $channels);
    }

    public function test_the_email_names_the_new_status(): void
    {
        $order = $this->order('ready_for_pickup');

        $mail = (new OrderStatusChanged($order, 'processing', 'ready_for_pickup'))->toMail($this->customer);
        $html = view($mail->view, $mail->viewData)->render();

        $this->assertStringContainsString('Order ORDR-MAIL - Ready For Pickup', $mail->subject);
        $this->assertStringContainsString('ready for pickup', $html);
        $this->assertStringContainsString($this->customer->fullname, $html);
    }

    public function test_a_cancellation_email_carries_the_reason(): void
    {
        $order = $this->order('cancelled', 'Out of acrylic sheets');

        $mail = (new OrderStatusChanged($order, 'approved', 'cancelled'))->toMail($this->customer);
        $html = view($mail->view, $mail->viewData)->render();

        $this->assertStringContainsString('cancelled', $html);
        $this->assertStringContainsString('Out of acrylic sheets', $html);
    }

    public function test_staff_advancing_an_order_notifies_the_customer(): void
    {
        Notification::fake();

        $order = $this->order('ready_for_pickup');

        $staff = User::create([
            'fullname' => 'Staff', 'email' => 's@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);
        Sanctum::actingAs($staff);

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'completed'])
            ->assertSessionHas('success');

        Notification::assertSentTo(
            $this->customer,
            OrderStatusChanged::class,
            fn ($notification, $channels) => in_array('mail', $channels, true)
        );
    }

    public function test_a_customer_who_disabled_notifications_gets_no_email(): void
    {
        Notification::fake();

        $this->customer->update(['notifications_enabled' => false]);
        $order = $this->order('ready_for_pickup');

        $staff = User::create([
            'fullname' => 'Staff', 'email' => 's@example.test', 'password' => 'password',
            'role' => 'staff', 'contact_number' => '09111111111', 'phone_verified' => true,
        ]);
        Sanctum::actingAs($staff);

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'completed'])
            ->assertSessionHas('success');

        Notification::assertNotSentTo($this->customer, OrderStatusChanged::class);
    }
}
