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
    use Concerns\FollowsUpdateChoice;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return $this->customerChannels($notifiable);
    }

    public function toSms(object $notifiable): string
    {
        $number = $this->order->order_number;

        if ($this->order->isPurchaseRequest() && $this->order->pr_deadline) {
            return "FABLAB: We received order {$number}. File your Purchase Request, then enter the PR number in My Orders by "
                . $this->order->pr_deadline->format('j M Y') . '.';
        }

        return "FABLAB: We received order {$number}, estimated at {$this->smsAmount($this->order->total_amount)}. "
            . 'We will text you again once FabLab reviews it.';
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
            'body' => 'We received your order, estimated at ₱' . number_format((float) $this->order->total_amount, 2)
                . '. FabLab confirms the final price when it reviews the order.',
            // Fragment opens this order's details drawer on arrival, rather
            // than leaving the customer to find it in the list.
            'url' => route('customer.orders.index', [], false) . '#order-' . $this->order->order_id,
            'order_id' => $this->order->order_id,
        ];
    }
}
