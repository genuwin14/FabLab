<?php

namespace App\Models;

use App\Enums\StockMovementReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * One entry in a raw material's stock ledger.
 *
 * Rows are never edited or deleted — a mistake is undone by writing a
 * `Reversal` that points back at it, so the history stays readable.
 */
class RawMaterialMovement extends Model
{
    protected $primaryKey = 'movement_id';

    protected $fillable = [
        'raw_material_id',
        'user_id',
        'order_id',
        'reverses_movement_id',
        'reason',
        'quantity',
        'stock_delta',
        'stock_after',
        'note',
    ];

    protected $casts = [
        'reason' => StockMovementReason::class,
        'quantity' => 'decimal:2',
        'stock_delta' => 'decimal:2',
        'stock_after' => 'decimal:2',
    ];

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id', 'raw_material_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /**
     * The entry this one undoes, if it is itself a reversal.
     */
    public function reverses(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_movement_id', 'movement_id');
    }

    /**
     * The reversal written against this entry, if someone undid it.
     */
    public function reversal(): HasOne
    {
        return $this->hasOne(self::class, 'reverses_movement_id', 'movement_id');
    }

    /**
     * Only a plain, un-reversed entry can be undone. Reversing a reversal
     * would just re-apply the original.
     */
    public function isReversible(): bool
    {
        return $this->reason !== StockMovementReason::Reversal
            && $this->reversal === null;
    }

    /**
     * Who to credit in the log. Order approvals run without a signed-in
     * person behind them in queue and command contexts.
     */
    public function actorName(): string
    {
        if ($this->user) {
            return $this->user->fullname;
        }

        return $this->order_id ? 'Order approval' : 'System';
    }
}
