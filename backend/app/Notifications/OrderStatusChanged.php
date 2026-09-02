<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
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
        // Approval already lands in the inbox as the transaction-slip email,
        // so mailing here too would tell the customer the same thing twice.
        // Every other step gets an email as well as the in-app bell, so the
        // customer stays up to date without opening the system.
        return $this->newStatus === 'approved' ? ['database'] : ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = ucwords(str_replace('_', ' ', $this->newStatus));

        return (new MailMessage)
            ->subject("Order {$this->order->order_number} - {$label}")
            ->view('emails.orders.status', [
                'order' => $this->order,
                'newStatus' => $this->newStatus,
                'label' => $label,
            ]);
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
