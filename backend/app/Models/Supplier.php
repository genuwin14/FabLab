<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address'
    ];

    public function rawMaterials()
    {
        return $this->hasMany(RawMaterial::class, 'supplier_id', 'supplier_id');
    }
}
