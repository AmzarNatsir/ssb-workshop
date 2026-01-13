<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class Racks extends Model
{
    protected $table = 'common_racks';
    protected $fillable = [
        'uid',
        'rack_code',
        'name',
        'location',
        'responsible_person',
        'slug',
    ];
    public function tools()
    {
        return $this->hasMany(\App\Models\Tools::class, 'racks_id');
    }
}
