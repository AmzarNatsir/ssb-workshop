<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'common_status';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
