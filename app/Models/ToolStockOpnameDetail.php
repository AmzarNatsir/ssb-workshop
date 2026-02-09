<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolStockOpnameDetail extends Model
{
    protected $fillable = [
        'opname_id',
        'tool_id',
        'physical_exist',
        'physical_condition',
        'physical_location',
        'system_status',
        'mismatch',
        'notes',
    ];

    const EXIST_YES = 'EXISTS';
    const EXIST_NO = 'NOT_EXISTS';

    const CONDITION_GOOD = 'GOOD';
    const CONDITION_DAMAGE = 'DAMAGE';
    const CONDITION_LOST = 'LOST';

    const SYSTEM_STATUS_AVAILABLE = 'AVAILABLE';
    const SYSTEM_STATUS_BORROWED = 'BORROWED';
    const SYSTEM_STATUS_DAMAGED = 'DAMAGED';
    const SYSTEM_STATUS_LOST = 'LOST';

    public function opname()
    {
        return $this->belongsTo(ToolStockOpname::class, 'opname_id');
    }

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tool_id');
    }
}
