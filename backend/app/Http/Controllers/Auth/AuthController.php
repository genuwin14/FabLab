<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            // Check if user is active
            if (Auth::user()->status === 'disabled') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account has been disabled. Please contact support.',
                ]);
            }

            $request->session()->regenerate();

            return $this->authenticated($request, Auth::user());
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function register(Request $request, \App\Services\SmsService $smsService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', 'min:8'],
            'verification_mode' => ['required', 'in:sms,email'],
        ]);

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            if ($existingUser->phone_verified) {
                // Email belongs to a verified user -> throw error
                throw ValidationException::withMessages([
                    'email' => ['The email has already been taken.'],
                ]);
            } else {
                // Email exists but unverified -> Update details and resend OTP
                $user = $existingUser;
                $user->update([
                    'fullname' => $validated['name'],
                    'contact_number' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'customer',
                    'phone_verification_code' => $otp,
                    // Keep phone_verified as false
                ]);
            }
        } else {
            // New User
            $user = User::create([
                'fullname' => $validated['name'],
                'email' => $validated['email'],
                'contact_number' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'customer',
                'phone_verification_code' => $otp,
                'phone_verified' => false,
            ]);

            \App\Support\Notifier::staffAndAdmins(new \App\Notifications\NewCustomerRegistered($user));
        }

        Auth::login($user);

        $message = "Your FABLAB verification code is: {$otp}";

        if ($validated['verification_mode'] === 'email') {
            // Send via Email
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($user) {
                $mail->to($user->email)
                    ->subject('FABLAB Account Verification');
            });
        } else {
            // Send via SMS
            $smsService->send($validated['phone'], $message);
        }

        // Redirect to OTP verification page
        return redirect()->route('verify.code')->with('verification_mode', $validated['verification_mode']);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Ask a Google-created customer for the one detail Google doesn't provide.
     * Skippable, so it never blocks anyone from the shop.
     */
    public function showCompleteProfile()
    {
        if (filled(auth()->user()->contact_number)) {
            return redirect()->route(auth()->user()->homeRoute());
        }

        return view('auth.complete-profile');
    }

    public function storeCompleteProfile(Request $request)
    {
        $validated = $request->validate([
            'contact_number' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $user->contact_number = $validated['contact_number'];
        $user->save();

        return redirect()->route($user->homeRoute())
            ->with('success', 'Thanks — we can reach you about your orders now.');
    }

    /**
     * Where a successful sign-in lands.
     *
     * An account that has verified neither channel gets the verification modal
     * over the login page instead of a dashboard. It stays signed in, because
     * the verification request that follows needs the session.
     */
    protected function authenticated(Request $request, $user)
    {
        if (! $user->phone_verified && ! $user->email_verified_at) {
            return view('auth.login', ['showVerificationModal' => true]);
        }

        return redirect()->intended(route($user->homeRoute()));
    }

    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed.');
        }

        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            // Check if user is active
            if ($existingUser->status === 'disabled') {
                return redirect()->route('login')->with('error', 'Your account has been disabled. Please contact support.');
            }

            // Login existing user
            Auth::login($existingUser);

            // If no photo, use Google Avatar URL
            if (!$existingUser->photo && $googleUser->getAvatar()) {
                $existingUser->photo = $googleUser->getAvatar();
                $existingUser->save();
            }

            // Still no way to reach them about an order — ask again (skippable).
            if ($existingUser->role === 'customer' && blank($existingUser->contact_number)) {
                return redirect()->route('profile.complete');
            }

            return $this->authenticated(request(), $existingUser);
        } else {
            // Create new user
            $user = User::create([
                'fullname' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => null,
                'role' => 'customer',
                'email_verified_at' => now(),
                'phone_verified' => true,
                'contact_number' => '',
                'photo' => $googleUser->getAvatar(), // Store URL directly
            ]);

            \App\Support\Notifier::staffAndAdmins(new \App\Notifications\NewCustomerRegistered($user));

            Auth::login($user);

            // Google gives us a verified email but never a phone number.
            return redirect()->route('profile.complete');
        }
    }

    // Forgot Password Methods

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function checkUserForReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find an account with that email address.'],
            ]);
        }

        // Mask Email
        $emailParts = explode('@', $user->email);
        $maskedEmail = substr($emailParts[0], 0, 2) . '***' . substr($emailParts[0], -1) . '@' . $emailParts[1];

        // Mask Phone
        $maskedPhone = 'Not available';
        if ($user->contact_number) {
            $maskedPhone = substr($user->contact_number, 0, 4) . '****' . substr($user->contact_number, -3);
        }

        return view('auth.forgot-password', [
            'showMethodSelection' => true,
            'email' => $user->email,
            'maskedEmail' => $maskedEmail,
            'maskedPhone' => $maskedPhone,
        ]);
    }

    public function sendResetCode(Request $request, \App\Services\SmsService $smsService)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_mode' => 'required|in:sms,email',
        ]);

        $user = User::where('email', $request->email)->first();
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // The code lives in the session rather than a password_resets row —
        // it's single-device by design and expires with the window below.
        session([
            'password_reset_otp' => $otp,
            'password_reset_email' => $user->email,
            'password_reset_expires' => now()->addMinutes(10),
        ]);

        $message = "Your FABLAB password reset code is: {$otp}";

        if ($request->verification_mode === 'email') {
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($user) {
                $mail->to($user->email)
                    ->subject('FABLAB Password Reset Code');
            });
        } else {
            if (!$user->contact_number) {
                return back()->withErrors(['email' => 'User does not have a phone number linked.']);
            }
            $smsService->send($user->contact_number, $message);
        }

        return redirect()->route('password.verify.show');
    }

    public function showResetVerificationForm()
    {
        if (!session('password_reset_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.verify-reset-code'); // We might need to create this or reuse verify-code
    }
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $sessionOtp = session('password_reset_otp');
        $sessionEmail = session('password_reset_email');
        $expires = session('password_reset_expires');

        if (!$sessionOtp || !$sessionEmail || now()->greaterThan($expires)) {
            return redirect()->route('password.request')->withErrors(['email' => 'Session expired. Please try again.']);
        }

        if ($request->otp !== $sessionOtp) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        // The code checks out. Hold a one-time token so the reset form knows
        // this email has been proven, and send them there.
        session([
            'password_reset_token' => \Illuminate\Support\Str::random(60),
            'password_reset_verified_email' => $sessionEmail,
        ]);

        return redirect()->route('password.reset.form');
    }
    public function showResetPasswordForm()
    {
        if (!session('password_reset_token')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $token = session('password_reset_token');
        $email = session('password_reset_verified_email');

        if (!$token || !$email) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid session. Please try again.']);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Clear session
        session()->forget(['password_reset_otp', 'password_reset_email', 'password_reset_expires', 'password_reset_token', 'password_reset_verified_email']);

        return redirect()->route('login')->with('status', 'Password has been reset successfully. Please login.');
    }
}
