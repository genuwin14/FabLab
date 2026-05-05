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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'custom_design_id', 'custom_design_id');
    }

    /**
     * Look up the Texture record referenced by this design's recipe (if any).
     */
    public function texture()
    {
        $textureId = $this->recipe['texture_id'] ?? null;
        if (!$textureId) return null;
        return Texture::find($textureId);
    }

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

        $texture = $this->texture();
        if ($texture && $texture->price_modifier) {
            $extra += (float) $texture->price_modifier;
        }

        return $basePrice + $extra;
    }
}
