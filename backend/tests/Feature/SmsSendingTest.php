<?php

namespace Tests\Feature;

use App\Services\SmsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * SmsService used to talk to PhilSMS only, and read its settings through env()
 * — which returns null once config is cached, so it would have failed silently
 * in production. Settings now come from config/sms.php and the driver is
 * switchable, so a phone running MacroDroid can send instead of a paid gateway.
 */
class SmsSendingTest extends TestCase
{
    private const WEBHOOK = 'https://trigger.macrodroid.com/abc-123/sms';

    private function macroDroid(array $overrides = []): SmsService
    {
        config([
            'sms.driver' => 'macrodroid',
            'sms.country_code' => '63',
            'sms.drivers.macrodroid' => array_merge([
                'url' => self::WEBHOOK,
                'number_param' => 'sms_number',
                'message_param' => 'sms_text',
                'number_format' => 'e164',
                'timeout' => 10,
            ], $overrides),
        ]);

        return new SmsService();
    }

    public function test_it_triggers_the_macrodroid_webhook(): void
    {
        Http::fake([self::WEBHOOK . '*' => Http::response('OK', 200)]);

        $this->assertTrue($this->macroDroid()->send('09171234567', 'Your FabLab code is 123456'));

        Http::assertSent(function (Request $request) {
            return str_starts_with($request->url(), self::WEBHOOK)
                && $request['sms_number'] === '+639171234567'
                && $request['sms_text'] === 'Your FabLab code is 123456';
        });
    }

    public function test_the_message_is_url_encoded(): void
    {
        Http::fake([self::WEBHOOK . '*' => Http::response('OK', 200)]);

        // Spaces, & and + in a message would otherwise break the query string.
        $this->macroDroid()->send('09171234567', 'Order #12 approved & ready + collect');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'Order+%2312+approved+%26+ready+%2B+collect')
                || str_contains($request->url(), 'Order%20%2312%20approved%20%26%20ready%20%2B%20collect');
        });
    }

    #[DataProvider('numberFormats')]
    public function test_it_writes_the_number_the_way_the_handset_wants(string $format, string $given, string $expected): void
    {
        Http::fake([self::WEBHOOK . '*' => Http::response('OK', 200)]);

        $this->macroDroid(['number_format' => $format])->send($given, 'hi');

        Http::assertSent(fn (Request $request) => $request['sms_number'] === $expected);
    }

    public static function numberFormats(): array
    {
        return [
            'e164 from local' => ['e164', '09171234567', '+639171234567'],
            'e164 from 63' => ['e164', '639171234567', '+639171234567'],
            'e164 already plus' => ['e164', '+639171234567', '+639171234567'],
            'e164 strips spaces' => ['e164', '0917 123 4567', '+639171234567'],
            'local from 63' => ['local', '639171234567', '09171234567'],
            'local stays local' => ['local', '09171234567', '09171234567'],
            'raw is untouched' => ['raw', '0917-123-4567', '0917-123-4567'],
        ];
    }

    public function test_a_dead_phone_does_not_blow_up_registration(): void
    {
        // Connection refused — MacroDroid is closed, or the phone is off.
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection refused'));

        // Must be false, not an exception: registration calls this inline.
        $this->assertFalse($this->macroDroid()->send('09171234567', 'hi'));
    }

    public function test_a_non_200_from_macrodroid_is_a_failure(): void
    {
        Http::fake([self::WEBHOOK . '*' => Http::response('Not found', 404)]);

        $this->assertFalse($this->macroDroid()->send('09171234567', 'hi'));
    }

    public function test_it_refuses_to_call_an_unconfigured_webhook(): void
    {
        Http::fake();

        $this->assertFalse($this->macroDroid(['url' => ''])->send('09171234567', 'hi'));

        Http::assertNothingSent();
    }

    public function test_an_empty_recipient_is_rejected_before_any_call(): void
    {
        Http::fake();

        $this->assertFalse($this->macroDroid()->send('', 'hi'));
        $this->assertFalse($this->macroDroid()->send('09171234567', '   '));

        Http::assertNothingSent();
    }

    public function test_the_log_driver_sends_nothing(): void
    {
        Http::fake();
        Log::spy();

        config(['sms.driver' => 'log', 'sms.drivers.log' => ['channel' => null]]);

        $this->assertTrue((new SmsService())->send('09171234567', 'Code 4321'));

        Http::assertNothingSent();
        Log::shouldHaveReceived('info')->withArgs(fn ($m) => str_contains($m, 'Code 4321'));
    }

    public function test_philsms_still_works_and_uses_the_bare_country_code(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);

        config([
            'sms.driver' => 'philsms',
            'sms.country_code' => '63',
            'sms.drivers.philsms' => [
                'url' => 'https://dashboard.philsms.com/api/v3',
                'token' => 'test-token',
                'sender' => 'FabLabs',
                'timeout' => 15,
            ],
        ]);

        $this->assertTrue((new SmsService())->send('09171234567', 'hello'));

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://dashboard.philsms.com/api/v3/sms/send'
                // No plus for the gateway, unlike the handset.
                && $request['recipient'] === '639171234567'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function test_an_unknown_driver_fails_loudly_rather_than_pretending(): void
    {
        Http::fake();

        config(['sms.driver' => 'carrier-pigeon']);

        $this->assertFalse((new SmsService())->send('09171234567', 'hi'));
        Http::assertNothingSent();
    }
}
