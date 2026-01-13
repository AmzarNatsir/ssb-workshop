<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    protected $table = 'common_meter_reading';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
