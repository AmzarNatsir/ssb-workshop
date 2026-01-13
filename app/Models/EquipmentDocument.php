<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\common\Documents;

class EquipmentDocument extends Model
{
    protected $table = 'equipment_document';
    protected $fillable = [
        'equipment_id',
        'document_type_id',
        'document_path',
    ];

    public function documentType()
    {
        return $this->belongsTo(Documents::class, 'document_type_id', 'id');
    }
}
