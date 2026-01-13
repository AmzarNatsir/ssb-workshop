<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    protected $table = 'common_unit_type';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
