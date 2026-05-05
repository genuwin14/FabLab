<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'purchase_order_item_id';

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'raw_material_id',
        'texture_id',
        'quantity',
        'cost'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function texture()
    {
        return $this->belongsTo(Texture::class, 'texture_id', 'texture_id');
    }
}
