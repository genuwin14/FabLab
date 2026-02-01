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

        if ($user->phone_verification_code === $request->otp) {
            $user->phone_verification_code = null;

            if ($request->input('verification_mode') === 'email') {
                $user->email_verified_at = now();
                $user->phone_verified = true; // Allow access but ideally this should be separate
            } else {
                $user->phone_verified = true;
            }

            $user->save();

            // Redirect based on role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'staff':
                    return redirect()->route('staff.dashboard');
                case 'customer':
                default:
                    return redirect()->route('customer.shop');
            }
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

        $message = "Your new FABLAB verification code is: {$otp}";

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
