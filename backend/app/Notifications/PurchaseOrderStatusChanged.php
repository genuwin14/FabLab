<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Support\Notifier;
use Illuminate\Notifications\Notification;

class PurchaseOrderStatusChanged extends Notification
{
    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $supplier = $this->purchaseOrder->supplier->name ?? 'supplier';

        return [
            'category' => 'purchase',
            'icon' => 'bi-truck',
            'title' => "PO {$this->purchaseOrder->po_number}",
            'body' => ucfirst($supplier) . " — status changed to " . ucfirst($this->newStatus) . '.',
            'url' => Notifier::routeFor($notifiable, 'purchase.show', $this->purchaseOrder->purchase_order_id),
            'purchase_order_id' => $this->purchaseOrder->purchase_order_id,
        ];
    }
}
