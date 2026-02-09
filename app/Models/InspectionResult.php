<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InspectionResult extends Model
{
    use HasFactory;

    protected $table = 'inspection_results';

    protected $fillable = [
        'uid',
        'result_code',
        'schedule_id',
        'form_id',
        'unit_id',
        'inspector_id',
        'inspection_date',
        'start_time',
        'end_time',
        'overall_status',
        'unit_ready_for_operation',
        'supervisor_approval_id',
        'supervisor_approval_at',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'unit_ready_for_operation' => 'boolean',
        'supervisor_approval_at' => 'datetime',
    ];

    /**
     * Boot function to auto-generate UID and result code
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
            if (empty($model->result_code)) {
                $model->result_code = self::generateResultCode();
            }
        });
    }

    /**
     * Generate unique result code
     */
    public static function generateResultCode()
    {
        $prefix = 'INSP-' . date('Ym') . '-';
        $lastResult = self::where('result_code', 'LIKE', $prefix . '%')
            ->orderBy('result_code', 'desc')
            ->first();

        if ($lastResult) {
            $parts = explode('-', $lastResult->result_code);
            $lastNumber = intval(end($parts));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Relationships
     */
    public function schedule()
    {
        return $this->belongsTo(InspectionSchedule::class, 'schedule_id');
    }

    public function form()
    {
        return $this->belongsTo(InspectionForm::class, 'form_id');
    }

    public function unit()
    {
        return $this->belongsTo(Equipments::class, 'unit_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_approval_id');
    }

    public function resultItems()
    {
        return $this->hasMany(InspectionResultItem::class, 'result_id');
    }

    public function sectionResults()
    {
        return $this->hasMany(InspectionSectionResult::class, 'result_id');
    }

    /**
     * Business Methods
     */
    
    /**
     * Calculate overall status based on result items
     * @return string - 'PASS' or 'FAIL'
     */
    public function calculateOverallStatus()
    {
        $hasCritical = false;
        $hasFail = false;

        foreach ($this->resultItems as $resultItem) {
            $item = $resultItem->item;
            $value = $resultItem->value_option ?? $resultItem->value_number;

            // Check for fail conditions
            if (in_array($value, ['Repair', 'Replace', 'Fail', 'Faulty', 'No'])) {
                $hasFail = true;
            }

            // Check threshold
            if ($item->input_type === 'NUMBER' && $resultItem->value_number !== null) {
                $thresholdStatus = $item->validateThreshold($resultItem->value_number);
                if ($thresholdStatus === 'CRITICAL') {
                    $hasCritical = true;
                }
            }
        }

        return ($hasCritical || $hasFail) ? 'FAIL' : 'PASS';
    }

    /**
     * Check if requires supervisor approval
     * @return bool
     */
    public function requiresSupervisorApproval()
    {
        return $this->overall_status === 'FAIL';
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('overall_status', 'PENDING');
    }

    public function scopeForInspector($query, $inspectorId)
    {
        return $query->where('inspector_id', $inspectorId);
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}
