<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Model;

class ToolType extends Model
{
    protected $table = 'common_tool_type';
    protected $fillable = [
        'uid',
        'name',
        'description',
        'slug',
    ];
}
