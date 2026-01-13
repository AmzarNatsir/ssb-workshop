<?php

namespace App\Models\ref;

use Illuminate\Database\Eloquent\Model;

class PeriodicServiceType extends Model
{
    protected $table = 'periodic_service_type';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
