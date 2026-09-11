<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An admin-editable price for one kind of customization.
 *
 * The set of rates is fixed by DEFINITIONS below — the customizer only knows
 * how to apply these — so the admin screen edits amounts and nothing else.
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
     *
     * Sizes also carry `short`, the garment code (S … 5XL) the studio prints on
     * the size button and the admin screen shows beside the name.
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
        // Listed smallest first, which is the order every screen shows them
        // in; the shop takes garments up to 5XL.
        //
        // `area_factor` is how much bigger or smaller a design prints on this
        // size than on the product's Medium, which is the figure the
        // product's own print area records. Measured ink and paper scale by
        // it. It ships at 1 for every size because the shop presses a cut
        // transfer, and a 4×2 sticker is 4×2 on a Small and on a 5XL; a shop
        // that scales the print with the garment can raise it per size on
        // the pricing screen, beside the surcharge.
        'size_small' => [
            'group' => 'sizes',
            'label' => 'Small',
            'short' => 'S',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-dash-square',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
        'size_medium' => [
            'group' => 'sizes',
            'label' => 'Medium',
            'short' => 'M',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-square',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
        'size_large' => [
            'group' => 'sizes',
            'label' => 'Large',
            'short' => 'L',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-plus-square',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
        'size_xl' => [
            'group' => 'sizes',
            'label' => 'X-Large',
            'short' => 'XL',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-plus-square-fill',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
        'size_2xl' => [
            'group' => 'sizes',
            'label' => '2X-Large',
            'short' => '2XL',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-plus-square-fill',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
        'size_3xl' => [
            'group' => 'sizes',
            'label' => '3X-Large',
            'short' => '3XL',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-plus-square-fill',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
        'size_4xl' => [
            'group' => 'sizes',
            'label' => '4X-Large',
            'short' => '4XL',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-plus-square-fill',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
        'size_5xl' => [
            'group' => 'sizes',
            'label' => '5X-Large',
            'short' => '5XL',
            'description' => 'Added when the customer orders this size.',
            'icon' => 'bi-plus-square-fill',
            'suffix' => 'per item',
            'default' => 0,
            'area_factor' => 1.0,
        ],
    ];

    /** The recipe value a size rate key stands for: `size_2xl` → `2xl`. */
    private const SIZE_PREFIX = 'size_';

    /** The rate key for a recipe's size, or null if it names something unknown. */
    public static function keyForSize(?string $size): ?string
    {
        $key = self::SIZE_PREFIX . strtolower(trim((string) $size));

        return isset(self::DEFINITIONS[$key]) ? $key : null;
    }

    /**
     * The sizes a customer can order, smallest first, keyed by the value the
     * design recipe stores (`small` … `5xl`). Each carries its definition plus
     * `rate_key`, so the studio can build its size buttons from the same list
     * the admin prices and the cart charges against.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function sizes(): array
    {
        $sizes = [];

        foreach (self::DEFINITIONS as $key => $definition) {
            if ($definition['group'] !== 'sizes') continue;

            $sizes[substr($key, strlen(self::SIZE_PREFIX))] = $definition + ['rate_key' => $key];
        }

        return $sizes;
    }

    protected $primaryKey = 'customization_rate_id';

    protected $fillable = ['key', 'amount', 'print_area_factor'];

    protected $casts = ['amount' => 'float', 'print_area_factor' => 'float'];

    /** Per-request memo. One small query serves every design priced in a request. */
    private static ?array $cachedAmounts = null;

    /** The same memo for the material side. See materials(). */
    private static ?array $cachedMaterials = null;

    /** And for the print-area factors. See areaFactors(). */
    private static ?array $cachedAreaFactors = null;

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
     * How much bigger each size's printable panels are than the product's
     * Medium, as `size_key => factor`, with shipped defaults filling gaps.
     *
     * Only the size rows have one; the elements are absent. Same guarded read
     * as amounts(), and for the same reason.
     *
     * @return array<string, float>
     */
    public static function areaFactors(): array
    {
        if (self::$cachedAreaFactors !== null) return self::$cachedAreaFactors;

        $defaults = collect(self::DEFINITIONS)
            ->filter(fn ($definition) => isset($definition['area_factor']))
            ->map(fn ($definition) => (float) $definition['area_factor'])
            ->all();

        try {
            $stored = self::query()->whereIn('key', array_keys($defaults))
                ->pluck('print_area_factor', 'key')
                ->map(fn ($factor) => (float) $factor)
                ->all();
        } catch (\Throwable) {
            $stored = [];
        }

        return self::$cachedAreaFactors = array_merge($defaults, $stored);
    }

    /**
     * The print-area factor for a recipe's size. An unknown or missing size
     * is treated as Medium — the product's own figure, unscaled — rather
     * than guessed at.
     */
    public static function areaFactorForSize(?string $size): float
    {
        $key = self::keyForSize($size);

        return $key ? (self::areaFactors()[$key] ?? 1.0) : 1.0;
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
        self::$cachedAreaFactors = null;
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
        $areaFactors = self::areaFactors();

        return collect(self::DEFINITIONS)
            ->map(fn($definition, $key) => $definition + [
                'key' => $key,
                'amount' => $amounts[$key] ?? 0.0,
                // What the shop spends on the option, alongside what it
                // charges for it. `raw_material_id => quantity_required`.
                'materials' => $materials[$key] ?? [],
                // Sizes only: the live factor replaces the shipped default.
                'area_factor' => $areaFactors[$key] ?? null,
            ])
            // preserveKeys: the rate key is the form field name, so losing it
            // would post rates[0] instead of rates[logo].
            ->groupBy('group', true)
            ->map(fn($group) => $group->all())
            ->all();
    }
}
