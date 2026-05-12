<?php

namespace App\Notifications;

use App\Models\Product;
use App\Support\Notifier;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
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
            'icon' => 'bi-exclamation-triangle',
            'title' => "Low stock: {$this->product->name}",
            'body' => "Only {$this->product->stock} {$this->product->unit} left (threshold {$this->product->low_stock_threshold}).",
            'url' => Notifier::routeFor($notifiable, 'inventory.index'),
            'product_id' => $this->product->product_id,
        ];
    }
}
