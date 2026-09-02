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
     * e.g. routeFor($user, 'orders.index') => 'admin.orders.index' or 'staff.orders.index'.
     */
    public static function routeFor($notifiable, string $name, $parameters = []): string
    {
        $prefix = ($notifiable->role ?? 'staff') === 'admin' ? 'admin' : 'staff';

        return route("{$prefix}.{$name}", $parameters);
    }
}
