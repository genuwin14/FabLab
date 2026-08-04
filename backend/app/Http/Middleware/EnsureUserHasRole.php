<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict a route group to one or more roles, e.g. `role:admin` or
 * `role:staff,admin`.
 *
 * Authentication is the caller's job (`auth:sanctum` runs first), so by the
 * time this middleware sees the request there is a user — it only decides
 * whether that user's role is allowed here.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Anything that changes state, and anything expecting JSON, gets a flat
        // refusal. A plain page visit is more likely a stale link or a mistyped
        // URL, so send the user somewhere useful instead of an error page.
        if (! $user || $request->expectsJson() || ! $request->isMethod('GET')) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this area.');
        }

        return redirect()->route($user->homeRoute())
            ->with('error', 'You do not have access to that page.');
    }
}
