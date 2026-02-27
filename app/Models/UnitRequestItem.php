<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_request_id',
        'equipment_id',
        'unit_name',
        'mechanic_id',
        'work_request_id',
        'status',
        'p2h_result_id',
        'operator_id',
        'hm_start',
        'km_start',
        'fuel_level',
        'refuel_status',
        'attachment_paths',
    ];

    protected $casts = [
        'attachment_paths' => 'array',
    ];

    public function request()
    {
        return $this->belongsTo(UnitRequest::class, 'unit_request_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipments::class, 'equipment_id');
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class, 'work_request_id');
    }

    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }

    public function p2hResult()
    {
        return $this->belongsTo(InspectionResult::class, 'p2h_result_id');
    }
}
