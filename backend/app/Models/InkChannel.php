<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One of the printer's four inks, and what a square centimetre of it costs.
 *
 * The customizer's ink used to be a fixed figure per element, which is a bill
 * of materials guessing at a picture. Ink is now measured: the studio exports
 * the flat print, the server works out how much of the printable area each
 * channel covers, and this row turns that coverage into millilitres —
 * `coverage × printable area × ml_per_cm2` — and says which bottle they come
 * out of.
 *
 * The four channels are fixed; only the material link and the rate are
 * editable. Rates are read through rates(), memoised per request the same way
 * customization rates are, so every design costed in one request sees one
 * consistent set of figures.
 */
class InkChannel extends Model
{
    /**
     * Channel key => how the screens name it and the CSS colour they swatch
     * it with. Listed in the order a CMYK split is normally written.
     */
    public const CHANNELS = [
        'cyan' => ['label' => 'Cyan', 'swatch' => '#00b7eb'],
        'magenta' => ['label' => 'Magenta', 'swatch' => '#ec008c'],
        'yellow' => ['label' => 'Yellow', 'swatch' => '#ffef00'],
        'black' => ['label' => 'Black', 'swatch' => '#231f20'],
    ];

    /**
     * The rate a fresh install starts with, in millilitres per square
     * centimetre of solid coverage.
     *
     * A ballpark for a desktop dye-sublimation printer laying down one channel
     * at full density: an A4 sheet of solid colour comes to a millilitre and a
     * half or so. It is meant to be calibrated, not trusted — print a solid
     * square, weigh the bottle, divide.
     */
    public const DEFAULT_ML_PER_CM2 = 0.0025;

    protected $primaryKey = 'ink_channel_id';

    protected $fillable = ['channel', 'raw_material_id', 'ml_per_cm2'];

    protected $casts = ['ml_per_cm2' => 'float'];

    /** Per-request memo. See rates(). */
    private static ?array $cachedRates = null;

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id', 'raw_material_id');
    }

    /**
     * Every channel as `channel => ['raw_material_id' => ?int, 'ml_per_cm2' => float]`.
     *
     * Always all four, in CHANNELS order, so a caller can iterate without
     * checking. A channel whose row is missing reads as unlinked with the
     * default rate. The swallowed throwable is the same guard the other rate
     * tables use: a console command running before migrate must not fatal on
     * a table that isn't there yet.
     *
     * @return array<string, array{raw_material_id: ?int, ml_per_cm2: float}>
     */
    public static function rates(): array
    {
        if (self::$cachedRates !== null) return self::$cachedRates;

        try {
            $stored = self::query()->get(['channel', 'raw_material_id', 'ml_per_cm2'])->keyBy('channel');
        } catch (\Throwable) {
            $stored = collect();
        }

        $rates = [];
        foreach (array_keys(self::CHANNELS) as $channel) {
            $row = $stored->get($channel);
            $rates[$channel] = [
                'raw_material_id' => $row?->raw_material_id !== null ? (int) $row->raw_material_id : null,
                'ml_per_cm2' => $row ? (float) $row->ml_per_cm2 : self::DEFAULT_ML_PER_CM2,
            ];
        }

        return self::$cachedRates = $rates;
    }

    /**
     * The materials the channels empty, `channel => raw_material_id`, only
     * for channels that are linked to a bottle and cost something per cm².
     *
     * This is the set a coverage-based draw can reach. An order that measures
     * its ink skips these same materials in the per-element bills of
     * materials, so a bottle is never drawn twice for one print.
     *
     * @return array<string, int>
     */
    public static function linkedMaterials(): array
    {
        $linked = [];
        foreach (self::rates() as $channel => $rate) {
            if ($rate['raw_material_id'] !== null && $rate['ml_per_cm2'] > 0) {
                $linked[$channel] = $rate['raw_material_id'];
            }
        }

        return $linked;
    }

    /** Whether measured ink can be drawn at all: at least one bottle is linked. */
    public static function configured(): bool
    {
        return self::linkedMaterials() !== [];
    }

    /**
     * The channels with their live settings merged in, for the admin and
     * staff screens.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function forDisplay(): array
    {
        $rates = self::rates();

        return collect(self::CHANNELS)
            ->map(fn ($definition, $channel) => $definition + ['key' => $channel] + $rates[$channel])
            ->all();
    }

    /** Drop the memo — for tests and long-running workers. */
    public static function flushCache(): void
    {
        self::$cachedRates = null;
    }
}
