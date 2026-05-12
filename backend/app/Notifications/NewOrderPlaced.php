<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Notifier;
use Illuminate\Notifications\Notification;

class NewOrderPlaced extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $customer = $this->order->user->fullname ?? 'A customer';

        return [
            'category' => 'order',
            'icon' => 'bi-cart-plus',
            'title' => "New order {$this->order->order_number}",
            'body' => "{$customer} placed an order worth ₱" . number_format((float) $this->order->total_amount, 2) . '.',
            'url' => Notifier::routeFor($notifiable, 'orders.index'),
        ];
    }
}
