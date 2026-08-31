<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line in a customization option's bill of materials.
 *
 * `CustomizationRate` says what an option charges the customer;
 * this says what one unit of it takes off the shelf — a millilitre of ink per
 * line of text, an LED strip per lit item.
 */
class CustomizationRateMaterial extends Model
{
    protected $primaryKey = 'customization_rate_material_id';

    protected $fillable = [
        'rate_key',
        'raw_material_id',
        'quantity_required',
    ];

    protected $casts = [
        'quantity_required' => 'float',
    ];

    protected static function booted(): void
    {
        // The BOM is memoised per request the same way rates are, so a save
        // and a re-read in the same request can't disagree.
        static::saved(fn () => CustomizationRate::flushCache());
        static::deleted(fn () => CustomizationRate::flushCache());
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id', 'raw_material_id');
    }
}
