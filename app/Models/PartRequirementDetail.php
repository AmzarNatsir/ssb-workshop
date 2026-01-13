<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\wh\PartsTemp;

class PartRequirementDetail extends Model
{
    protected $table = 'part_requirement_details';
    protected $fillable = [
        'uid',
        'part_requirement_id',
        'part_id',
        'quantity',
    ];

    public function partRequirement()
    {
        return $this->belongsTo(PartRequirement::class, 'part_requirement_id');
    }

    public function part()
    {
        return $this->belongsTo(PartsTemp::class, 'part_id');
    }
}
