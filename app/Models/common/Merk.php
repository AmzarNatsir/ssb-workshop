<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class Merk extends Model
{
    protected $table = 'common_merk';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
