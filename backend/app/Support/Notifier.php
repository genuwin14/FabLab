<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\Notification;

class Notifier
{
    /**
     * Send a notification to every active staff and admin user.
     */
    public static function staffAndAdmins(Notification $notification): void
    {
        User::query()
            ->whereIn('role', ['staff', 'admin'])
            ->where('status', 'active')
            ->where('notifications_enabled', true)
            ->get()
            ->each
            ->notify($notification);
    }

    /**
     * Send a notification to one customer, respecting their notification
     * toggle. Some of these carry a mail channel that talks to SMTP inline,
     * and a mail hiccup must not turn an already-saved status change into an
     * error page — the database channel runs first, so the in-app bell
     * survives even when the email doesn't go out.
     */
    public static function customer(?User $user, Notification $notification): void
    {
        if (! $user || ! $user->notifications_enabled) {
            return;
        }

        try {
            $user->notify($notification);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify customer: ' . $e->getMessage());
        }
    }

    /**
     * Resolve a role-prefixed route name for the given notifiable user,
     * e.g. routeFor($user, 'orders.index') => '/admin/orders' or '/staff/orders'.
     *
     * A path, never a whole URL: see link().
     */
    public static function routeFor($notifiable, string $name, $parameters = []): string
    {
        $prefix = ($notifiable->role ?? 'staff') === 'admin' ? 'admin' : 'staff';

        return route("{$prefix}.{$name}", $parameters, false);
    }

    /**
     * Where a stored notification link sends its reader: a path, to be
     * followed on whatever host they are signed in on now.
     *
     * Links used to be stored whole, scheme and host included, taken from
     * the request that raised the notification — a customer's checkout, or
     * APP_URL for a console command. Anyone reading on another host
     * (localhost against 127.0.0.1, https against http) followed the link off
     * the site they were signed in to and landed on its login page. Links are
     * stored as bare paths now; this rescues the ones stored before, and
     * turns anything that is not a path on this site into the list.
     */
    public static function link(?string $url): string
    {
        $fallback = route('notifications.index', [], false);
        $parts = blank($url) ? false : parse_url($url);

        if ($parts === false || (isset($parts['scheme']) && ! in_array(strtolower($parts['scheme']), ['http', 'https'], true))) {
            return $fallback;
        }

        $path = $parts['path'] ?? '/';

        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return $fallback;
        }

        // A link stored under a sub-folder install carries the folder, which
        // redirect()->to() puts back on by itself.
        $base = request()->getBaseUrl();
        if ($base !== '' && str_starts_with($path, $base . '/')) {
            $path = substr($path, strlen($base));
        }

        return $path
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }
}
