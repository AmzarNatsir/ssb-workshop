<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'common_category';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
