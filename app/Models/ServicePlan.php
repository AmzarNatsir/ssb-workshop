<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_plans';

    protected $fillable = [
        'uid',
        'equipment_id',
        'hm_ps_sebelumnya',
        'hm_actual',
        'wh_project',
        'overdue',
        'ps_berikutnya',
        'plan_date',
        'service_type',
        'status',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'hm_ps_sebelumnya' => 'decimal:2',
        'hm_actual' => 'decimal:2',
        'wh_project' => 'decimal:2',
        'overdue' => 'decimal:2',
        'ps_berikutnya' => 'decimal:2',
        'plan_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function equipment()
    {
        return $this->belongsTo(Equipments::class, 'equipment_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function history()
    {
        return $this->hasOne(ServiceHistory::class, 'service_plan_id');
    }

    public function workOrder()
    {
        return $this->hasOne(WorkOrder::class, 'service_plan_id');
    }

    /**
     * Check if a Work Order can be created for this plan
     */
    public function canCreateWorkOrder()
    {
        // One Work Order per Plan Service
        if ($this->workOrder()->exists()) {
            return false;
        }

        // Only active plans
        if (!$this->is_active || !in_array($this->status, ['PLANNED', 'OVERDUE'])) {
            return false;
        }

        $today = now()->startOfDay();
        $planDate = $this->plan_date ? \Carbon\Carbon::parse($this->plan_date)->startOfDay() : null;
        $daysToPlan = $planDate ? $today->diffInDays($planDate, false) : 999;

        // Formula: Overdue > 0 OR Plan Date - Today <= 7 days
        return ($this->overdue > 0 || ($planDate && $daysToPlan <= 7));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'OVERDUE');
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'PLANNED');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'PLANNED' => '<span class="badge bg-success">PLANNED</span>',
            'OVERDUE' => '<span class="badge bg-danger">OVERDUE</span>',
            'COMPLETED' => '<span class="badge bg-secondary">COMPLETED</span>',
            'CANCELLED' => '<span class="badge bg-warning">CANCELLED</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light">UNKNOWN</span>';
    }

    public function getOverdueColorAttribute()
    {
        return $this->overdue > 0 ? 'text-danger' : 'text-success';
    }
}
