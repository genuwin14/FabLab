<?php

namespace App\Support;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;

/**
 * Sending a verification code, in one place.
 *
 * The channel the code went to is kept in the session, not taken back from
 * the form, so entering the code confirms the channel that received it: a
 * code texted to a phone can't be submitted as an email confirmation. A code
 * for a new number (a profile change) carries that number in the session too,
 * so the account keeps its verified number until the new one is proven.
 */
class PhoneVerification
{
    /** How long a code lives; the text promises it, OtpController checks it. */
    public const MINUTES = 10;

    /**
     * @param  'sms'|'email'  $channel
     * @param  string|null  $newNumber  a number being proven before it replaces the account's own
     */
    public static function send(User $user, string $channel, ?string $newNumber = null): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill(['phone_verification_code' => $code])->save();

        session([
            'phone_verification_expires' => now()->addMinutes(self::MINUTES),
            'verification_channel' => $channel,
        ]);

        if ($newNumber !== null) {
            session(['pending_contact_number' => $newNumber]);
        } elseif ($channel === 'sms') {
            session()->forget('pending_contact_number');
        }

        // The wording UniSMS requires of a code text: brand, code, purpose, expiry.
        $message = "Your FABLAB OTP code is {$code} for account verification. Please do not share. Expires in " . self::MINUTES . ' minutes.';

        if ($channel === 'email') {
            Mail::raw($message, function ($mail) use ($user) {
                $mail->to($user->email)->subject('FABLAB Verification Code');
            });

            return;
        }

        app(SmsService::class)->send($newNumber ?? $user->contact_number, $message);
    }

    /** Whether this account can already get in: either channel verified. */
    public static function hasVerifiedChannel(User $user): bool
    {
        return (bool) $user->phone_verified || $user->email_verified_at !== null;
    }
}
