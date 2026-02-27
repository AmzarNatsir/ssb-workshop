<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class UnitRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uid',
        'request_no',
        'project_id',
        'project_name',
        'status',
        'requested_by',
        'ga_validated_by',
        'om_approved_by',
        'remarks',
        'total_units',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uid = (string) Str::uuid();
            if (empty($model->request_no)) {
                $model->request_no = self::generateRequestNumber();
            }
        });
    }

    public static function generateRequestNumber()
    {
        $lastRequest = self::orderBy('id', 'desc')->first();
        $sequence = $lastRequest ? ((int) substr($lastRequest->request_no, 2)) + 1 : 1;
        return 'UR' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function gaValidatedBy()
    {
        return $this->belongsTo(User::class, 'ga_validated_by');
    }

    public function omApprovedBy()
    {
        return $this->belongsTo(User::class, 'om_approved_by');
    }

    public function items()
    {
        return $this->hasMany(UnitRequestItem::class);
    }
}
