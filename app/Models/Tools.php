<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\common\Racks;
use App\Models\common\ToolType;

class Tools extends Model
{
    protected $table = 'tools';
    protected $fillable = [
        'uid',
        'code',
        'name',
        'description',
        'acquisition_date',
        'acquisition_cost',
        'quantity',
        'min_quantity',
        'image',
        'racks_id',
        'tool_type_id',
        'status_id',
        'print_date',
        'print_barcode',
    ];

    public function racks()
    {
        return $this->belongsTo(Racks::class);
    }

    public function tool_type()
    {
        return $this->belongsTo(ToolType::class);
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\common\Status::class);
    }
}
