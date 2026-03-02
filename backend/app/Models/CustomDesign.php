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

    /**
     * Calculate the price based on customization.
     */
    public function getCalculatedPriceAttribute()
    {
        $basePrice = $this->product->price ?? 0;
        $extra = 0;
        $elements = $this->recipe['elements'] ?? [];

        $extra += count($elements['text'] ?? []) * 50;
        $extra += count($elements['shapes'] ?? []) * 30;
        $extra += count($elements['logos'] ?? []) * 150;

        if (isset($this->recipe['features']['led_lighting']) && $this->recipe['features']['led_lighting']) {
            $extra += 500;
        }

        return $basePrice + $extra;
    }
}
