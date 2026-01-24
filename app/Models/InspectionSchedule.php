<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InspectionSchedule extends Model
{
    use HasFactory;

    protected $table = 'inspection_schedules';

    protected $fillable = [
        'form_id',
        'unit_id',
        'frequency',
        'schedule_time',
        'start_date',
        'end_date',
        'is_active',
        'last_generated_at',
        'next_generation_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_generated_at' => 'datetime',
        'next_generation_at' => 'datetime',
    ];

    /**
     * Boot function to calculate next_generation_at
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->next_generation_at)) {
                // First inspection should be generated on the start date
                $model->next_generation_at = $model->start_date;
            }
        });
    }

    /**
     * Relationships
     */
    public function form()
    {
        return $this->belongsTo(InspectionForm::class, 'form_id');
    }

    public function unit()
    {
        return $this->belongsTo(Equipments::class, 'unit_id');
    }

    public function results()
    {
        return $this->hasMany(InspectionResult::class, 'schedule_id');
    }

    /**
     * Business Methods
     */
    
    /**
     * Calculate next generation date based on frequency
     * @param Carbon|null $fromDate
     * @return Carbon
     */
    public function calculateNextGenerationDate($fromDate = null)
    {
        $baseDate = $fromDate ?? Carbon::parse($this->start_date);

        switch ($this->frequency) {
            case 'DAILY':
                return $baseDate->addDay();
            case 'WEEKLY':
                return $baseDate->addWeek();
            case 'MONTHLY':
                return $baseDate->addMonth();
            default:
                return $baseDate->addDay();
        }
    }

    /**
     * Generate next inspection
     * @return InspectionResult
     */
    public function generateNextInspection()
    {
        $result = InspectionResult::create([
            'uid' => \Illuminate\Support\Str::uuid(),
            'result_code' => InspectionResult::generateResultCode(),
            'schedule_id' => $this->id,
            'form_id' => $this->form_id,
            'unit_id' => $this->unit_id,
            'inspector_id' => null, // Will be assigned later
            'inspection_date' => now(),
            'overall_status' => 'PENDING',
        ]);

        // Update schedule
        $this->last_generated_at = now();
        $this->next_generation_at = $this->calculateNextGenerationDate(now());
        $this->save();

        return $result;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueForGeneration($query)
    {
        return $query->where('is_active', true)
            ->where('next_generation_at', '<=', now())
            ->whereNull('end_date')
            ->orWhere('end_date', '>=', now());
    }
}
