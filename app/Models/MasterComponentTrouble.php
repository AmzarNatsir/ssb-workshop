<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterComponentTrouble extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'component_name',
        'description',
    ];
}
