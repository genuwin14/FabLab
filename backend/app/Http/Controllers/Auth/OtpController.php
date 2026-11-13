<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        // A code lives ten minutes from when it was sent (AuthController::register
        // and resend() below). With no expiry on record (a code from before this
        // rule, or another browser) it counts as expired, and Resend Code
        // issues a fresh one.
        $expires = session('phone_verification_expires');

        if (! $expires || now()->greaterThan($expires)) {
            return back()->withErrors(['otp' => 'This code has expired. Tap Resend Code to get a new one.'])
                ->with('verification_mode', $request->verification_mode);
        }

        if ($user->phone_verification_code === $request->otp) {
            $user->phone_verification_code = null;
            session()->forget('phone_verification_expires');

            // Verify the channel the code actually went to, and only that one.
            // Access is granted on either (see AuthController::authenticated),
            // so an email-verified account still gets in — but its phone number
            // is not passed off as confirmed.
            if ($request->input('verification_mode') === 'email') {
                $user->email_verified_at = now();
            } else {
                $user->phone_verified = true;
            }

            $user->save();

            return redirect()->route($user->homeRoute());
        }

        return back()->withErrors(['otp' => 'The provided code is invalid.'])
            ->with('verification_mode', $request->verification_mode);
    }

    public function resend(Request $request, \App\Services\SmsService $smsService)
    {
        $user = $request->user();
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $mode = $request->input('verification_mode', 'sms');

        $user->update([
            'phone_verification_code' => $otp,
        ]);

        // A fresh code gets a fresh ten minutes; the wording is the one UniSMS
        // requires (see AuthController::register).
        session(['phone_verification_expires' => now()->addMinutes(10)]);

        $message = "Your FABLAB OTP code is {$otp} for account verification. Please do not share. Expires in 10 minutes.";

        if ($mode === 'email') {
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($user) {
                $mail->to($user->email)
                    ->subject('FABLAB Verification Code');
            });
        } else {
            $smsService->send($user->contact_number, $message);
        }

        return redirect()->route('verify.code')
            ->with('status', 'verification-code-sent')
            ->with('verification_mode', $mode);
    }
}
