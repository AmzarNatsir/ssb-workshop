<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionSectionResult extends Model
{
    protected $fillable = [
        'result_id',
        'section_id',
        'image_path',
    ];

    public function result()
    {
        return $this->belongsTo(InspectionResult::class, 'result_id');
    }

    public function section()
    {
        return $this->belongsTo(InspectionSection::class, 'section_id');
    }
}
