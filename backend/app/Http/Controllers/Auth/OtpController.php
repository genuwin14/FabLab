<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\PhoneVerification;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function show()
    {
        return view('auth.verify-code');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'verification_mode' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // The channel the code was sent to, as the server recorded it when
        // sending (PhoneVerification::send). The form's own field is only a
        // fallback for a code sent before that was recorded.
        $channel = session('verification_channel')
            ?? ($request->input('verification_mode') === 'email' ? 'email' : 'sms');

        // A code lives ten minutes from when it was sent (AuthController::register
        // and resend() below). With no expiry on record (a code from before this
        // rule, or another browser) it counts as expired, and Resend Code
        // issues a fresh one.
        $expires = session('phone_verification_expires');

        if (! $expires || now()->greaterThan($expires)) {
            return back()->withErrors(['otp' => 'This code has expired. Tap Resend Code to get a new one.'])
                ->with('verification_mode', $channel);
        }

        if ($user->phone_verification_code !== $request->otp) {
            return back()->withErrors(['otp' => 'The provided code is invalid.'])
                ->with('verification_mode', $channel);
        }

        $user->phone_verification_code = null;
        $pending = session('pending_contact_number');
        $message = null;

        // Verify the channel the code actually went to, and only that one.
        // Access is granted on either (see AuthController::authenticated), so
        // an email-verified account still gets in — but its phone number is
        // not passed off as confirmed.
        if ($channel === 'email') {
            $user->email_verified_at = now();
        } else {
            // A new number from the profile replaces the old one only now
            // that it has proven it receives texts.
            if (filled($pending)) {
                $user->contact_number = $pending;
                $message = 'Your new number is verified and saved.';
            }

            $user->phone_verified = true;
            $message ??= 'Your phone number is verified.';
        }

        $user->save();
        session()->forget(['phone_verification_expires', 'verification_channel', 'pending_contact_number']);

        return redirect()->route($user->homeRoute())->with('success', $message ?? 'Your account is verified.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        // "Verify now" in the profile is about the number already on the
        // account, whatever new number an abandoned change left behind.
        if ($request->input('target') === 'current') {
            session()->forget('pending_contact_number');
        }

        // The channel asked for, else the one the last code went to.
        $mode = in_array($request->input('verification_mode'), ['sms', 'email'], true)
            ? $request->input('verification_mode')
            : session('verification_channel', 'sms');

        if ($mode === 'sms' && blank(session('pending_contact_number')) && blank($user->contact_number)) {
            return redirect()->route('verify.code')
                ->withErrors(['otp' => 'There is no phone number on your account to text a code to.']);
        }

        // A code for a new number goes to that number, not the old one.
        $pending = $mode === 'sms' ? session('pending_contact_number') : null;

        PhoneVerification::send($user, $mode, filled($pending) ? $pending : null);

        return redirect()->route('verify.code')
            ->with('success', $mode === 'email' ? 'We emailed you a new code.' : 'We texted you a new code.')
            ->with('verification_mode', $mode);
    }
}
