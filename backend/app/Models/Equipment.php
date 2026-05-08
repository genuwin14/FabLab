<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipment';
    protected $primaryKey = 'equipment_id';

    protected $fillable = [
        'name',
        'brand',
        'property_no',
        'date_acquired',
        'cost',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'cost' => 'decimal:2',
    ];
}
