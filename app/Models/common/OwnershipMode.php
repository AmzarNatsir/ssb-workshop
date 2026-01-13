<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class OwnershipMode extends Model
{
    protected $table = 'common_ownership_mode';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
