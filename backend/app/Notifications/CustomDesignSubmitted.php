<?php

namespace App\Notifications;

use App\Models\CustomDesign;
use Illuminate\Notifications\Notification;

class CustomDesignSubmitted extends Notification
{
    public function __construct(public CustomDesign $design) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $customer = $this->design->user->fullname ?? 'A customer';
        $product = $this->design->product->name ?? 'a product';
        $url = ($notifiable->role ?? 'staff') === 'admin' ? route('admin.dashboard') : route('staff.dashboard');

        return [
            'category' => 'design',
            'icon' => 'bi-palette',
            'title' => 'New custom design',
            'body' => "{$customer} created a custom design for {$product}.",
            'url' => $url,
            'custom_design_id' => $this->design->custom_design_id,
        ];
    }
}
