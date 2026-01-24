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
}
