<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends one SMS, through whichever driver config/sms.php selects.
 *
 * `send()` keeps its original signature and its "never throw, return a bool"
 * contract, because registration and password reset call it inline and must
 * not 500 when a phone is off or a gateway is down.
 *
 * Settings come from config() rather than env(). env() outside a config file
 * returns null once `php artisan config:cache` has run, which would have made
 * SMS fail silently in production only.
 */
class SmsService
{
    private string $driver;

    /** @var array<string, mixed> */
    private array $config;

    private string $countryCode;

    public function __construct()
    {
        $this->driver = (string) config('sms.driver', 'log');
        $this->config = (array) config('sms.drivers.' . $this->driver, []);
        $this->countryCode = (string) config('sms.country_code', '63');
    }

    /**
     * Send an SMS message.
     *
     * @param  string  $recipient  The phone number (e.g. 09xxxxxxxxx or 639xxxxxxxxx)
     * @param  string  $message    The message content
     * @return bool  True if the driver accepted it, false otherwise.
     */
    public function send($recipient, $message): bool
    {
        $recipient = trim((string) $recipient);

        if ($recipient === '' || trim((string) $message) === '') {
            Log::warning('SMS not sent: empty recipient or message.');

            return false;
        }

        try {
            return match ($this->driver) {
                'macrodroid' => $this->sendViaMacroDroid($recipient, $message),
                'philsms' => $this->sendViaPhilSms($recipient, $message),
                'log' => $this->sendViaLog($recipient, $message),
                default => $this->unknownDriver(),
            };
        } catch (\Throwable $e) {
            // Connection refused, DNS failure, timeout — the phone is off, or
            // the laptop is on a different network than the handset.
            Log::error("SMS ({$this->driver}) exception: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Which driver is live. Surfaced by `php artisan sms:test`.
     */
    public function driver(): string
    {
        return $this->driver;
    }

    /**
     * Hand the message to a phone running MacroDroid.
     *
     * The phone is the thing with the SIM, so Laravel only has to reach
     * MacroDroid's trigger URL — an outbound call, which is why this works
     * from localhost while a normal inbound webhook would not.
     *
     * Note this is fire-and-forget: a 200 means MacroDroid accepted the
     * trigger, not that the network delivered the text.
     */
    private function sendViaMacroDroid(string $recipient, string $message): bool
    {
        $url = (string) ($this->config['url'] ?? '');

        if ($url === '') {
            Log::error('SMS (macrodroid): MACRODROID_WEBHOOK_URL is not set.');

            return false;
        }

        $response = Http::timeout((int) ($this->config['timeout'] ?? 10))
            ->get($url, [
                // Http::get URL-encodes these, so spaces and punctuation in the
                // message survive the trip.
                ($this->config['number_param'] ?? 'sms_number') => $this->formatForMacroDroid($recipient),
                ($this->config['message_param'] ?? 'sms_text') => $message,
            ]);

        if ($response->successful()) {
            Log::info("SMS (macrodroid) triggered for {$recipient}.");

            return true;
        }

        Log::error("SMS (macrodroid) failed for {$recipient}. Status: {$response->status()}. Body: {$response->body()}");

        return false;
    }

    private function sendViaPhilSms(string $recipient, string $message): bool
    {
        // PhilSMS wants 639xxxxxxxxx, no plus.
        $recipient = $this->toInternational($recipient);

        $response = Http::timeout((int) ($this->config['timeout'] ?? 15))
            ->withHeaders([
                'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(rtrim((string) ($this->config['url'] ?? ''), '/') . '/sms/send', [
                'recipient' => $recipient,
                'sender_id' => $this->config['sender'] ?? 'FabLabs',
                'type' => 'plain',
                'message' => $message,
            ]);

        if ($response->successful()) {
            Log::info("SMS (philsms) sent to {$recipient}.");

            return true;
        }

        Log::error("SMS (philsms) failed for {$recipient}. Status: {$response->status()}. Body: {$response->body()}");

        return false;
    }

    /**
     * Development driver: record the message instead of spending a text.
     */
    private function sendViaLog(string $recipient, string $message): bool
    {
        $channel = $this->config['channel'] ?? null;

        $line = "SMS (log driver) to {$recipient}: {$message}";

        $channel ? Log::channel($channel)->info($line) : Log::info($line);

        return true;
    }

    private function unknownDriver(): bool
    {
        Log::error("SMS driver \"{$this->driver}\" is not one of: macrodroid, philsms, log.");

        return false;
    }

    /**
     * Android is happiest with E.164, but some prepaid SIMs refuse a +63 that
     * the handset would rather see as 09…, so the format is configurable.
     */
    private function formatForMacroDroid(string $recipient): string
    {
        return match ($this->config['number_format'] ?? 'e164') {
            'raw' => $recipient,
            'local' => $this->toLocal($recipient),
            default => '+' . $this->toInternational($recipient),
        };
    }

    /**
     * 09171234567 / +639171234567 / 639171234567 → 639171234567
     */
    private function toInternational(string $recipient): string
    {
        $digits = preg_replace('/\D/', '', $recipient) ?? '';

        if (str_starts_with($digits, '0')) {
            return $this->countryCode . substr($digits, 1);
        }

        if (str_starts_with($digits, $this->countryCode)) {
            return $digits;
        }

        return $this->countryCode . $digits;
    }

    /**
     * The same number written the way it is dialled locally: 09171234567
     */
    private function toLocal(string $recipient): string
    {
        $digits = preg_replace('/\D/', '', $recipient) ?? '';

        if (str_starts_with($digits, $this->countryCode)) {
            return '0' . substr($digits, strlen($this->countryCode));
        }

        return str_starts_with($digits, '0') ? $digits : '0' . $digits;
    }
}
