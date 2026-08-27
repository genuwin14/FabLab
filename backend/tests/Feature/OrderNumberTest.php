<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Order numbers carry the date they were placed plus that day's sequence, so a
 * consultant reading a slip can tell when the order was taken without looking
 * it up. The sequence restarts each day and ignores suffixes that aren't a
 * plain number.
 */
class OrderNumberTest extends TestCase
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

    public function test_first_order_of_the_day_starts_the_sequence(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');

        $this->assertSame('ORDR-20260827-0001', Order::nextOrderNumber());
    }

    public function test_sequence_counts_up_within_the_same_day(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');

        $this->placeOrder(Order::nextOrderNumber());
        $this->placeOrder(Order::nextOrderNumber());

        $this->assertSame('ORDR-20260827-0003', Order::nextOrderNumber());
    }

    public function test_sequence_restarts_on_the_next_day(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');
        $this->placeOrder(Order::nextOrderNumber());

        Carbon::setTestNow('2026-08-28 09:00:00');

        $this->assertSame('ORDR-20260828-0001', Order::nextOrderNumber());
    }

    public function test_non_numeric_suffixes_are_not_read_as_a_counter(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');

        // The shape the old seeder used to write.
        $this->placeOrder('ORDR-20260827-PEND');

        $this->assertSame('ORDR-20260827-0001', Order::nextOrderNumber());
    }

    public function test_legacy_uniqid_numbers_do_not_disturb_the_sequence(): void
    {
        Carbon::setTestNow('2026-08-27 09:00:00');

        // Orders placed before the format changed stay exactly as they are.
        $this->placeOrder('ORDR-68A1F3C2D4E5');

        $this->assertSame('ORDR-20260827-0001', Order::nextOrderNumber());
    }

    private function placeOrder(string $number): Order
    {
        return Order::create([
            'order_number' => $number,
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total_amount' => 100,
        ]);
    }
}
