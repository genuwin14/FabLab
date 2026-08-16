<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A plain finish a customer can pick instead of an image texture.
 *
 * Colors and textures are mutually exclusive on a design — see
 * CustomDesign::finishLine() for how that is settled when pricing.
 */
class Color extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'color_id';

    protected $fillable = [
        'name',
        'hex_code',
        'description',
        'price_modifier',
    ];

    protected $casts = [
        'price_modifier' => 'float',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_colors', 'color_id', 'product_id');
    }

    /**
     * Readable ink for text sitting on top of the swatch. Uses the standard
     * luminance weighting so a pale yellow gets dark text and a navy gets white.
     */
    public function getContrastColorAttribute(): string
    {
        $hex = ltrim($this->hex_code ?? '#000000', '#');
        if (strlen($hex) !== 6) return '#ffffff';

        [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.6 ? '#05111a' : '#ffffff';
    }
}
