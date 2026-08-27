<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'total_amount',
        'payment_reference',
        'reason'
    ];

    /**
     * Build the next order number for today, e.g. ORDR-20260827-0001.
     *
     * The sequence restarts each day. Suffixes that aren't a plain number are
     * skipped, so the seeded demo orders (ORDR-20260827-PEND and friends) never
     * get read as a counter. Two checkouts racing can land on the same
     * candidate, so callers retry on the order_number unique index.
     */
    public static function nextOrderNumber(): string
    {
        $prefix = 'ORDR-' . now()->format('Ymd') . '-';

        $highest = static::where('order_number', 'like', $prefix . '%')
            ->pluck('order_number')
            ->map(fn ($number) => substr($number, strlen($prefix)))
            ->filter(fn ($suffix) => ctype_digit($suffix))
            ->map(fn ($suffix) => (int) $suffix)
            ->max();

        return $prefix . str_pad((string) (($highest ?? 0) + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
}
