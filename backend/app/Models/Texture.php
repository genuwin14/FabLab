<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Texture extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'texture_id';

    protected $fillable = [
        'name',
        'image_path',
        'description',
        'supplier_id',
        'cost_per_unit',
        'stock_quantity',
        'low_stock_threshold',
        'unit',
        'price_modifier',
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
