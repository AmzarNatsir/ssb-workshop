<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoanTransaction extends Model
{
    use HasFactory;

    protected $table = 'loan_transactions';

    protected $fillable = [
        'loan_number',
        'tool_card_id',
        'tool_id',
        'admin_id',
        'requirement_type',
        'requirement_reference',
        'requirement_notes',
        'loan_date',
        'expected_return_date',
        'actual_return_date',
        'status',
        'return_condition',
        'return_notes',
    ];

    protected $casts = [
        'loan_date' => 'datetime',
        'expected_return_date' => 'datetime',
        'actual_return_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->loan_number)) {
                $model->loan_number = self::generateLoanNumber();
            }
            
            if (empty($model->loan_date)) {
                $model->loan_date = now();
            }
            
            if (empty($model->expected_return_date)) {
                $model->expected_return_date = self::calculateExpectedReturn($model->loan_date);
            }
        });
    }

    // Relationships
    public function toolCard()
    {
        return $this->belongsTo(ToolCard::class);
    }

    public function employee()
    {
        return $this->hasOneThrough(
            Employee::class,
            ToolCard::class,
            'id',
            'id',
            'tool_card_id',
            'employee_id'
        );
    }

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tool_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function reminders()
    {
        return $this->hasMany(LoanReminder::class);
    }

    public function incidentReport()
    {
        return $this->hasOne(ToolIncidentReport::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'Active')
                     ->where('expected_return_date', '<', now());
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->whereHas('toolCard', function($q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        });
    }

    public function scopeByTool($query, $toolId)
    {
        return $query->where('tool_id', $toolId);
    }

    // Methods
    public function isOverdue()
    {
        return $this->status === 'Active' && $this->expected_return_date < now();
    }

    public function getTimeRemaining()
    {
        if ($this->status !== 'Active') {
            return null;
        }

        $now = Carbon::now();
        $expectedReturn = Carbon::parse($this->expected_return_date);
        
        if ($expectedReturn < $now) {
            return [
                'overdue' => true,
                'duration' => $now->diff($expectedReturn)->format('%d days %h hours'),
                'hours' => $now->diffInHours($expectedReturn)
            ];
        }

        return [
            'overdue' => false,
            'duration' => $expectedReturn->diff($now)->format('%d days %h hours'),
            'hours' => $expectedReturn->diffInHours($now)
        ];
    }

    public static function generateLoanNumber()
    {
        $date = now()->format('Ymd');
        $prefix = "LN-{$date}-";
        
        // Get today's count
        $todayCount = self::where('loan_number', 'like', $prefix . '%')
                          ->count();
        
        $sequence = str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $sequence;
    }

    /**
     * Calculate expected return date based on system settings
     */
    public static function calculateExpectedReturn()
    {
        $durationHours = (int) SystemSetting::get('loan_duration_hours', 24);
        return now()->addHours($durationHours);
    }
}
