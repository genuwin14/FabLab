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

        return redirect('/');
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
}
