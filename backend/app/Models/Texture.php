<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Texture extends Model
{
    use SoftDeletes;
    use Concerns\TracksStockLevel;

    protected $primaryKey = 'texture_id';

    protected string $stockColumn = 'stock_quantity';
    protected string $stockItemType = 'Texture';

    protected $fillable = [
        'name',
        'image_path',
        'description',
        'supplier_id',
        'cost_per_unit',
        'stock_quantity',
        'units_on_display',
        'units_sponsored',
        'units_damaged',
        'units_consumed',
        'low_stock_threshold',
        'unit',
        'price_modifier',
        'department',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_textures', 'texture_id', 'product_id')
            ->withTimestamps();
    }
}
