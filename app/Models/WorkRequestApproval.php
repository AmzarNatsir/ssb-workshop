<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class WorkRequestApproval extends Model
{
    protected $fillable = [
        'work_request_id',
        'user_id',
        'role_id',
        'status',
        'comment',
        'step_order',
    ];

    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
