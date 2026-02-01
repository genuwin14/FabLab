<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $baseUrl;
    protected $apiToken;
    protected $senderId;

    public function __construct()
    {
        $this->baseUrl = env('PHILSMS_URL', 'https://dashboard.philsms.com/api/v3');
        $this->apiToken = env('PHILSMS_API_TOKEN');
        $this->senderId = env('PHILSMS_SENDER', 'FabLabs');
    }

    /**
     * Send an SMS message.
     *
     * @param string $recipient The phone number (e.g., 09xxxxxxxxx or 639xxxxxxxxx)
     * @param string $message The message content
     * @return bool True if sent successfully, false otherwise
     */
    public function send($recipient, $message)
    {
        // Format recipient: ensure it starts with 63 if it starts with 0
        if (str_starts_with($recipient, '0')) {
            $recipient = '63' . substr($recipient, 1);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/sms/send', [
                        'recipient' => $recipient,
                        'sender_id' => $this->senderId,
                        'type' => 'plain',
                        'message' => $message,
                    ]);

            if ($response->successful()) {
                Log::info("SMS sent to {$recipient}: {$message}");
                return true;
            } else {
                Log::error("Failed to send SMS to {$recipient}. Status: {$response->status()}. Body: {$response->body()}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("SMS Exception: " . $e->getMessage());
            return false;
        }
    }
}
