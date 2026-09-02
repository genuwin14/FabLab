<?php

namespace App\Console\Commands;

use App\Enums\StockMovementReason;
use App\Models\Order;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Models\Texture;
use App\Notifications\LowStockAlert;
use App\Support\Notifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Reset each item's low-stock line to what the previous month says it should be.
 *
 * A hand-set threshold describes the demand somebody remembered on the day they
 * typed it, and it stays that number while the demand moves on. This walks
 * every stock-bearing item — products, raw materials, textures — and sets the
 * line to HALF of what the previous calendar month actually drew: two weeks of
 * observed demand, which against this shop's two-to-ten-day supplier leads is
 * a lead time plus slack to place the order in.
 *
 * Two deliberate asymmetries:
 *
 *   - An item nothing drew on is left alone. A quiet month is not evidence the
 *     line should be zero — zero would switch its alerts off entirely — so a
 *     slow mover keeps whatever line an admin gave it.
 *   - An admin can still edit any threshold by hand at any time; the next
 *     monthly run overwrites it with the demand-based figure. That is the
 *     point of the feature, not a bug in it: the panel asked for the marker to
 *     follow the previous month, and a number nobody maintains is exactly what
 *     this replaces.
 *
 * Scheduled monthly, on the 1st — see bootstrap/app.php.
 */
class RetuneLowStockThresholds extends Command
{
    protected $signature = 'stock:retune-thresholds';

    protected $description = "Reset each item's low-stock threshold to half of what the previous month drew.";

    /**
     * An order counts toward demand once it is approved — that is when stock
     * leaves the shelf. Pending never took anything, cancelled gave it back.
     */
    private const CONSUMING_STATUSES = ['approved', 'processing', 'ready_for_pickup', 'for_delivery', 'completed'];

    public function handle(): int
    {
        $start = now()->subMonthNoOverflow()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $this->info('Retuning low-stock thresholds from ' . $start->format('F Y') . ' demand.');

        $changed = $this->retune(RawMaterial::all(), $this->materialOutflow($start, $end))
            + $this->retune(Product::all(), $this->productOutflow($start, $end))
            + $this->retune(Texture::all(), $this->textureOutflow($start, $end));

        $this->info($changed . ' threshold(s) retuned.');

        return self::SUCCESS;
    }

    /**
     * What each raw material gave up during the window, read off the ledger.
     *
     * Reserved and Consumed both mean "left the shelf for work": an order holds
     * an unreversed Reserved row until production converts it — reversing the
     * reservation and writing a Consumed row — and the usage form writes
     * Consumed directly. Counting unreversed rows of either kind therefore
     * counts each draw once. The one blind spot: an order reserved at the end
     * of one month and converted at the start of the next lands in both
     * months' sums. That overstates demand slightly and errs toward alerting
     * sooner, which is the right side to miss on for a reorder line.
     *
     * Damaged, sponsored and display write-offs stay out: they are one-off
     * events, not demand to plan stock around.
     *
     * @return array<int, float>
     */
    private function materialOutflow(Carbon $start, Carbon $end): array
    {
        return RawMaterialMovement::query()
            ->whereIn('reason', [StockMovementReason::Reserved, StockMovementReason::Consumed])
            ->whereDoesntHave('reversal')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('raw_material_id')
            ->selectRaw('raw_material_id, SUM(quantity) AS outflow')
            ->pluck('outflow', 'raw_material_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * Units of each product ordered during the window, on orders that reached
     * a stock-consuming status.
     *
     * @return array<int, float>
     */
    private function productOutflow(Carbon $start, Carbon $end): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->whereIn('orders.status', self::CONSUMING_STATUSES)
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) AS outflow')
            ->pluck('outflow', 'product_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * Texture draw is one unit per item carrying that finish — the same rule
     * OrderStockService::requirements() reserves by. The finish lives inside
     * each design's recipe rather than in a column, so this walks the window's
     * orders instead of asking SQL.
     *
     * @return array<int, float>
     */
    private function textureOutflow(Carbon $start, Carbon $end): array
    {
        $outflow = [];

        $orders = Order::with('orderItems.customDesign')
            ->whereIn('status', self::CONSUMING_STATUSES)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $texture = $item->customDesign?->texture();

                if ($texture) {
                    $outflow[$texture->texture_id] = ($outflow[$texture->texture_id] ?? 0) + (float) $item->quantity;
                }
            }
        }

        return $outflow;
    }

    /**
     * Apply the demand-based line to every item the month touched.
     *
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $items
     * @param  array<int, float>  $outflow
     */
    private function retune($items, array $outflow): int
    {
        $changed = 0;

        foreach ($items as $item) {
            $drawn = $outflow[$item->getKey()] ?? 0.0;

            if ($drawn <= 0) {
                continue;
            }

            $threshold = (float) ceil($drawn / 2);
            $old = $item->stockThreshold();

            if ($old !== null && abs($old - $threshold) < 0.005) {
                continue;
            }

            $item->low_stock_threshold = $threshold;
            $item->save();
            $changed++;

            $this->line(sprintf(
                '%s "%s": %s -> %s (%s drawn last month)',
                $item->stockItemType(),
                $item->name,
                $old === null ? 'unset' : $old + 0,
                $threshold + 0,
                $drawn + 0,
            ));

            // The observer only speaks when *stock* moves, so an item the new
            // line just overtook would stay silent forever — no later movement
            // can cross a line it is already under. Say it now instead.
            if ($item->currentStock() > 0
                && $item->currentStock() <= $threshold
                && ($old === null || $item->currentStock() > $old)) {
                Notifier::staffAndAdmins(new LowStockAlert($item));
            }
        }

        return $changed;
    }
}
