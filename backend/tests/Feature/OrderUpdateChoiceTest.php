<?php

namespace Tests\Feature;

use App\Mail\OrderReceipt;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderStatusChanged;
use App\Notifications\PaymentRecorded;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * At checkout the customer ticks how they want to hear about the order:
 * Email, SMS, both or neither. The in-app bell rings regardless, unless
 * they switched it off in Settings, which now governs the bell alone.
 *
 * Texts go through SmsService (UniSMS live), so each must fit one plain
 * 160-character SMS with no link, or it costs extra credits or is refused.
 */
class OrderUpdateChoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->customer = $this->user('customer', 'c@example.test', '09171234567');

        $category = Category::create(['name' => 'Mugs', 'description' => 'x']);

        $this->product = Product::create([
            'name' => 'Mug', 'sku' => 'MG-1', 'category_id' => $category->category_id,
            'price' => 100, 'stock' => 20, 'is_customizable' => false,
        ]);
    }

    private function user(string $role, string $email, string $phone = '09123456789'): User
    {
        return User::create([
            'fullname' => ucfirst($role), 'email' => $email, 'password' => 'password',
            'role' => $role, 'contact_number' => $phone, 'phone_verified' => true,
        ]);
    }

    private function order(array $attributes = []): Order
    {
        $order = Order::create($attributes + [
            'order_number' => 'ORDR-20261125-0001',
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_method' => Order::METHOD_CASH,
            'total_amount' => 200,
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'price' => 100,
        ]);

        return $order->refresh();
    }

    /** Check the one cart line out over AJAX, as the cart page does. */
    private function checkout(array $fields = [])
    {
        Sanctum::actingAs($this->customer);

        $line = CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'quantity' => 1,
            'price' => 100,
        ]);

        return $this->postJson(route('customer.cart.checkout'), $fields + [
            'selected_items' => [$line->lineKey()],
            'payment_method' => Order::METHOD_CASH,
        ]);
    }

    /** Stand in for the gateway and keep what would have been texted. */
    private function recordTexts(): object
    {
        $sms = new class extends SmsService
        {
            /** @var array<int, array{0: string, 1: string}> */
            public array $sent = [];

            public function send($recipient, $message): bool
            {
                $this->sent[] = [(string) $recipient, (string) $message];

                return true;
            }
        };

        $this->app->instance(SmsService::class, $sms);

        return $sms;
    }

    // ---------------------------------------------------------------- checkout

    public static function choices(): array
    {
        return [
            'both' => [1, 1, true, true],
            'email only' => [1, 0, true, false],
            'sms only' => [0, 1, false, true],
            'neither' => [0, 0, false, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('choices')]
    public function test_checkout_keeps_what_the_customer_ticked(int $email, int $sms, bool $wantEmail, bool $wantSms): void
    {
        Notification::fake();

        $this->checkout(['notify_email' => $email, 'notify_sms' => $sms])
            ->assertOk()->assertJson(['success' => true]);

        $order = Order::sole();
        $this->assertSame($wantEmail, $order->notify_email);
        $this->assertSame($wantSms, $order->notify_sms);
    }

    public function test_a_cart_page_from_before_the_choice_keeps_emailing_and_does_not_text(): void
    {
        Notification::fake();

        $this->checkout()->assertOk();

        $order = Order::sole();
        $this->assertTrue($order->notify_email);
        $this->assertFalse($order->notify_sms);
    }

    public function test_texts_need_a_phone_number_on_the_account(): void
    {
        $this->customer->update(['contact_number' => '']);

        $this->checkout(['notify_email' => 1, 'notify_sms' => 1])
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'There is no phone number on your account to text. Add one in your Profile, or untick SMS.']);

        $this->assertSame(0, Order::count());
        $this->assertSame(1, CartItem::count());
    }

    public function test_checking_out_with_sms_ticked_texts_the_confirmation(): void
    {
        $sms = $this->recordTexts();

        $this->checkout(['notify_email' => 0, 'notify_sms' => 1])->assertOk();

        $order = Order::sole();
        $this->assertSame([[
            '09171234567',
            "FABLAB: We received order {$order->order_number}, estimated at PHP 100.00. We will text you again once FabLab reviews it.",
        ]], $sms->sent);
    }

    // --------------------------------------------------------------- cart page

    private function box(string $html, string $id): \DOMElement
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        return $dom->getElementById($id) ?? $this->fail("No #{$id} on the cart page.");
    }

    public function test_a_first_order_starts_with_email_ticked_and_sms_not(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->get(route('customer.cart.index'))->assertOk();

        $this->assertSame(['email' => true, 'sms' => false, 'phone' => '09171234567'], $response->viewData('updates'));
        $this->assertTrue($this->box($response->getContent(), 'notifyEmail')->hasAttribute('checked'));
        $this->assertFalse($this->box($response->getContent(), 'notifySms')->hasAttribute('checked'));
        $response->assertSee('Texts go to 09171234567.');
    }

    public function test_the_boxes_start_as_the_last_order_left_them(): void
    {
        $this->order(['order_number' => 'ORDR-20261124-0001', 'notify_email' => true, 'notify_sms' => false]);
        $this->order(['order_number' => 'ORDR-20261124-0002', 'notify_email' => false, 'notify_sms' => true]);
        Sanctum::actingAs($this->customer);

        $response = $this->get(route('customer.cart.index'))->assertOk();

        $this->assertSame(['email' => false, 'sms' => true, 'phone' => '09171234567'], $response->viewData('updates'));
        $this->assertFalse($this->box($response->getContent(), 'notifyEmail')->hasAttribute('checked'));
        $this->assertTrue($this->box($response->getContent(), 'notifySms')->hasAttribute('checked'));
    }

    public function test_without_a_phone_number_the_sms_box_is_off_and_says_why(): void
    {
        $this->order(['notify_sms' => true]);
        $this->customer->update(['contact_number' => '']);
        Sanctum::actingAs($this->customer);

        $response = $this->get(route('customer.cart.index'))->assertOk();

        $sms = $this->box($response->getContent(), 'notifySms');
        $this->assertTrue($sms->hasAttribute('disabled'));
        $this->assertFalse($sms->hasAttribute('checked'));
        $response->assertSee('There is no phone number on your account.');
    }

    // ---------------------------------------------------------------- channels

    public function test_each_update_takes_only_the_channels_ticked_for_its_order(): void
    {
        foreach (self::choices() as $label => [$email, $sms, $wantEmail, $wantSms]) {
            $order = new Order(['notify_email' => (bool) $email, 'notify_sms' => (bool) $sms]);

            $expected = array_values(array_filter([
                'database',
                $wantSms ? SmsChannel::class : null,
                $wantEmail ? 'mail' : null,
            ]));

            foreach ([
                new OrderPlaced($order),
                new OrderStatusChanged($order, 'paid', 'processing'),
                new PaymentRecorded($order),
            ] as $notification) {
                $this->assertSame($expected, $notification->via($this->customer), $label . ': ' . $notification::class);
            }
        }
    }

    public function test_approval_texts_but_leaves_the_email_to_the_slip(): void
    {
        $order = new Order(['notify_email' => true, 'notify_sms' => true]);

        $this->assertSame(
            ['database', SmsChannel::class],
            (new OrderStatusChanged($order, 'pending', 'approved'))->via($this->customer)
        );
    }

    /**
     * Laravel stops at the first channel that throws. SMTP can; SmsService
     * never does. So the text goes first, and a mail-server hiccup cannot
     * take it down with the email.
     */
    public function test_the_text_goes_out_before_the_email(): void
    {
        $channels = (new OrderPlaced(new Order(['notify_email' => true, 'notify_sms' => true])))->via($this->customer);

        $this->assertLessThan(array_search('mail', $channels, true), array_search(SmsChannel::class, $channels, true));
    }

    public function test_a_number_removed_after_ordering_is_not_texted(): void
    {
        $this->customer->update(['contact_number' => '']);

        $channels = (new OrderPlaced(new Order(['notify_email' => true, 'notify_sms' => true])))->via($this->customer);

        $this->assertSame(['database', 'mail'], $channels);
    }

    public function test_the_settings_switch_turns_off_the_bell_and_nothing_else(): void
    {
        $this->customer->update(['notifications_enabled' => false]);

        $both = new OrderStatusChanged(new Order(['notify_email' => true, 'notify_sms' => true]), 'paid', 'processing');
        $neither = new OrderStatusChanged(new Order(['notify_email' => false, 'notify_sms' => false]), 'paid', 'processing');

        $this->assertSame([SmsChannel::class, 'mail'], $both->via($this->customer));
        $this->assertSame([], $neither->via($this->customer));
    }

    public function test_staff_moving_a_texted_order_on_texts_the_customer(): void
    {
        $sms = $this->recordTexts();
        $order = $this->order(['status' => 'ready_for_pickup', 'notify_email' => false, 'notify_sms' => true, 'payment_reference' => 'OR-1234567']);
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'completed'])
            ->assertSessionHas('success');

        $this->assertSame([[
            '09171234567',
            'FABLAB: Order ORDR-20261125-0001 is completed. Thank you for choosing CSPC FabLab!',
        ]], $sms->sent);
    }

    public function test_an_order_that_did_not_tick_sms_is_never_texted(): void
    {
        $sms = $this->recordTexts();
        $order = $this->order(['status' => 'ready_for_pickup', 'notify_email' => true, 'notify_sms' => false]);
        Sanctum::actingAs($this->user('staff', 's@example.test'));

        $this->post("/staff/orders/{$order->order_id}/update-status", ['status' => 'completed'])
            ->assertSessionHas('success');

        $this->assertSame([], $sms->sent);
    }

    // --------------------------------------------------------------------- slip

    public function test_approval_emails_the_slip_only_when_email_was_ticked(): void
    {
        $sms = $this->recordTexts();
        $emailed = $this->order(['order_number' => 'ORDR-20261125-0001', 'notify_email' => true, 'notify_sms' => false]);
        $texted = $this->order(['order_number' => 'ORDR-20261125-0002', 'notify_email' => false, 'notify_sms' => true]);
        Sanctum::actingAs($this->user('admin', 'a@example.test'));

        $this->post("/admin/orders/{$emailed->order_id}/review", ['status' => 'approved'])->assertSessionHas('success');
        $this->post("/admin/orders/{$texted->order_id}/review", ['status' => 'approved'])->assertSessionHas('success');

        Mail::assertSent(OrderReceipt::class, 1);
        Mail::assertSent(OrderReceipt::class, fn (OrderReceipt $mail) => $mail->order->is($emailed));
        $this->assertSame([[
            '09171234567',
            'FABLAB: Order ORDR-20261125-0002 is approved at PHP 200.00. Open View Slip in My Orders and pay at PAXS.',
        ]], $sms->sent);
    }

    // -------------------------------------------------------------------- texts

    /** GSM 03.38's basic set: anything outside it sends the text as Unicode, 70 characters a part. */
    private const GSM = '@£$¥èéùìòÇØøÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';

    public function test_every_text_fits_one_plain_sms_with_no_link(): void
    {
        // Longer values than the shop will see: a seven-figure total, and
        // the longest receipt number that is ever texted.
        $paxs = new Order([
            'order_number' => 'ORDR-20261125-0001', 'payment_method' => Order::METHOD_CASH,
            'total_amount' => 1234567.89, 'payment_reference' => str_repeat('9', 20),
        ]);
        $pr = new Order([
            'order_number' => 'ORDR-20261125-0001', 'payment_method' => Order::METHOD_PR,
            'total_amount' => 1234567.89, 'pr_deadline' => now()->setDate(2026, 12, 31),
        ]);

        $texts = [
            'placed, PAXS' => (new OrderPlaced($paxs))->toSms($this->customer),
            'placed, PR' => (new OrderPlaced($pr))->toSms($this->customer),
            'payment recorded' => (new PaymentRecorded($paxs))->toSms($this->customer),
            'approved, PR' => (new OrderStatusChanged($pr, 'pending', 'approved'))->toSms($this->customer),
        ];
        foreach (Order::STATUSES as $status) {
            $texts[$status] = (new OrderStatusChanged($paxs, 'pending', $status))->toSms($this->customer);
        }

        foreach ($texts as $what => $text) {
            $this->assertStringStartsWith('FABLAB: ', $text, $what);
            $this->assertStringContainsString('ORDR-20261125-0001', $text, $what);
            $this->assertLessThanOrEqual(160, mb_strlen($text), "{$what}: {$text}");
            $this->assertStringNotContainsStringIgnoringCase('http', $text, $what);
            $this->assertStringNotContainsString('www.', $text, $what);

            foreach (mb_str_split($text) as $character) {
                $this->assertNotFalse(mb_strpos(self::GSM, $character), "{$what}: \"{$character}\" is not plain SMS text");
            }
        }
    }

    public function test_the_texts_say_what_each_step_means(): void
    {
        $order = new Order(['order_number' => 'ORDR-1', 'payment_method' => Order::METHOD_CASH, 'total_amount' => 1250, 'payment_reference' => 'OR-55']);
        $pr = new Order(['order_number' => 'ORDR-2', 'payment_method' => Order::METHOD_PR, 'total_amount' => 1250, 'pr_deadline' => now()->setDate(2026, 12, 2)]);

        $this->assertSame('FABLAB: We received order ORDR-2. File your Purchase Request, then enter the PR number in My Orders by 2 Dec 2026.', (new OrderPlaced($pr))->toSms($this->customer));
        $this->assertSame('FABLAB: Order ORDR-1 is approved at PHP 1,250.00. Open View Slip in My Orders and pay at PAXS.', (new OrderStatusChanged($order, 'pending', 'approved'))->toSms($this->customer));
        $this->assertSame('FABLAB: Order ORDR-2 is approved at PHP 1,250.00. Production starts once the Notice of Award is in.', (new OrderStatusChanged($pr, 'pending', 'approved'))->toSms($this->customer));
        $this->assertSame('FABLAB: Payment received for order ORDR-1, receipt no. OR-55. Show it when you collect the order.', (new PaymentRecorded($order))->toSms($this->customer));
        $this->assertSame('FABLAB: Order ORDR-1 is ready for pickup at the CSPC FabLab. Bring receipt no. OR-55.', (new OrderStatusChanged($order, 'processing', 'ready_for_pickup'))->toSms($this->customer));
        $this->assertSame('FABLAB: Order ORDR-1 was cancelled. The reason is on the order in My Orders.', (new OrderStatusChanged($order, 'approved', 'cancelled'))->toSms($this->customer));
    }

    public function test_a_receipt_number_too_long_to_text_is_left_out_rather_than_cut(): void
    {
        $order = new Order(['order_number' => 'ORDR-1', 'payment_reference' => str_repeat('7', 21)]);

        $paid = (new PaymentRecorded($order))->toSms($this->customer);
        $pickup = (new OrderStatusChanged($order, 'processing', 'ready_for_pickup'))->toSms($this->customer);

        $this->assertSame('FABLAB: Payment received for order ORDR-1. Show your official receipt when you collect the order.', $paid);
        $this->assertSame('FABLAB: Order ORDR-1 is ready for pickup at the CSPC FabLab. Bring your official receipt.', $pickup);
    }

    // ------------------------------------------------------------------ showing

    public function test_the_order_says_how_the_customer_hears_about_it(): void
    {
        $this->assertSame('Email + SMS', (new Order(['notify_email' => true, 'notify_sms' => true]))->updates_via);
        $this->assertSame('Email', (new Order(['notify_email' => true, 'notify_sms' => false]))->updates_via);
        $this->assertSame('SMS', (new Order(['notify_email' => false, 'notify_sms' => true]))->updates_via);
        $this->assertSame('In-app only', (new Order(['notify_email' => false, 'notify_sms' => false]))->updates_via);

        // The admin and staff windows render from the order's JSON.
        $this->assertSame('SMS', json_decode(json_encode($this->order(['notify_email' => false, 'notify_sms' => true])), true)['updates_via']);
    }

    public function test_my_orders_points_to_view_slip_when_the_slip_was_not_emailed(): void
    {
        $this->order(['order_number' => 'ORDR-20261125-0001', 'status' => 'approved', 'notify_email' => true]);
        $this->order(['order_number' => 'ORDR-20261125-0002', 'status' => 'approved', 'notify_email' => false, 'notify_sms' => true]);
        Sanctum::actingAs($this->customer);

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('A transaction slip has been sent to your email.')
            ->assertSee('Open your transaction slip with', false)
            ->assertSee('Updates via');
    }
}
