<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDesign extends Model
{
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
     * Get the user who created the design.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being customized.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Get the order items that use this design.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'custom_design_id', 'custom_design_id');
    }
}
