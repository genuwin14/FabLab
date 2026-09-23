<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The admin has recorded the customer's payment: the receipt number is on
 * the order and production can start. The status does not change, so this
 * is its own notification rather than an OrderStatusChanged — and it is
 * worth an email, because the receipt number is what the customer has to
 * bring to collect the order.
 */
class PaymentRecorded extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order {$this->order->order_number} - Payment Received")
            ->view('emails.orders.payment', ['order' => $this->order]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'order',
            'icon' => 'bi-receipt',
            'title' => "Order {$this->order->order_number}",
            'body' => "Payment received — receipt number {$this->order->payment_reference}. Bring it to collect your order.",
            'url' => route('customer.orders.index', [], false) . '#order-' . $this->order->order_id,
            'order_id' => $this->order->order_id,
        ];
    }
}
