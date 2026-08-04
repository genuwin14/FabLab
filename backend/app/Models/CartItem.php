<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'user_id',
        'product_id',
        'custom_design_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function customDesign()
    {
        return $this->belongsTo(CustomDesign::class, 'custom_design_id', 'custom_design_id');
    }

    /**
     * The identifier the cart UI uses for a line. Two designs of the same
     * product are different things to make, so they stay separate lines.
     */
    public function lineKey(): string
    {
        return self::keyFor($this->product_id, $this->custom_design_id);
    }

    public static function keyFor(int|string $productId, int|string|null $designId): string
    {
        return $designId ? "{$productId}_custom_{$designId}" : (string) $productId;
    }

    /**
     * Split a line key back into its parts.
     *
     * @return array{0: int, 1: int|null}
     */
    public static function parseKey(string $key): array
    {
        if (str_contains($key, '_custom_')) {
            [$productId, $designId] = explode('_custom_', $key, 2);

            return [(int) $productId, (int) $designId];
        }

        return [(int) $key, null];
    }
}
