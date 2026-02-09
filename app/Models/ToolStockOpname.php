<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ToolStockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_no',
        'period',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_FINAL = 'FINAL';

    const PERIOD_MONTHLY = 'MONTHLY';
    const PERIOD_QUARTERLY = 'QUARTERLY';
    const PERIOD_YEARLY = 'YEARLY';

    public function details()
    {
        return $this->hasMany(ToolStockOpnameDetail::class, 'opname_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function adjustments()
    {
        return $this->hasMany(ToolAdjustment::class, 'opname_id');
    }

    public static function generateOpnameNumber($period)
    {
        $prefix = ($period == self::PERIOD_MONTHLY) ? 'OPN-M' : (($period == self::PERIOD_QUARTERLY) ? 'OPN-Q' : 'OPN-Y');
        $datePart = date('my'); // e.g., 0226 for Feb 2026
        
        $latest = self::where('opname_no', 'like', $prefix . '-' . $datePart . '-%')
                      ->orderBy('opname_no', 'desc')
                      ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->opname_no, -3);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return $prefix . '-' . $datePart . '-' . $newNum;
    }
}
