<?php

namespace App\Services;

use App\Enums\StockMovementReason;
use App\Models\Order;
use App\Models\RawMaterial;
use App\Models\Texture;
use Illuminate\Support\Facades\Auth;

/**
 * Every stock movement an order causes, in one place.
 *
 * Product stock moves at checkout (down) and at cancellation (up). Raw
 * materials and textures move at approval (down) and at cancellation (up),
 * because that's the point the shop commits to making the thing.
 *
 * Quantities are aggregated per material and per texture before being applied:
 * two lines of different products can draw on the same material, and the
 * shortage check is only meaningful against the combined figure.
 *
 * Raw materials go through RawMaterialStockService so an approval lands in the
 * same ledger — and the same `units_consumed` counter — as usage recorded by
 * hand. Textures have no ledger of their own yet and still move directly.
 */
class OrderStockService
{
    public function __construct(private RawMaterialStockService $materialStock)
    {
    }

    /**
     * What this order consumes on approval.
     *
     * @return array{materials: array<int, array{model: RawMaterial, quantity: float}>, textures: array<int, array{model: Texture, quantity: float}>}
     */
    public function requirements(Order $order): array
    {
        $order->loadMissing(['orderItems.product.rawMaterials', 'orderItems.customDesign']);

        $materials = [];
        $textures = [];

        foreach ($order->orderItems as $item) {
            if (! $item->product) {
                continue;
            }

            $quantity = (int) $item->quantity;

            foreach ($item->product->rawMaterials as $material) {
                $id = $material->raw_material_id;
                $needed = (float) $material->pivot->quantity_required * $quantity;

                $materials[$id]['model'] ??= $material;
                $materials[$id]['quantity'] = ($materials[$id]['quantity'] ?? 0) + $needed;
            }

            $texture = $item->customDesign?->texture();
            if ($texture) {
                $id = $texture->texture_id;

                $textures[$id]['model'] ??= $texture;
                $textures[$id]['quantity'] = ($textures[$id]['quantity'] ?? 0) + $quantity;
            }
        }

        return ['materials' => $materials, 'textures' => $textures];
    }

    /**
     * Anything this order needs more of than the shop holds, described for a
     * human. An empty array means approval is safe.
     *
     * @return array<int, string>
     */
    public function shortages(Order $order): array
    {
        $requirements = $this->requirements($order);
        $shortages = [];

        foreach ($requirements['materials'] as $entry) {
            $available = (float) $entry['model']->stock_quantity;
            if ($entry['quantity'] > $available) {
                $shortages[] = sprintf(
                    '%s (needs %s %s, %s in stock)',
                    $entry['model']->name,
                    $this->number($entry['quantity']),
                    $entry['model']->unit,
                    $this->number($available)
                );
            }
        }

        foreach ($requirements['textures'] as $entry) {
            $available = (float) $entry['model']->stock_quantity;
            if ($entry['quantity'] > $available) {
                $shortages[] = sprintf(
                    '%s (needs %s, %s in stock)',
                    $entry['model']->name,
                    $this->number($entry['quantity']),
                    $this->number($available)
                );
            }
        }

        return $shortages;
    }

    /**
     * Draw down materials and textures. Call this on approval only.
     */
    public function consume(Order $order): void
    {
        $requirements = $this->requirements($order);

        foreach ($requirements['materials'] as $entry) {
            $this->materialStock->record($entry['model'], StockMovementReason::Consumed, $entry['quantity'], [
                'user_id' => Auth::id(),
                'order_id' => $order->order_id,
                'note' => "Approved order {$order->order_number}",
            ]);
        }

        foreach ($requirements['textures'] as $entry) {
            $entry['model']->decrement('stock_quantity', $entry['quantity']);
        }
    }

    /**
     * Put materials and textures back. Call this only for an order that was
     * approved — a rejected order never consumed them.
     *
     * Materials are returned by reversing the ledger rows the approval wrote,
     * not by re-reading the bill of materials: a product's BOM can be edited
     * between approval and cancellation, and giving back a quantity the order
     * never took would invent stock.
     */
    public function restore(Order $order): void
    {
        $this->materialStock->reverseForOrder($order->order_id, [
            'user_id' => Auth::id(),
            'note' => "Cancelled order {$order->order_number}",
        ]);

        foreach ($this->requirements($order)['textures'] as $entry) {
            $entry['model']->increment('stock_quantity', $entry['quantity']);
        }
    }

    /**
     * Return the finished-goods stock that checkout took.
     */
    public function returnProducts(Order $order): void
    {
        $order->loadMissing('orderItems.product');

        foreach ($order->orderItems as $item) {
            $item->product?->increment('stock', $item->quantity);
        }
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
