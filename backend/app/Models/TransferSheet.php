<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The transfer sheet a design is printed on, and what it is stocked as.
 *
 * The shop cuts every print from an A4 sheet and presses the piece onto the
 * product. So the paper an order takes is measured, like its ink: each
 * panel's artwork is boxed off the print, the box grows by the cutting
 * margin, and the piece is a fraction of this sheet. See
 * InkEstimator::paper().
 *
 * One row, read through current(), memoised per request like the ink
 * channels. A missing row reads as an A4 with nothing linked, so the
 * screens still have something to show; nothing is drawn until an admin
 * links the paper.
 */
class TransferSheet extends Model
{
    public const A4_WIDTH_CM = 21.0;
    public const A4_HEIGHT_CM = 29.7;
    public const DEFAULT_MARGIN_CM = 0.5;

    protected $primaryKey = 'transfer_sheet_id';

    protected $fillable = ['name', 'raw_material_id', 'width_cm', 'height_cm', 'margin_cm'];

    protected $casts = [
        'width_cm' => 'float',
        'height_cm' => 'float',
        'margin_cm' => 'float',
    ];

    /** Per-request memo. See current(). */
    private static ?array $cachedCurrent = null;

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
     * The sheet in use, as a plain array.
     *
     * Same guarded read as the other settings tables: a console command
     * running before migrate must not fatal on a table that isn't there.
     *
     * @return array{name: string, raw_material_id: ?int, width_cm: float, height_cm: float, margin_cm: float}
     */
    public static function current(): array
    {
        if (self::$cachedCurrent !== null) return self::$cachedCurrent;

        try {
            $row = self::query()->orderBy('transfer_sheet_id')->first();
        } catch (\Throwable) {
            $row = null;
        }

        return self::$cachedCurrent = [
            'name' => $row?->name ?? 'A4',
            'raw_material_id' => $row?->raw_material_id !== null ? (int) $row->raw_material_id : null,
            'width_cm' => $row ? (float) $row->width_cm : self::A4_WIDTH_CM,
            'height_cm' => $row ? (float) $row->height_cm : self::A4_HEIGHT_CM,
            'margin_cm' => $row ? (float) $row->margin_cm : self::DEFAULT_MARGIN_CM,
        ];
    }

    /** The paper's raw material, or null while nothing is linked. */
    public static function materialId(): ?int
    {
        return self::current()['raw_material_id'];
    }

    /** Whether measured paper can be drawn at all: the sheet is stocked as something. */
    public static function configured(): bool
    {
        $sheet = self::current();

        return $sheet['raw_material_id'] !== null && $sheet['width_cm'] > 0 && $sheet['height_cm'] > 0;
    }

    /** Drop the memo — for tests and long-running workers. */
    public static function flushCache(): void
    {
        self::$cachedCurrent = null;
    }
}
