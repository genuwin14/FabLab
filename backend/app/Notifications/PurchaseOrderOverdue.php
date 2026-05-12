<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Support\Notifier;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class PurchaseOrderOverdue extends Notification
{
    public function __construct(public PurchaseOrder $purchaseOrder) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $expected = $this->purchaseOrder->expected_delivery_date
            ? Carbon::parse($this->purchaseOrder->expected_delivery_date)->format('M j, Y')
            : 'an earlier date';

        return [
            'category' => 'purchase',
            'icon' => 'bi-clock-history',
            'title' => "PO {$this->purchaseOrder->po_number} overdue",
            'body' => "Expected {$expected} — still " . ucfirst($this->purchaseOrder->status) . '.',
            'url' => Notifier::routeFor($notifiable, 'purchase.show', $this->purchaseOrder->purchase_order_id),
            'purchase_order_id' => $this->purchaseOrder->purchase_order_id,
        ];
    }
}
