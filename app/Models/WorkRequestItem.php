<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_request_id',
        'part_name',
        'qty',
        'price',
        'unit',
    ];

    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class);
    }
}
