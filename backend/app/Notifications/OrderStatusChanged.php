<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Concerns\FollowsUpdateChoice;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        // Approval already lands in the inbox as the transaction-slip email,
        // so mailing here too would tell the customer the same thing twice.
        // Every other step emails as well, and every step texts, for the
        // customers who ticked those at checkout.
        return $this->customerChannels($notifiable, mail: $this->newStatus !== 'approved');
    }

    public function toSms(object $notifiable): string
    {
        $order = $this->order;
        $number = $order->order_number;

        $text = match ($this->newStatus) {
            'approved' => $order->isPurchaseRequest()
                ? "Order {$number} is approved at {$this->smsAmount($order->total_amount)}. Production starts once the Notice of Award is in."
                : "Order {$number} is approved at {$this->smsAmount($order->total_amount)}. Open View Slip in My Orders and pay at PAXS.",
            'processing' => "Order {$number} is now in production. We will text you when it moves to the next step.",
            'ready_for_pickup' => "Order {$number} is ready for pickup at the CSPC FabLab. "
                . (($receipt = $this->smsReceipt()) ? "Bring receipt no. {$receipt}." : 'Bring your official receipt.'),
            'for_delivery' => "Order {$number} has been released for delivery.",
            'completed' => "Order {$number} is completed. Thank you for choosing CSPC FabLab!",
            'cancelled' => "Order {$number} was cancelled. The reason is on the order in My Orders.",
            default => "Order {$number} is now " . Order::statusLabel($this->newStatus) . '.',
        };

        return 'FABLAB: ' . $text;
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
        $body = "Your order is now {$label}.";

        // Approval is the shop confirming the price the system estimated.
        if ($this->newStatus === 'approved') {
            $body .= ' FabLab confirmed the total at ₱' . number_format((float) $this->order->total_amount, 2)
                . ($this->order->priceWasAdjusted()
                    ? ' (your estimate was ₱' . number_format($this->order->estimatedTotal(), 2) . ').'
                    : '.');
        }

        return [
            'category' => 'order',
            'icon' => 'bi-bag-check',
            'title' => "Order {$this->order->order_number}",
            'body' => $body,
            // Fragment opens this order's details drawer on arrival, rather
            // than leaving the customer to find it in the list.
            'url' => route('customer.orders.index', [], false) . '#order-' . $this->order->order_id,
            'order_id' => $this->order->order_id,
        ];
    }
}
