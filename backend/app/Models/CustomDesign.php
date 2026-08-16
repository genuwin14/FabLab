<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDesign extends Model
{
    /**
     * How far the Size slider lets an uploaded image be scaled.
     *
     * The image's fee pays for the print, so it tracks how large the customer
     * prints it: half size costs half, double size costs double. Scales outside
     * the slider's own range are clamped, so a hand-edited recipe can't buy a
     * free or negative logo. The rate itself is admin-editable — see
     * App\Models\CustomizationRate.
     */
    public const LOGO_MIN_SCALE = 0.1;
    public const LOGO_MAX_SCALE = 5.0;

    protected $primaryKey = 'custom_design_id';

    protected $fillable = [
        'user_id',
        'product_id',
        'recipe',
        'snapshot'
    ];

    protected $casts = [
        'recipe' => 'array'
    ];

    /**
     * Ship the itemised charges with the design wherever it is serialised, so
     * the admin order inspector can show why a tailored item costs what it does.
     */
    protected $appends = ['price_breakdown'];

    /** Memoised finish lookups — the breakdown and the price both want them. */
    private ?Texture $resolvedTexture = null;
    private bool $textureResolved = false;
    private ?Color $resolvedColor = null;
    private bool $colorResolved = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'custom_design_id', 'custom_design_id');
    }

    /**
     * Look up the Texture record referenced by this design's recipe (if any).
     */
    public function texture()
    {
        if ($this->textureResolved) return $this->resolvedTexture;

        $this->textureResolved = true;
        $textureId = $this->recipe['texture_id'] ?? null;
        $this->resolvedTexture = $textureId ? Texture::withTrashed()->find($textureId) : null;

        return $this->resolvedTexture;
    }

    /**
     * Look up the plain Color referenced by this design's recipe (if any).
     */
    public function color()
    {
        if ($this->colorResolved) return $this->resolvedColor;

        $this->colorResolved = true;
        $colorId = $this->recipe['color_id'] ?? null;
        $this->resolvedColor = $colorId ? Color::withTrashed()->find($colorId) : null;

        return $this->resolvedColor;
    }

    /**
     * The single finish charge for this design.
     *
     * A product is either plain or patterned, so the customizer only ever lets
     * one of the two be picked. If a hand-edited recipe names both, the texture
     * wins and the color is ignored — never both, which would double-charge.
     *
     * @return array{label: string, amount: float}|null
     */
    public function finishLine(): ?array
    {
        $texture = $this->texture();
        if ($texture) {
            return $texture->price_modifier
                ? ['label' => trim(($texture->name ?? 'Texture') . ' texture'), 'amount' => (float) $texture->price_modifier]
                : null;
        }

        $color = $this->color();
        if ($color && $color->price_modifier) {
            return ['label' => trim(($color->name ?? 'Color') . ' finish'), 'amount' => (float) $color->price_modifier];
        }

        return null;
    }

    /**
     * The Size slider value an uploaded image is printed at, clamped to the
     * range the slider itself offers.
     */
    public static function normalisedLogoScale($scale): float
    {
        $value = is_numeric($scale) ? (float) $scale : 1.0;

        return max(self::LOGO_MIN_SCALE, min(self::LOGO_MAX_SCALE, $value));
    }

    /** What one uploaded image costs at the given Size slider value. */
    public static function logoFee($scale): float
    {
        return round(CustomizationRate::amountFor('logo') * self::normalisedLogoScale($scale), 2);
    }

    /**
     * Every charge this design adds on top of the product's own price, itemised.
     * Deliberately free of the product relation so serialising a design doesn't
     * pull one in.
     */
    public function getPriceBreakdownAttribute(): array
    {
        $elements = $this->recipe['elements'] ?? [];
        $lines = [];

        $textCount = count($elements['text'] ?? []);
        if ($textCount > 0) {
            $lines[] = [
                'label' => "Custom text × {$textCount}",
                'amount' => round($textCount * CustomizationRate::amountFor('text'), 2),
            ];
        }

        $shapeCount = count($elements['shapes'] ?? []);
        if ($shapeCount > 0) {
            $lines[] = [
                'label' => "Custom shapes × {$shapeCount}",
                'amount' => round($shapeCount * CustomizationRate::amountFor('shape'), 2),
            ];
        }

        // One line per image — they are priced individually because each is
        // printed at its own size.
        foreach (array_values($elements['logos'] ?? []) as $index => $logo) {
            $scale = self::normalisedLogoScale($logo['scale'] ?? null);
            $lines[] = [
                'label' => 'Uploaded image ' . ($index + 1) . ' at ' . self::formatScale($scale) . '× size',
                'amount' => self::logoFee($scale),
            ];
        }

        if (!empty($this->recipe['features']['led_lighting'])) {
            $lines[] = [
                'label' => 'Internal LED lighting',
                'amount' => CustomizationRate::amountFor('led_lighting'),
            ];
        }

        if ($finish = $this->finishLine()) {
            $lines[] = $finish;
        }

        return $lines;
    }

    public function getCalculatedPriceAttribute()
    {
        $basePrice = $this->product->price ?? 0;
        $extra = array_sum(array_column($this->price_breakdown, 'amount'));

        return $basePrice + $extra;
    }

    /** "1", "0.5", "2.5" — trailing zeros trimmed so labels read naturally. */
    private static function formatScale(float $scale): string
    {
        return rtrim(rtrim(number_format($scale, 2, '.', ''), '0'), '.');
    }
}
