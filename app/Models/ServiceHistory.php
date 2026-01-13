<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceHistory extends Model
{
    use HasFactory;

    protected $table = 'service_histories';

    protected $fillable = [
        'uid',
        'service_plan_id',
        'hm_at_service',
        'service_date',
        'service_type',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'hm_at_service' => 'decimal:2',
        'service_date' => 'date',
    ];

    // Relationships
    public function servicePlan()
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
