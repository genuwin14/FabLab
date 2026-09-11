<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    use Concerns\TracksStockLevel;
    use Concerns\HasStoredImage;

    protected $primaryKey = 'product_id';

    protected string $stockColumn = 'stock';
    protected string $stockItemType = 'Product';

    protected string $imageColumn = 'image';
    protected string $imageDirectory = 'products';

    /** So JSON payloads (order modals, JS) carry a usable image URL. */
    protected $appends = ['image_url'];

    protected $fillable = [
        'sku',
        'name',
        'description',
        'brand',
        'price',
        'stock',
        'units_on_display',
        'units_sponsored',
        'units_damaged',
        'units_consumed',
        'department',
        'category_id',
        'status',
        'is_customizable',
        'has_sizes',
        'print_area_cm2',
        'low_stock_threshold',
        'unit',
        'image'
    ];

    protected $casts = [
        'print_area_cm2' => 'float',
        'has_sizes' => 'boolean',
    ];

    /**
     * The printable area of this product at the given garment size, in square
     * centimetres — the blank's own figure scaled by the size's factor.
     *
     * Null when the product's print area has never been measured. That is
     * the signal for the stock service to fall back to the per-element bills
     * of materials rather than draw a measured figure against nothing.
     */
    public function printAreaFor(?string $size): ?float
    {
        if ($this->print_area_cm2 === null || (float) $this->print_area_cm2 <= 0) {
            return null;
        }

        return round((float) $this->print_area_cm2 * CustomizationRate::areaFactorForSize($size), 2);
    }

    /**
     * The shapes the 3D studio can actually render, matched against a name.
     *
     * One entry per GLB in public/gbl — mug is cup.glb, umbrella is
     * umbreella_open.glb. Shorts is deliberately absent: models/shorts.js loads
     * a file that was never shipped and falls through to a placeholder box.
     *
     * Order matters, because these are substring matches against the product
     * name and the first hit wins. 'polo' sits ahead of 't-shirt' so a "Polo
     * T-Shirt" opens in the polo model rather than the tee.
     */
    private const CUSTOMIZER_SHAPES = ['mug', 'polo', 't-shirt', 'umbrella', 'bag'];

    /**
     * Which model the studio opens this product in, or null if none fits.
     *
     * A product outside the list has no mesh to render, so the customizer would
     * open it as the default t-shirt wearing another product's name — an ID lace
     * shown as a shirt. That makes `is_customizable` on such a product a data
     * error rather than a choice, which is what the seeder and its guard in
     * SeedDataIntegrityTest enforce.
     */
    public function customizerShape(): ?string
    {
        $name = strtolower($this->name ?? '');

        foreach (self::CUSTOMIZER_SHAPES as $shape) {
            if (str_contains($name, $shape)) {
                return $shape;
            }
        }

        return null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers', 'product_id', 'supplier_id')
            ->withPivot(['cost', 'is_default', 'min_order_qty', 'lead_time_days'])
            ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    /**
     * Get the customized designs created for this product.
     */
    public function customDesigns()
    {
        return $this->hasMany(CustomDesign::class, 'product_id', 'product_id');
    }

    public function rawMaterials(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'product_raw_materials', 'product_id', 'raw_material_id')
            // requires_design marks a line as a decorating consumable rather
            // than part of the blank — see the migration that adds it.
            ->withPivot('quantity_required', 'requires_design')
            ->withTimestamps();
    }

    public function textures(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Texture::class, 'product_textures', 'product_id', 'texture_id')
            ->withTimestamps();
    }

    /** Plain finishes offered for this product, the alternative to a texture. */
    public function colors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_colors', 'product_id', 'color_id')
            ->withTimestamps();
    }

    // ------------------------------------------------ stock by size and colour

    /**
     * The cells of this product's stock grid, one per size × colour.
     *
     * Ordered the way the grid is drawn: by size, smallest first, then by
     * colour in the order the colours were assigned. Empty for a product
     * that comes in one size with no colours, which keeps a single figure.
     */
    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'product_id')
            ->orderBy('product_variant_id');
    }

    /** Whether this product is stocked per size and/or per colour at all. */
    public function tracksVariants(): bool
    {
        return (bool) $this->has_sizes || $this->hasColorVariants();
    }

    /** Whether colour is a dimension of the stock grid: any colour assigned. */
    public function hasColorVariants(): bool
    {
        return $this->relationLoaded('colors')
            ? $this->colors->isNotEmpty()
            : $this->colors()->exists();
    }

    /**
     * The colour a design takes when it names none — a texture finish, or a
     * recipe from before colours were assigned. The first assigned colour,
     * which the studio also shows first.
     */
    public function defaultColorId(): ?int
    {
        $colors = $this->relationLoaded('colors') ? $this->colors : $this->colors()->get();

        return $colors->first()?->color_id;
    }

    /**
     * Every cell the grid should have, as variant keys with their size and
     * colour: sizes (or just "none") × assigned colours (or just "none").
     *
     * @return array<string, array{size: ?string, color_id: ?int}>
     */
    public function variantMatrix(): array
    {
        if (! $this->tracksVariants()) {
            return [];
        }

        $sizes = $this->has_sizes ? array_keys(CustomizationRate::sizes()) : [null];
        $colors = $this->relationLoaded('colors') ? $this->colors : $this->colors()->get();
        $colorIds = $colors->isNotEmpty() ? $colors->pluck('color_id')->all() : [null];

        $matrix = [];
        foreach ($sizes as $size) {
            foreach ($colorIds as $colorId) {
                $matrix[ProductVariant::keyFor($size, $colorId)] = ['size' => $size, 'color_id' => $colorId];
            }
        }

        return $matrix;
    }

    /**
     * Make the stored cells match the grid: create what is missing, drop
     * what no longer belongs, and lose no stock doing it.
     *
     * Stock in a cell that disappears — a colour unassigned, sizes switched
     * off — moves to the nearest cell that remains: one of the same colour
     * if there is one, else the first. A product that gains its first cells
     * moves its single figure into the first of them, for an admin to
     * spread out. A product that loses its last cells keeps the total as
     * its single figure. Either way the total never changes here.
     */
    public function ensureVariants(): void
    {
        $matrix = $this->variantMatrix();
        $existing = $this->variants()->get()->keyBy('variant_key');

        if ($matrix === []) {
            if ($existing->isNotEmpty()) {
                $total = (int) $existing->sum('stock');
                ProductVariant::withoutEvents(fn () => $existing->each->delete());
                $this->unsetRelation('variants');
                $this->update(['stock' => $total]);
            }

            return;
        }

        $hadCells = $existing->isNotEmpty();
        $orphaned = 0;
        $carry = [];

        foreach ($existing as $key => $variant) {
            if (isset($matrix[$key])) {
                continue;
            }

            // Prefer a surviving cell of the same colour.
            $target = null;
            foreach ($matrix as $candidate => $cell) {
                if ($cell['color_id'] === $variant->color_id) {
                    $target = $candidate;
                    break;
                }
            }
            $carry[$target ?? array_key_first($matrix)] = ($carry[$target ?? array_key_first($matrix)] ?? 0) + (int) $variant->stock;
            $orphaned++;

            ProductVariant::withoutEvents(fn () => $variant->delete());
        }

        // A product's first cells inherit the figure it had as one product.
        if (! $hadCells) {
            $carry[array_key_first($matrix)] = ($carry[array_key_first($matrix)] ?? 0) + (int) $this->stock;
        }

        foreach ($matrix as $key => $cell) {
            $variant = $existing->get($key);

            if (! $variant) {
                // Created without events, so the key is set here rather than
                // by the saving hook that would otherwise do it.
                $variant = ProductVariant::withoutEvents(fn () => ProductVariant::create([
                    'product_id' => $this->product_id,
                    'size' => $cell['size'],
                    'color_id' => $cell['color_id'],
                    'variant_key' => $key,
                    'stock' => 0,
                ]));
            }

            if (($carry[$key] ?? 0) > 0) {
                ProductVariant::withoutEvents(fn () => $variant->update(['stock' => $variant->stock + $carry[$key]]));
            }
        }

        $this->unsetRelation('variants');
        $this->syncStockFromVariants();
    }

    /**
     * Re-sum the product's `stock` from its cells. Called whenever a cell
     * moves, so the total every other screen reads stays right — and goes
     * through update(), so the total's own low-stock alert still fires.
     */
    public function syncStockFromVariants(): void
    {
        if (! $this->variants()->exists()) {
            return;
        }

        $total = (int) $this->variants()->sum('stock');

        if ((int) $this->stock !== $total) {
            $this->update(['stock' => $total]);
        }
    }

    /**
     * The cell a size and colour land in, resolved against what this
     * product tracks: a size only if it has sizes, a colour only if it has
     * colours — and the default colour when it has colours and none was
     * named. Null for a product with no cells, which is moved as a whole.
     */
    public function variantFor(?string $size, int|string|null $colorId): ?ProductVariant
    {
        if (! $this->tracksVariants()) {
            return null;
        }

        $size = $this->has_sizes && CustomizationRate::keyForSize($size) ? strtolower(trim($size)) : null;
        $colorId = $this->hasColorVariants() ? ((int) $colorId ?: $this->defaultColorId()) : null;

        // A colour the product doesn't carry is not a cell it has.
        if ($colorId !== null && ! $this->colors()->where('colors.color_id', $colorId)->exists()) {
            $colorId = $this->defaultColorId();
        }

        $key = ProductVariant::keyFor($size, $colorId);
        $variant = $this->variants()->where('variant_key', $key)->first();

        // A cell the grid should have but doesn't yet exist is created on
        // demand, so a product whose colours were assigned before this
        // existed still resolves.
        if (! $variant && isset($this->variantMatrix()[$key])) {
            $this->ensureVariants();
            $variant = $this->variants()->where('variant_key', $key)->first();
        }

        return $variant;
    }

    /** The first cell of the grid, where stock with no cell named lands. */
    public function firstVariant(): ?ProductVariant
    {
        if (! $this->tracksVariants()) {
            return null;
        }

        return $this->variants()->first() ?? tap($this, fn () => $this->ensureVariants())->variants()->first();
    }
}
