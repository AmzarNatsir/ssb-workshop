<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolIncidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_number',
        'incident_type',
        'tool_id',
        'loan_transaction_id',
        'employee_id',
        'incident_date',
        'description',
        'chronology',
        'last_location',
        'last_condition',
        'replacement_cost',
        'installment_months',
        'monthly_installment_amount',
        'installment_start_month',
        'installment_end_month',
        'status',
        'document_path',
        'created_by',
        'approved_mtn_by',
        'approved_mtn_at',
        'approved_hr_by',
        'approved_hr_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'installment_start_month' => 'date',
        'installment_end_month' => 'date',
        'approved_mtn_at' => 'datetime',
        'approved_hr_at' => 'datetime',
    ];

    // Status Constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_APPROVED_MTN = 'APPROVED_MTN';
    const STATUS_APPROVED_HR = 'APPROVED_HR';
    const STATUS_REJECTED = 'REJECTED';

    // Type Constants
    const TYPE_MISSING = 'MISSING';
    const TYPE_DAMAGED = 'DAMAGED';

    /**
     * Generate incident number
     * Format: {Seq}/BAH-Tools/{MM}-{YYYY} or null/BAR-Tools/...
     */
    public static function generateIncidentNumber($type)
    {
        $prefix = $type === self::TYPE_MISSING ? 'BAH-Tools' : 'BAR-Tools';
        $month = date('m');
        $year = date('Y');
        
        $latest = self::where('incident_type', $type)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->max('id');
            
        $sequence = str_pad(($latest ? $latest + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        return "{$sequence}/{$prefix}/{$month}-{$year}";
    }

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tool_id');
    }

    public function loanTransaction()
    {
        return $this->belongsTo(LoanTransaction::class, 'loan_transaction_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function installments()
    {
        return $this->hasMany(ToolIncidentInstallment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
