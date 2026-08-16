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
        'low_stock_threshold',
        'unit',
        'image'
    ];

    /**
     * The shapes the 3D studio can actually render, matched against a name.
     *
     * One entry per GLB in public/gbl — mug is cup.glb, umbrella is
     * umbreella_open.glb. Shorts is deliberately absent: models/shorts.js loads
     * a file that was never shipped and falls through to a placeholder box.
     */
    private const CUSTOMIZER_SHAPES = ['mug', 't-shirt', 'umbrella', 'bag'];

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
            ->withPivot('quantity_required')
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
}
