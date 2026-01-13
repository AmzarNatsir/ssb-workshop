<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WorkRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'wr_no',
        'category',
        'service_plan_id',
        'equipment_id',
        'operator_name',
        'hm_km',
        'asset_condition',
        'trouble_description',
        'location',
        'type',
        'status',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uid = (string) Str::uuid();
            if (empty($model->wr_no)) {
                $model->wr_no = self::generateWRNumber();
            }
        });
    }

    public static function generateWRNumber()
    {
        $lastWR = self::orderBy('id', 'desc')->first();
        $sequence = $lastWR ? ((int) substr($lastWR->wr_no, 2)) + 1 : 1;
        return 'WR' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipments::class, 'equipment_id');
    }

    public function servicePlan()
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals()
    {
        return $this->hasMany(WorkRequestApproval::class);
    }

    public function workOrder()
    {
        return $this->hasOne(WorkOrder::class, 'work_request_id'); // We'll need to add this column to work_orders later or link via middle table
    }

    public function items()
    {
        return $this->hasMany(WorkRequestItem::class, 'work_request_id');
    }
}
