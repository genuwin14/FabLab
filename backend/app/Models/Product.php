<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    use Concerns\TracksStockLevel;

    protected $primaryKey = 'product_id';

    protected string $stockColumn = 'stock';
    protected string $stockItemType = 'Product';

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
}
