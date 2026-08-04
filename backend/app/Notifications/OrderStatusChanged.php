<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = ucwords(str_replace('_', ' ', $this->newStatus));

        return [
            'category' => 'order',
            'icon' => 'bi-bag-check',
            'title' => "Order {$this->order->order_number}",
            'body' => "Your order is now {$label}.",
            // Fragment opens this order's details drawer on arrival, rather
            // than leaving the customer to find it in the list.
            'url' => route('customer.orders.index') . '#order-' . $this->order->order_id,
            'order_id' => $this->order->order_id,
        ];
    }
}
