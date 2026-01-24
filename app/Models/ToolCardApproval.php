<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolCardApproval extends Model
{
    use HasFactory;

    protected $table = 'tool_card_approvals';

    protected $fillable = [
        'tool_card_id',
        'approver_id',
        'level',
        'status',
        'notes',
    ];

    public function toolCard()
    {
        return $this->belongsTo(ToolCard::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
