<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'order_item_id';

    protected $fillable = [
        'order_id',
        'product_id',
        'custom_design_id',
        'quantity',
        'price'
    ];

    /**
     * Get the customized design associated with the order item.
     */
    public function customDesign()
    {
        return $this->belongsTo(CustomDesign::class, 'custom_design_id', 'custom_design_id');
    }

    /**
     * Get the order that owns the order item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /**
     * Get the product associated with the order item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
