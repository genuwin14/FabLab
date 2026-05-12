<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NewCustomerRegistered extends Notification
{
    public function __construct(public User $customer) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = ($notifiable->role ?? 'staff') === 'admin'
            ? route('admin.users.index')
            : route('staff.dashboard');

        return [
            'category' => 'user',
            'icon' => 'bi-person-plus',
            'title' => 'New customer registered',
            'body' => ($this->customer->fullname ?: 'A new customer') . " ({$this->customer->email}) just signed up.",
            'url' => $url,
        ];
    }
}
