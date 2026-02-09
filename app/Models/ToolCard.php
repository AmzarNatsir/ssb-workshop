<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ToolCard extends Model
{
    use HasFactory;

    protected $table = 'tool_cards';

    protected $fillable = [
        'uid',
        'employee_id',
        'access_level',
        'code_type',
        'tool_categories',
        'status',
        'current_approval_level',
        'barcode_path',
        'created_by',
    ];

    protected $casts = [
        'tool_categories' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uid)) {
                // Fetch employee NIK
                $employee = Employee::find($model->employee_id);
                if ($employee) {
                    $model->uid = $employee->nik;
                } else {
                    // Fallback if employee somehow not found (should be validated before)
                    $model->uid = (string) Str::uuid();
                }
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals()
    {
        return $this->hasMany(ToolCardApproval::class);
    }

    public function loanTransactions()
    {
        return $this->hasMany(LoanTransaction::class);
    }

    public function getActiveLoans()
    {
        return $this->loanTransactions()->where('status', 'Active')->get();
    }

    public function canBorrowTool($tool)
    {
        // Check if tool card is approved
        if ($this->status !== 'APPROVED_FINAL') {
            return [
                'can_borrow' => false,
                'message' => 'Tool card is not approved'
            ];
        }

        // Check access level (hierarchical: 3 can access all, 2 can access 1-2, 1 only 1)
        if ($this->access_level < $tool->required_access_level) {
            return [
                'can_borrow' => false,
                'message' => 'Insufficient access level for this tool'
            ];
        }

        return [
            'can_borrow' => true,
            'message' => 'Access granted'
        ];
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED_FINAL');
    }
}
