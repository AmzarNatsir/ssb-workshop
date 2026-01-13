<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipments;

class PartRequirement extends Model
{
    protected $table = 'part_requirements';
    protected $fillable = [
        'uid',
        'equipment_id',
        'description',
        'status',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipments::class, 'equipment_id');
    }

    public function details()
    {
        return $this->hasMany(PartRequirementDetail::class, 'part_requirement_id');
    }
}
