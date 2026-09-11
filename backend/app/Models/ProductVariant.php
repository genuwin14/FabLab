<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One cell of a product's stock grid: a size, a colour, and how many.
 *
 * A garment is stocked per colour and per size, and "200 shirts" says
 * nothing about the Navy 5XL being down to one. Each variant carries its
 * own stock and its own threshold (falling back to the product's), and
 * raises its own low-stock alert, so the alert names the cell.
 *
 * The product's `stock` is the sum of its variants and is re-summed
 * whenever one moves — see Product::syncStockFromVariants() — so every
 * total on every screen stays right without knowing variants exist.
 *
 * Either dimension can be absent: a mug has no size, a product with no
 * colours assigned has no colour. A product with neither has no variants
 * at all and keeps its single figure.
 */
class ProductVariant extends Model
{
    use Concerns\TracksStockLevel;

    protected $primaryKey = 'product_variant_id';

    protected string $stockColumn = 'stock';
    protected string $stockItemType = 'Product variant';

    protected $fillable = ['product_id', 'size', 'color_id', 'stock', 'low_stock_threshold', 'variant_key'];

    protected $casts = ['stock' => 'integer', 'low_stock_threshold' => 'integer'];

    /** Every serialised variant says what it is, for the screens. */
    protected $appends = ['label'];

    protected static function booted(): void
    {
        static::saving(function (self $variant) {
            $variant->variant_key = self::keyFor($variant->size, $variant->color_id);
        });

        // created/updated/deleted rather than saved: an increment() fires
        // `updated` without `saved`, and that is how checkout moves stock.
        $resum = fn (self $variant) => $variant->product?->syncStockFromVariants();
        static::created($resum);
        static::updated(function (self $variant) use ($resum) {
            if ($variant->wasChanged('stock')) {
                $resum($variant);
            }
        });
        static::deleted($resum);
    }

    /** "size:colour", with "-" for a dimension the product doesn't have. */
    public static function keyFor(?string $size, int|string|null $colorId): string
    {
        return ($size !== null && $size !== '' ? strtolower($size) : '-') . ':' . ($colorId ? (int) $colorId : '-');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id', 'color_id')->withTrashed();
    }

    /** The size as the screens print it: "L", "5XL". Empty for a one-size product. */
    public function sizeShort(): string
    {
        $key = CustomizationRate::keyForSize($this->size);

        return $key ? CustomizationRate::DEFINITIONS[$key]['short'] : '';
    }

    /**
     * "Navy Blue · L", "L", "Navy Blue" — whatever dimensions the cell has.
     * Empty only for a product with neither, which has no variants anyway.
     */
    public function getLabelAttribute(): string
    {
        $parts = array_filter([$this->color?->name, $this->sizeShort()]);

        return implode(' · ', $parts);
    }

    /** The alert needs a name: the product's, then the cell. */
    public function getNameAttribute(): string
    {
        $label = $this->label;

        return trim(($this->product?->name ?? 'Product') . ($label !== '' ? " — {$label}" : ''));
    }

    /**
     * Its own threshold, else its share of the product's.
     *
     * A product's threshold is for the whole shelf. Spread over thirty-two
     * cells it would flag every one of them, so a cell without a threshold
     * of its own takes the product's divided by the number of cells, and
     * never less than one.
     */
    public function stockThreshold(): ?float
    {
        if ($this->low_stock_threshold !== null) {
            return (float) $this->low_stock_threshold;
        }

        $product = $this->product;
        $threshold = $product?->low_stock_threshold;
        if ($threshold === null) {
            return null;
        }

        $cells = $product->relationLoaded('variants') ? $product->variants->count() : $product->variants()->count();

        return (float) max(1, (int) ceil($threshold / max(1, $cells)));
    }

    public function stockUnit(): string
    {
        return $this->product?->unit ?: 'pcs';
    }
}
