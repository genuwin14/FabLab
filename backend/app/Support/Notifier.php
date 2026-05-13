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
     * Resolve a role-prefixed route name for the given notifiable user,
     * e.g. routeFor($user, 'orders.index') => 'admin.orders.index' or 'staff.orders.index'.
     */
    public static function routeFor($notifiable, string $name, $parameters = []): string
    {
        $prefix = ($notifiable->role ?? 'staff') === 'admin' ? 'admin' : 'staff';

        return route("{$prefix}.{$name}", $parameters);
    }
}
