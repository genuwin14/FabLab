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
        'supplier_id',
        'cost_per_unit',
        'stock_quantity',
        'low_stock_threshold',
        'unit',
        'description'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }
}
