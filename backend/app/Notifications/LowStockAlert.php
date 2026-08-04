<?php

namespace App\Notifications;

use App\Support\Notifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    /** @param Model $item a product, raw material or texture */
    public function __construct(public Model $item) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $stock = rtrim(rtrim(number_format($this->item->currentStock(), 2, '.', ''), '0'), '.');
        $threshold = (float) $this->item->stockThreshold();
        $threshold = rtrim(rtrim(number_format($threshold, 2, '.', ''), '0'), '.');

        return [
            'category' => 'stock',
            'icon' => 'bi-exclamation-triangle',
            'title' => "Low stock: {$this->item->name}",
            'body' => "Only {$stock} {$this->item->stockUnit()} left (threshold {$threshold}).",
            'url' => Notifier::routeFor($notifiable, 'inventory.index'),
            'item_type' => $this->item->stockItemType(),
            'item_id' => $this->item->getKey(),
        ];
    }
}
