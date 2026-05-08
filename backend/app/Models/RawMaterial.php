<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'raw_material_id';

    protected $fillable = [
        'name',
        'image_path',
        'supplier_id',
        'cost_per_unit',
        'stock_quantity',
        'units_on_display',
        'units_sponsored',
        'units_damaged',
        'units_consumed',
        'low_stock_threshold',
        'unit',
        'description',
        'department',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }
}
