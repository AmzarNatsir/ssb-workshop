<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\common\Category;
use App\Models\common\Merk;
use App\Models\common\UnitType;
use App\Models\common\Status;
use App\Models\common\MeterReading;
use App\Models\common\OwnershipMode;
use App\Models\Supplier;
use App\Models\ref\PeriodicServiceType;

class Equipments extends Model
{
    use HasFactory;
    protected $table = 'equipments';
    protected $fillable = [
        'uid',
        'code',
        'name',
        'description',
        'engine_no',
        'chassis_no',
        'plate_number',
        'prodution_year',
        'warranty_date',
        'purchase_date',
        'purchase_price',
        'internal_estimated_price',
        'market_price',
        'equipment_status_id',
        'status_information',
        'project_id',
        'project_status',
        'wh_per_project',
        'meter_reading_id',
        'supplier_id',
        'pic_unit',
        'ownership_mode_id',
        'category_id',
        'merk_id',
        'unit_type_id',
        'periodic_service_type_id',
        'image',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class, 'equipment_status_id', 'id');
    }

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class, 'meter_reading_id');
    }

    public function ownershipMode()
    {
        return $this->belongsTo(OwnershipMode::class, 'ownership_mode_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function merk()
    {
        return $this->belongsTo(Merk::class, 'merk_id', 'id');
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(EquipmentDocument::class, 'equipment_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function periodicServiceType()
    {
        return $this->belongsTo(PeriodicServiceType::class, 'periodic_service_type_id');
    }

    public function servicePlans()
    {
        return $this->hasMany(\App\Models\ServicePlan::class, 'equipment_id');
    }

    public function activeServicePlan()
    {
        return $this->hasOne(\App\Models\ServicePlan::class, 'equipment_id')->where('is_active', true);
    }
}
