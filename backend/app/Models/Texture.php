<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Texture extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'texture_id';

    protected $fillable = [
        'name',
        'image_path',
        'description'
    ];
}
