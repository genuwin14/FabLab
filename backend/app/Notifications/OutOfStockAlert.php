<?php

namespace App\Notifications;

use App\Models\Product;
use App\Support\Notifier;
use Illuminate\Notifications\Notification;

class OutOfStockAlert extends Notification
{
    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'stock',
            'icon' => 'bi-x-octagon',
            'title' => "Out of stock: {$this->product->name}",
            'body' => "{$this->product->name} has run out. Restock to keep it available.",
            'url' => Notifier::routeFor($notifiable, 'inventory.index'),
            'product_id' => $this->product->product_id,
        ];
    }
}
