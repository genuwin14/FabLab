<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

    protected function authenticated(Request $request, $user)
    {
        // Check if user is verified
        if (!$user->phone_verified && !$user->email_verified_at) {
            // Not verified -> Show Verification Selection Modal on Login Page
            Auth::logout(); // Logout to prevent dashboard access, or keep logged in? 
            // Better to keep logged in BUT return the view immediately so they can't browse away.
            // Actually, if we return the view, they are logged in. 
            // If they refresh, they go to dashboard (unless middleware stops them).
            // For security, let's keep them logged in but ensure they verify.
            // Returning the view 'auth.login' with the modal effectively halts them.

            // Re-login to ensure session is active for the subsequent verification request
            Auth::login($user);

            return view('auth.login', ['showVerificationModal' => true]);
        }

        // Role-based redirection
        switch ($user->role) {
            case 'admin':
                return redirect()->intended(route('admin.dashboard'));
            case 'staff':
                return redirect()->intended(route('staff.dashboard'));
            case 'customer':
            default:
                return redirect()->intended(route('customer.shop'));
        }
    }
    // ... existing methods ...

    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    // ... existing Google Callback ...
    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed.');
        }

        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            // Login existing user
            Auth::login($existingUser);
            return $this->authenticated(request(), $existingUser);
        } else {
            // Create new user
            $user = User::create([
                'fullname' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(\Illuminate\Support\Str::random(16)), // Random password
                'role' => 'customer',
                'email_verified_at' => now(),
                'phone_verified' => true, // Trust Google for now, or set false to force phone add
                // We might need to ask for phone number later since it's missing
                'contact_number' => '', // Placeholder
            ]);

            Auth::login($user);
            return $this->authenticated(request(), $user);
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

        // Store OTP in Session for verification (or DB password_resets table, but following docs "Session")
        // Docs: "a 6-digit code is generated and stored in the Session"
        session([
            'password_reset_otp' => $otp,
            'password_reset_email' => $user->email,
            'password_reset_expires' => now()->addMinutes(10), // 10 min expiry
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

        // OTP Valid!
        // You would typically redirect to a "Reset Password" form here where they enter the new password.
        // For now, let's just log them in or show success (since user asked to "add functionalities" but didn't specify reset pwd form)
        // Actually, the standard flow is -> Show Reset Password Form.

        // Let's create a temporary token to allow them to reset password.
        $token = \Illuminate\Support\Str::random(60);

        // Store token in password_resets table or session
        session(['password_reset_token' => $token, 'password_reset_verified_email' => $sessionEmail]);

        // For this task, I'll redirect to a simple "Reset Password" view, but first let's fix the POST error.
        // Let's assume we redirect to a route called 'password.reset' which shows the form.
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
