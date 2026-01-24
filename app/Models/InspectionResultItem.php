<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionResultItem extends Model
{
    use HasFactory;

    protected $table = 'inspection_result_items';

    protected $fillable = [
        'result_id',
        'item_id',
        'value_text',
        'value_number',
        'value_option',
        'image_path',
        'notes',
        'triggered_action',
    ];

    protected $casts = [
        'value_number' => 'decimal:2',
        'triggered_action' => 'array',
    ];

    /**
     * Relationships
     */
    public function result()
    {
        return $this->belongsTo(InspectionResult::class, 'result_id');
    }

    public function item()
    {
        return $this->belongsTo(InspectionItem::class, 'item_id');
    }
}
