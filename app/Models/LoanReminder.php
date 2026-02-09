<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanReminder extends Model
{
    use HasFactory;

    protected $table = 'loan_reminders';

    protected $fillable = [
        'loan_transaction_id',
        'reminder_type',
        'sent_at',
        'sent_to',
        'status',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function loanTransaction()
    {
        return $this->belongsTo(LoanTransaction::class);
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', 'Sent');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'Failed');
    }
}
