<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolAdjustment extends Model
{
    protected $fillable = [
        'tool_id',
        'opname_id',
        'adjustment_type',
        'old_value',
        'new_value',
        'reason',
        'adjusted_by',
    ];

    const TYPE_STATUS = 'STATUS';
    const TYPE_LOCATION = 'LOCATION';
    const TYPE_STOCK = 'STOCK';

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tool_id');
    }

    public function opname()
    {
        return $this->belongsTo(ToolStockOpname::class, 'opname_id');
    }

    public function adjuster()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
