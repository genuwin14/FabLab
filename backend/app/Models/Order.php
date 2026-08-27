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
        'payment_method',
        'total_amount',
        'payment_reference',
        'pr_number',
        'pr_deadline',
        'noa_path',
        'po_path',
        'reason'
    ];

    protected $casts = [
        'pr_deadline' => 'datetime',
    ];

    /** Paid at the CSPC Cashier against the transaction slip. */
    public const METHOD_CASH = 'cash';

    /** Bought through CSPC procurement on a Purchase Request. */
    public const METHOD_PR = 'pr';

    public const METHODS = [self::METHOD_CASH, self::METHOD_PR];

    /**
     * Every status an order can hold. On MySQL the column is an ENUM that says
     * the same thing; on SQLite this list is the only guard, so validation
     * rules should lean on it rather than repeating the strings.
     */
    public const STATUSES = [
        'pending',
        'awaiting_pr',
        'approved',
        'processing',
        'ready_for_pickup',
        'for_delivery',
        'completed',
        'cancelled',
    ];

    /**
     * How a status reads on screen. Only the PR one needs saying explicitly —
     * everything else is already a sentence once the underscores go.
     */
    public static function statusLabel(string $status): string
    {
        return $status === 'awaiting_pr'
            ? 'Awaiting PR'
            : ucwords(str_replace('_', ' ', $status));
    }

    public function isPurchaseRequest(): bool
    {
        return $this->payment_method === self::METHOD_PR;
    }

    /**
     * Waiting on the customer to come back with a PR number from procurement.
     */
    public function isAwaitingPr(): bool
    {
        return $this->status === 'awaiting_pr';
    }

    /**
     * The PR window has run out with no number submitted. Only meaningful
     * while the order is still waiting — once it moves on, the deadline is
     * history rather than a verdict.
     */
    public function prWindowHasClosed(): bool
    {
        return $this->isAwaitingPr()
            && $this->pr_deadline !== null
            && $this->pr_deadline->isPast();
    }

    /**
     * Whole days left in the PR window, floored at zero.
     */
    public function prDaysRemaining(): ?int
    {
        if (! $this->isAwaitingPr() || $this->pr_deadline === null) {
            return null;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->pr_deadline->copy()->startOfDay(), false));
    }

    /**
     * Build the next order number for a day, e.g. ORDR-20260827-0001.
     *
     * The sequence restarts each day. Suffixes that aren't a plain number are
     * skipped, so the seeded demo orders (ORDR-20260827-PEND and friends) never
     * get read as a counter. Two checkouts racing can land on the same
     * candidate, so callers retry on the order_number unique index.
     *
     * Pass a date to number a backdated order — seeded history, mainly, which
     * must not collide when the seeder is run more than once.
     */
    public static function nextOrderNumber(?\DateTimeInterface $date = null): string
    {
        $prefix = 'ORDR-' . ($date ? \Illuminate\Support\Carbon::instance($date) : now())->format('Ymd') . '-';

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
