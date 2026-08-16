<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An admin-editable price for one kind of customization.
 *
 * The set of rates is fixed by DEFINITIONS below — the customizer only knows
 * how to apply these four — so the admin screen edits amounts and nothing else.
 * Every consumer goes through amountFor(), which falls back to the shipped
 * default if a row is somehow missing, so a half-migrated database still prices
 * designs rather than charging zero.
 */
class CustomizationRate extends Model
{
    /**
     * key => how the rate is presented and what it costs out of the box.
     *
     * `suffix` is the qualifier the admin screen and the studio print after the
     * amount; the logo rate needs one because its charge scales with the size
     * the image is printed at.
     */
    public const DEFINITIONS = [
        'text' => [
            'label' => 'Custom text',
            'description' => 'Charged once per line of text a customer adds to a design.',
            'icon' => 'bi-fonts',
            'suffix' => 'each',
            'default' => 50,
        ],
        'shape' => [
            'label' => 'Custom shape',
            'description' => 'Charged once per circle or line a customer adds to a design.',
            'icon' => 'bi-circle-square',
            'suffix' => 'each',
            'default' => 30,
        ],
        'logo' => [
            'label' => 'Uploaded image',
            'description' => 'Charged per uploaded image, in proportion to the size it is printed at. Half size costs half this, double size costs double.',
            'icon' => 'bi-image',
            'suffix' => 'each at 1× size',
            'default' => 150,
        ],
        'led_lighting' => [
            'label' => 'Internal LED lighting',
            'description' => 'Charged once when a customer switches the lighting feature on.',
            'icon' => 'bi-lightbulb',
            'suffix' => 'per item',
            'default' => 500,
        ],
    ];

    protected $primaryKey = 'customization_rate_id';

    protected $fillable = ['key', 'amount'];

    protected $casts = ['amount' => 'float'];

    /** Per-request memo. One small query serves every design priced in a request. */
    private static ?array $cachedAmounts = null;

    protected static function booted(): void
    {
        // Any write invalidates the memo, so a save and a re-price in the same
        // request can't disagree.
        static::saved(fn() => self::$cachedAmounts = null);
        static::deleted(fn() => self::$cachedAmounts = null);
    }

    /** Every rate as key => amount, with shipped defaults filling any gaps. */
    public static function amounts(): array
    {
        if (self::$cachedAmounts !== null) return self::$cachedAmounts;

        $defaults = array_map(fn($definition) => (float) $definition['default'], self::DEFINITIONS);

        // A missing table (mid-migration, or a console command running before
        // migrate) must not take the storefront down with it.
        try {
            $stored = self::query()->pluck('amount', 'key')
                ->map(fn($amount) => (float) $amount)
                ->all();
        } catch (\Throwable) {
            $stored = [];
        }

        return self::$cachedAmounts = array_merge($defaults, array_intersect_key($stored, $defaults));
    }

    public static function amountFor(string $key): float
    {
        return self::amounts()[$key] ?? 0.0;
    }

    /** Drop the memo — for tests and long-running workers. */
    public static function flushCache(): void
    {
        self::$cachedAmounts = null;
    }

    /** The definitions with their live amounts merged in, for the admin screen. */
    public static function forDisplay(): array
    {
        $amounts = self::amounts();

        return collect(self::DEFINITIONS)
            ->map(fn($definition, $key) => $definition + ['key' => $key, 'amount' => $amounts[$key] ?? 0.0])
            ->all();
    }
}
