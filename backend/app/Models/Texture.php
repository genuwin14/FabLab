<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Texture extends Model
{
    use SoftDeletes;
    use Concerns\TracksStockLevel;
    use Concerns\HasStoredImage;

    protected $primaryKey = 'texture_id';

    protected string $stockColumn = 'stock_quantity';
    protected string $stockItemType = 'Texture';

    protected string $imageColumn = 'image_path';
    protected string $imageDirectory = 'textures';

    protected $appends = ['image_url'];

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

    /**
     * The shape the customizer's JavaScript wants a texture in.
     *
     * Any page that renders a 3D design — the studio, My Designs, the admin and
     * staff order inspectors — has to ship this catalogue, because a saved
     * recipe stores only a texture_id and the renderer resolves the image from
     * here. Without it every previewed model comes out blank white.
     *
     * @param  \Illuminate\Support\Collection<int, self>|null  $textures
     */
    public static function customizerPayload($textures = null)
    {
        // Retired textures are included: this catalogue exists to resolve
        // designs that were already saved, and those keep their finish.
        $textures ??= self::withTrashed()->get();

        return $textures->map(fn($texture) => [
            'id' => $texture->texture_id,
            'name' => $texture->name,
            'image_path' => $texture->image_url,
            'price_modifier' => (float) ($texture->price_modifier ?? 0),
        ])->values();
    }
}
