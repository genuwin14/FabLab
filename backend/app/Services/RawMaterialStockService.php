<?php

namespace App\Services;

use App\Enums\StockMovementReason;
use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The one way raw material stock is allowed to move.
 *
 * Before this, `stock_quantity` was overwritten by hand in the edit form and
 * the four `units_*` report counters were typed independently, so nothing
 * reconciled: a material could show 40 consumed while stock had never
 * dropped. Every change now goes through here, writes a ledger row, and moves
 * the counter and the stock level together.
 *
 * Callers hand in a context array — `note`, `user_id`, `order_id` — because
 * the same movement can come from a person at a form or from an order being
 * approved.
 */
class RawMaterialStockService
{
    /**
     * Record a usage entry someone picked from the form.
     *
     * @param  array{note?: string|null, user_id?: int|null, order_id?: int|null}  $context
     *
     * @throws RuntimeException when the quantity is not positive or exceeds stock
     */
    public function record(RawMaterial $material, StockMovementReason $reason, float $quantity, array $context = []): RawMaterialMovement
    {
        if (in_array($reason, [StockMovementReason::Correction, StockMovementReason::Reversal], true)) {
            throw new RuntimeException('Use correct() or reverse() for that kind of movement.');
        }

        return DB::transaction(function () use ($material, $reason, $quantity, $context) {
            $material = $this->lock($material);
            $quantity = round($quantity, 2);

            if ($quantity <= 0) {
                throw new RuntimeException('Enter a quantity greater than zero.');
            }

            $available = (float) $material->stock_quantity;

            // Display units stay in stock, but you still can't put more of a
            // material on a shelf than the shop actually holds.
            if ($quantity > $available) {
                throw new RuntimeException(sprintf(
                    'Only %s %s of %s in stock — you tried to record %s.',
                    $this->number($available),
                    $material->unit,
                    $material->name,
                    $this->number($quantity)
                ));
            }

            return $this->write(
                material: $material,
                reason: $reason,
                quantity: $quantity,
                stockDelta: $reason->reducesStock() ? -$quantity : 0.0,
                bucketColumn: $reason->bucketColumn(),
                bucketDelta: $quantity,
                context: $context,
            );
        });
    }

    /**
     * Reconcile against a physical count. The form asks for the counted total
     * rather than a difference, because that's what the person holding the
     * clipboard actually has.
     *
     * @param  array{note?: string|null, user_id?: int|null, order_id?: int|null}  $context
     *
     * @throws RuntimeException when the count is negative or unchanged
     */
    public function correct(RawMaterial $material, float $countedQuantity, array $context = []): RawMaterialMovement
    {
        return DB::transaction(function () use ($material, $countedQuantity, $context) {
            $material = $this->lock($material);
            $countedQuantity = round($countedQuantity, 2);

            if ($countedQuantity < 0) {
                throw new RuntimeException('A counted quantity cannot be negative.');
            }

            $delta = round($countedQuantity - (float) $material->stock_quantity, 2);

            if ($delta == 0.0) {
                throw new RuntimeException('That count already matches the recorded stock — nothing to correct.');
            }

            return $this->write(
                material: $material,
                reason: StockMovementReason::Correction,
                quantity: abs($delta),
                stockDelta: $delta,
                bucketColumn: null,
                bucketDelta: 0.0,
                context: $context,
            );
        });
    }

    /**
     * Undo an earlier entry by writing its opposite. The original row stays
     * put so the log still shows what happened and who fixed it.
     *
     * @param  array{note?: string|null, user_id?: int|null, order_id?: int|null}  $context
     *
     * @throws RuntimeException when the entry cannot be undone
     */
    public function reverse(RawMaterialMovement $movement, array $context = []): RawMaterialMovement
    {
        return DB::transaction(function () use ($movement, $context) {
            $movement->load('reversal');

            if (! $movement->isReversible()) {
                throw new RuntimeException('That entry has already been reversed.');
            }

            $material = $this->lock($movement->rawMaterial);
            $stockDelta = -(float) $movement->stock_delta;

            // Undoing something that *added* stock takes it back out, and the
            // material may have been used up in the meantime.
            if ($stockDelta < 0 && abs($stockDelta) > (float) $material->stock_quantity) {
                throw new RuntimeException(sprintf(
                    'Reversing this would drop %s below zero — only %s %s left.',
                    $material->name,
                    $this->number((float) $material->stock_quantity),
                    $material->unit
                ));
            }

            return $this->write(
                material: $material,
                reason: StockMovementReason::Reversal,
                quantity: (float) $movement->quantity,
                stockDelta: $stockDelta,
                // A reversal has no bucket of its own; it unwinds the one the
                // original entry filled.
                bucketColumn: $movement->reason->bucketColumn(),
                bucketDelta: -(float) $movement->quantity,
                context: $context,
                reverses: $movement->movement_id,
            );
        });
    }

    /**
     * Put back everything an order took. Used when an approved order is
     * cancelled — reversing the exact rows it wrote, rather than guessing the
     * quantities again from a bill of materials that may have changed since.
     *
     * @param  array{note?: string|null, user_id?: int|null}  $context
     */
    public function reverseForOrder(int $orderId, array $context = []): void
    {
        RawMaterialMovement::where('order_id', $orderId)
            ->whereDoesntHave('reversal')
            ->where('reason', '!=', StockMovementReason::Reversal)
            ->with('rawMaterial')
            ->get()
            ->each(fn (RawMaterialMovement $movement) => $this->reverse($movement, $context));
    }

    /**
     * Restate a material's figures from an imported historical report.
     *
     * An old report is a snapshot, not an event: its Consumed column is the
     * running total as of that date, and its Available column is already net of
     * everything beside it. So this does not re-consume anything — it writes the
     * difference between what the report says and what the row currently holds,
     * and leaves stock alone until the final correction sets it to the reported
     * figure. Recording the counters as ordinary usage instead would take the
     * consumed quantity off the shelf a second time.
     *
     * It still goes through the ledger rather than updating the columns, because
     * the counters having exactly one source is the whole point of this class.
     * A restatement that lowers a counter is written as a reversal, the same
     * shape reverse() uses, so the Usage Log never shows a decrease dressed up
     * as consumption.
     *
     * @param  array<string, float>  $counters  keyed on_display|sponsored|damaged|consumed
     * @param  array{note?: string|null, user_id?: int|null}  $context
     * @return list<RawMaterialMovement>  every row written, in the order written
     */
    public function openingBalance(RawMaterial $material, array $counters, float $available, array $context = []): array
    {
        if ($available < 0) {
            throw new RuntimeException('An imported report cannot leave stock below zero.');
        }

        return DB::transaction(function () use ($material, $counters, $available, $context) {
            $material = $this->lock($material);
            $written = [];

            foreach ($counters as $key => $reported) {
                $reason = StockMovementReason::from($key);
                $column = $reason->bucketColumn();
                $delta = round($reported - (float) $material->{$column}, 2);

                if ($delta == 0.0) {
                    continue;
                }

                $written[] = $this->write(
                    material: $material,
                    reason: $delta > 0 ? $reason : StockMovementReason::Reversal,
                    quantity: abs($delta),
                    stockDelta: 0.0,
                    bucketColumn: $column,
                    bucketDelta: $delta,
                    context: $context,
                );
            }

            $stockDelta = round($available - (float) $material->stock_quantity, 2);

            if ($stockDelta != 0.0) {
                $written[] = $this->write(
                    material: $material,
                    reason: StockMovementReason::Correction,
                    quantity: abs($stockDelta),
                    stockDelta: $stockDelta,
                    bucketColumn: null,
                    bucketDelta: 0.0,
                    context: $context,
                );
            }

            return $written;
        });
    }

    /**
     * Apply the movement and write its ledger row. Callers have already
     * validated; this just does the arithmetic in one place.
     *
     * @param  array{note?: string|null, user_id?: int|null, order_id?: int|null}  $context
     */
    private function write(
        RawMaterial $material,
        StockMovementReason $reason,
        float $quantity,
        float $stockDelta,
        ?string $bucketColumn,
        float $bucketDelta,
        array $context,
        ?int $reverses = null,
    ): RawMaterialMovement {
        // decrement/increment rather than a plain update, so the stock
        // observer still sees the column change and fires its low-stock alert.
        if ($stockDelta < 0) {
            $material->decrement('stock_quantity', abs($stockDelta));
        } elseif ($stockDelta > 0) {
            $material->increment('stock_quantity', $stockDelta);
        }

        if ($bucketColumn !== null && $bucketDelta != 0.0) {
            // Clamped, because a bucket that predates this ledger may already
            // be lower than the entry being unwound.
            $material->update([
                $bucketColumn => max(0, round((float) $material->{$bucketColumn} + $bucketDelta, 2)),
            ]);
        }

        return RawMaterialMovement::create([
            'raw_material_id' => $material->raw_material_id,
            'user_id' => $context['user_id'] ?? null,
            'order_id' => $context['order_id'] ?? null,
            'reverses_movement_id' => $reverses,
            'reason' => $reason,
            'quantity' => round($quantity, 2),
            'stock_delta' => round($stockDelta, 2),
            'stock_after' => round((float) $material->stock_quantity, 2),
            'note' => $context['note'] ?? null,
        ]);
    }

    /**
     * Re-read the row inside the transaction so two people recording usage at
     * once can't both pass the shortage check against the same stale figure.
     */
    private function lock(RawMaterial $material): RawMaterial
    {
        return RawMaterial::lockForUpdate()->findOrFail($material->raw_material_id);
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
