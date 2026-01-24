<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionSection extends Model
{
    use HasFactory;

    protected $table = 'inspection_sections';

    protected $fillable = [
        'form_id',
        'section_order',
        'section_title',
        'section_description',
    ];

    /**
     * Relationships
     */
    public function form()
    {
        return $this->belongsTo(InspectionForm::class, 'form_id');
    }

    public function items()
    {
        return $this->hasMany(InspectionItem::class, 'section_id')->orderBy('item_order');
    }
}
