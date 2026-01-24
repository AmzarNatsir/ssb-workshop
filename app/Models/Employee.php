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
    ];

    public function toolCards()
    {
        return $this->hasMany(ToolCard::class);
    }
}
