<?php

namespace App\Notifications;

use App\Support\Notifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class OutOfStockAlert extends Notification
{
    /** @param Model $item a product, raw material or texture */
    public function __construct(public Model $item) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = strtolower($this->item->stockItemType());

        return [
            'category' => 'stock',
            'icon' => 'bi-x-octagon',
            'title' => "Out of stock: {$this->item->name}",
            'body' => "{$this->item->name} has run out. Restock this {$type} to keep it available.",
            'url' => Notifier::routeFor($notifiable, 'inventory.index'),
            'item_type' => $this->item->stockItemType(),
            'item_id' => $this->item->getKey(),
        ];
    }
}
