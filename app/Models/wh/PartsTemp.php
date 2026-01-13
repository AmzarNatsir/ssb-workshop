<?php

namespace App\Models\wh;

use Illuminate\Database\Eloquent\Model;

class PartsTemp extends Model
{
    protected $table = 'parts_temp';
    protected $fillable = [
        'uid',
        'name',
        'price',
        'discount',
        'current_stock',
    ];
}
