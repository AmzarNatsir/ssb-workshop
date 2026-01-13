<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $fillable = [
        'uid',
        'name',
        'email',
        'phone',
        'address',
    ];

}
