<?php

namespace App\Http\Middleware;

use App\Support\PhoneVerification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keep an account that has verified nothing out of the app.
 *
 * Signing up signs the customer in straight away, because entering the code
 * needs the session, and signing in to an unverified account does the same
 * so the verification prompt can follow. Nothing stopped either from typing
 * a page's address and skipping the code entirely. Every role's pages now
 * need the phone or the email verified first; the code page, sign-out and
 * the profile prompt stay reachable, since they are how the account gets
 * verified.
 */
class EnsureAccountVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || PhoneVerification::hasVerifiedChannel($user)) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $request->isMethod('GET')) {
            abort(Response::HTTP_FORBIDDEN, 'Verify your account first: enter the code we sent you.');
        }

        return redirect()->route('verify.code')
            ->with('error', 'Verify your account to continue. Enter the code we sent you, or tap Resend Code.');
    }
}
