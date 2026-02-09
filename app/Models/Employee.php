<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'nik',
        'name',
        'position',
        'department',
        'photo_path',
    ];

    public function toolCards()
    {
        return $this->hasMany(ToolCard::class);
    }

    public function loanTransactions()
    {
        return $this->hasManyThrough(
            LoanTransaction::class,
            ToolCard::class,
            'employee_id',
            'tool_card_id'
        );
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('id', '>', 0); // All employees are active by default
    }
}
