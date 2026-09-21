<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Delivery receipts from UniSMS.
 *
 * A 201 from the send endpoint only means UniSMS queued the message. Whether
 * the network actually delivered it comes back here, seconds to minutes later,
 * as 'message.sent' or 'message.failed'.
 *
 * The receipt carries a reference id and nothing that identifies the user, so
 * it is written to the log next to the "SMS (unisms) accepted … Reference:"
 * line from SmsService. Match on that id to see whether a particular OTP
 * landed.
 *
 * This route is public — it has to be, UniSMS cannot log in — so the shared
 * secret is the only thing standing in front of it.
 */
class UniSmsWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $expected = (string) config('sms.drivers.unisms.webhook_secret', '');

        if ($expected === '') {
            // Refusing beats accepting anything at all from an open endpoint.
            Log::error('UniSMS webhook: UNISMS_WEBHOOK_SECRET is not set, so the receipt was rejected.');

            return response()->json(['message' => 'Webhook is not configured.'], 503);
        }

        // hash_equals, not ===: a plain comparison returns early on the first
        // wrong byte, which leaks the secret to anyone willing to time it.
        if (! hash_equals($expected, (string) $request->header('webhook-secret-key', ''))) {
            Log::warning('UniSMS webhook: rejected a receipt with a bad or missing secret key from ' . $request->ip() . '.');

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = (string) $request->input('event', 'unknown');
        $reference = (string) $request->input('message.reference_id', $request->input('id', 'unknown'));
        $recipient = (string) $request->input('message.recipient', 'unknown');
        $status = (string) $request->input('message.status', 'unknown');

        if ($event === 'message.failed') {
            $reason = (string) ($request->input('message.fail_reason') ?? 'no reason given');

            Log::error("UniSMS {$event} for {$recipient} (reference {$reference}): {$reason}");
        } else {
            Log::info("UniSMS {$event} for {$recipient} (reference {$reference}), status {$status}.");
        }

        // UniSMS only needs a 200; anything else and it treats the receipt as
        // undelivered and sends it again.
        return response()->json(['received' => true]);
    }
}
