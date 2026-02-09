<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolIncidentInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_incident_report_id',
        'period_month',
        'period_year',
        'amount',
        'status',
        'payroll_reference',
    ];

    const STATUS_PENDING = 'PENDING';
    const STATUS_PAID = 'PAID';

    public function incidentReport()
    {
        return $this->belongsTo(ToolIncidentReport::class, 'tool_incident_report_id');
    }
}
