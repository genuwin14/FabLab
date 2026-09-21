<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * /api/webhooks/unisms is the one route in this application that anyone on the
 * internet can POST to, so most of what matters here is what it turns away.
 */
class UniSmsWebhookTest extends TestCase
{
    private const URL = '/api/webhooks/unisms';

    private function receipt(string $event = 'message.sent', ?string $failReason = null): array
    {
        return [
            'id' => 'msg_84e8b93b',
            'event' => $event,
            'message' => [
                'status' => $event === 'message.failed' ? 'failed' : 'sent',
                'metadata' => [],
                'content' => 'Your FabLab code is 123456',
                'created' => '2026-03-16T14:32:44Z',
                'recipient' => '+639171234567',
                'reference_id' => 'msg_84e8b93b',
                'fail_reason' => $failReason,
            ],
        ];
    }

    public function test_a_receipt_with_the_right_secret_is_accepted(): void
    {
        config(['sms.drivers.unisms.webhook_secret' => 'test-secret']);
        Log::spy();

        $this->withHeader('webhook-secret-key', 'test-secret')
            ->postJson(self::URL, $this->receipt())
            ->assertOk()
            ->assertJson(['received' => true]);

        Log::shouldHaveReceived('info')
            ->withArgs(fn ($m) => str_contains($m, 'msg_84e8b93b') && str_contains($m, 'message.sent'));
    }

    public function test_a_failure_receipt_is_logged_as_an_error_with_its_reason(): void
    {
        config(['sms.drivers.unisms.webhook_secret' => 'test-secret']);
        Log::spy();

        $this->withHeader('webhook-secret-key', 'test-secret')
            ->postJson(self::URL, $this->receipt('message.failed', 'Subscriber unreachable'))
            ->assertOk();

        Log::shouldHaveReceived('error')
            ->withArgs(fn ($m) => str_contains($m, 'Subscriber unreachable'));
    }

    public function test_a_wrong_or_missing_secret_is_turned_away(): void
    {
        config(['sms.drivers.unisms.webhook_secret' => 'test-secret']);

        $this->withHeader('webhook-secret-key', 'not-the-secret')
            ->postJson(self::URL, $this->receipt())
            ->assertUnauthorized();

        $this->postJson(self::URL, $this->receipt())->assertUnauthorized();
    }

    public function test_an_unconfigured_endpoint_accepts_nothing_at_all(): void
    {
        // Otherwise a blank secret in .env would match a blank header and the
        // endpoint would take anything the internet sent it.
        config(['sms.drivers.unisms.webhook_secret' => '']);

        $this->withHeader('webhook-secret-key', '')
            ->postJson(self::URL, $this->receipt())
            ->assertStatus(503);
    }

    public function test_the_route_is_not_behind_csrf_or_authentication(): void
    {
        // UniSMS cannot log in and has no session to take a token from, so a
        // redirect to /login or a 419 here would mean no receipt ever arrives.
        config(['sms.drivers.unisms.webhook_secret' => 'test-secret']);

        $this->withHeader('webhook-secret-key', 'test-secret')
            ->post(self::URL, $this->receipt())
            ->assertOk();
    }
}
