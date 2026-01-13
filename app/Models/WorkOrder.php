<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_orders';

    protected $fillable = [
        'uid',
        'work_order_no',
        'wo_type',
        'service_category',
        'maintenance_type',
        'service_plan_id',
        'work_request_id',
        'equipment_id',
        'equipment_id',
        'priority',
        'work_date',
        'status',
        'assigned_to',
        'description',
        'created_by',
    ];

    /**
     * Get the service plan that owns the work order.
     */
    public function servicePlan()
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    /**
     * Get the work request that owns the work order.
     */
    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class, 'work_request_id');
    }

    /**
     * Get the equipment that owns the work order.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipments::class, 'equipment_id');
    }

    /**
     * Get the user who created the work order.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user assigned to the work order.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the spare parts for the work order.
     */
    public function spareParts()
    {
        return $this->hasMany(WorkOrderSparePart::class);
    }

    /**
     * Get the activities for the work order.
     */
    public function activities()
    {
        return $this->hasMany(MechanicActivity::class);
    }

    /**
     * Generate a unique Work Order number.
     * Scheduled: WOSCH + 6 digits
     * Manual: WO + YYYYMM + 4 digits
     */
    public static function generateWorkOrderNo($isScheduled = false)
    {
        if ($isScheduled) {
            $prefix = 'WOSCH';
            $lastWO = self::where('work_order_no', 'LIKE', $prefix . '%')
                ->withTrashed()
                ->orderBy('work_order_no', 'desc')
                ->first();

            $sequence = 1;
            if ($lastWO) {
                $lastNo = str_replace($prefix, '', $lastWO->work_order_no);
                $sequence = intval($lastNo) + 1;
            }
            return $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);
        }

        $prefix = 'WO-' . date('Ym');
        $lastWO = self::where('work_order_no', 'LIKE', $prefix . '-%')
            ->withTrashed()
            ->orderBy('work_order_no', 'desc')
            ->first();

        if ($lastWO) {
            // Updated to handle both styles of hyphenation if they exist
            $parts = explode('-', $lastWO->work_order_no);
            $lastNumber = intval(end($parts));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }
}
