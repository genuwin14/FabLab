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
    use Concerns\ConsumesRawMaterial;

    /**
     * What the admin and staff screens accept. Shared so the two can't drift
     * into disagreeing on what a valid swatch is — '#RRGGBB' only, because the
     * customizer hands hex_code straight to CSS and THREE.Color.
     */
    public const VALIDATION_RULES = [
        'name' => 'required|string|max:255',
        'hex_code' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        'description' => 'nullable|string|max:255',
        'price_modifier' => 'nullable|numeric|min:0|max:999999.99',
    ] + self::MATERIAL_VALIDATION_RULES;

    public const VALIDATION_MESSAGES = [
        'hex_code.regex' => 'Enter a colour as a 6-digit hex code, e.g. #1B2A4A.',
    ] + self::MATERIAL_VALIDATION_MESSAGES;

    protected $primaryKey = 'color_id';

    protected $fillable = [
        'name',
        'hex_code',
        'description',
        'price_modifier',
        'raw_material_id',
        'material_quantity',
    ];

    protected $casts = [
        'price_modifier' => 'float',
        'material_quantity' => 'float',
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
