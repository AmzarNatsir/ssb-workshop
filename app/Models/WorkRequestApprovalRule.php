<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class WorkRequestApprovalRule extends Model
{
    protected $fillable = [
        'category',
        'wr_type',
        'role_id',
        'step_order',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
