<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    protected $table = 'common_documents';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
