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
            'group' => 'elements',
            'label' => 'Custom text',
            'description' => 'Charged once per line of text a customer adds to a design.',
            'icon' => 'bi-fonts',
            'suffix' => 'each',
            'default' => 50,
        ],
        'shape' => [
            'group' => 'elements',
            'label' => 'Custom shape',
            'description' => 'Charged once per circle or line a customer adds to a design.',
            'icon' => 'bi-circle-square',
            'suffix' => 'each',
            'default' => 30,
        ],
        'logo' => [
            'group' => 'elements',
            'label' => 'Uploaded image',
            'description' => 'Charged per uploaded image, in proportion to the size it is printed at. Half size costs half this, double size costs double.',
            'icon' => 'bi-image',
            'suffix' => 'each at 1× size',
            'default' => 150,
        ],
        'led_lighting' => [
            'group' => 'elements',
            'label' => 'Internal LED lighting',
            'description' => 'Charged once when a customer switches the lighting feature on.',
            'icon' => 'bi-lightbulb',
            'suffix' => 'per item',
            'default' => 500,
        ],

        // Sizes default to zero, so nothing reprices until someone sets them.
        // Only one ever applies to an item — the size the customer picked.
        'size_small' => [
            'group' => 'sizes',
            'label' => 'Small',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-dash-square',
            'suffix' => 'per item',
            'default' => 0,
        ],
        'size_medium' => [
            'group' => 'sizes',
            'label' => 'Medium',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-square',
            'suffix' => 'per item',
            'default' => 0,
        ],
        'size_large' => [
            'group' => 'sizes',
            'label' => 'Large',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-plus-square',
            'suffix' => 'per item',
            'default' => 0,
        ],
    ];

    /** The rate key for a recipe's size, or null if it names something unknown. */
    public static function keyForSize(?string $size): ?string
    {
        $key = 'size_' . strtolower(trim((string) $size));

        return isset(self::DEFINITIONS[$key]) ? $key : null;
    }

    protected $primaryKey = 'customization_rate_id';

    protected $fillable = ['key', 'amount'];

    protected $casts = ['amount' => 'float'];

    /** Per-request memo. One small query serves every design priced in a request. */
    private static ?array $cachedAmounts = null;

    /** The same memo for the material side. See materials(). */
    private static ?array $cachedMaterials = null;

    protected static function booted(): void
    {
        // Any write invalidates the memo, so a save and a re-price in the same
        // request can't disagree.
        static::saved(fn() => self::flushCache());
        static::deleted(fn() => self::flushCache());
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

    /**
     * What one unit of each option takes off the shelf, as
     * `rate_key => [raw_material_id => quantity_required]`.
     *
     * Options with nothing mapped are absent rather than empty, so a caller
     * can ask `$materials[$key] ?? []` and get the "consumes nothing" answer
     * without a second lookup. Same swallowed-throwable guard as amounts():
     * a console command running before migrate must not fatal on a table that
     * isn't there yet.
     *
     * @return array<string, array<int, float>>
     */
    public static function materials(): array
    {
        if (self::$cachedMaterials !== null) return self::$cachedMaterials;

        try {
            $rows = CustomizationRateMaterial::query()
                ->whereIn('rate_key', array_keys(self::DEFINITIONS))
                ->get(['rate_key', 'raw_material_id', 'quantity_required']);
        } catch (\Throwable) {
            $rows = collect();
        }

        $materials = [];
        foreach ($rows as $row) {
            // A zero requirement is the same as no requirement, and letting it
            // through would write ledger rows for nothing.
            if ((float) $row->quantity_required <= 0) continue;

            $materials[$row->rate_key][(int) $row->raw_material_id] = (float) $row->quantity_required;
        }

        return self::$cachedMaterials = $materials;
    }

    /**
     * The BOM for one option: `[raw_material_id => quantity_required]`.
     *
     * @return array<int, float>
     */
    public static function materialsFor(string $key): array
    {
        return self::materials()[$key] ?? [];
    }

    /** Drop both memos — for tests and long-running workers. */
    public static function flushCache(): void
    {
        self::$cachedAmounts = null;
        self::$cachedMaterials = null;
    }

    /**
     * The definitions with their live amounts and bills of materials merged
     * in, grouped for the admin screen — element fees and size surcharges are
     * charged on different things and read better apart.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function forDisplay(): array
    {
        $amounts = self::amounts();
        $materials = self::materials();

        return collect(self::DEFINITIONS)
            ->map(fn($definition, $key) => $definition + [
                'key' => $key,
                'amount' => $amounts[$key] ?? 0.0,
                // What the shop spends on the option, alongside what it
                // charges for it. `raw_material_id => quantity_required`.
                'materials' => $materials[$key] ?? [],
            ])
            // preserveKeys: the rate key is the form field name, so losing it
            // would post rates[0] instead of rates[logo].
            ->groupBy('group', true)
            ->map(fn($group) => $group->all())
            ->all();
    }
}
