<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The customer's own confirmation that checkout went through — the counterpart
 * of NewOrderPlaced, which tells staff. A Purchase Request order gets its
 * filing instructions and deadline in writing here, since nothing else emails
 * the customer until an admin reviews it.
 */
class OrderPlaced extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order {$this->order->order_number} - Received")
            ->view('emails.orders.placed', ['order' => $this->order]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'order',
            'icon' => 'bi-cart-plus',
            'title' => "Order {$this->order->order_number}",
            'body' => 'We received your order worth ₱' . number_format((float) $this->order->total_amount, 2) . '.',
            // Fragment opens this order's details drawer on arrival, rather
            // than leaving the customer to find it in the list.
            'url' => route('customer.orders.index') . '#order-' . $this->order->order_id,
            'order_id' => $this->order->order_id,
        ];
    }
}
