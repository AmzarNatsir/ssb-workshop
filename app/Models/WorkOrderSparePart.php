<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderSparePart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'part_name',
        'qty_requested',
        'qty_issued', // Actual issued
        'qty_returned',
        'status', // PENDING, APPROVED, ISSUED, REJECTED
        'return_status', // NONE, PENDING, VALIDATED, APPROVED
        'issued_by',
        'issued_at',
        'validated_by',
        'validated_at',
        'returned_by',
        'returned_at',
        'return_validated_by',
        'return_validated_at',
        'return_approved_by',
        'return_approved_at',
        'notes',
        'picking_mechanic',
        'return_reason',
        'return_rejected_by',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function returner()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
