<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InspectionForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inspection_forms';

    protected $fillable = [
        'uid',
        'form_code',
        'form_title',
        'form_description',
        'version',
        'status',
        'applicable_unit_category',
        'applicable_unit_ids',
        'created_by',
    ];

    protected $casts = [
        'applicable_unit_ids' => 'array',
    ];

    /**
     * Boot function to auto-generate UID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
            if (empty($model->form_code)) {
                $model->form_code = self::generateFormCode();
            }
        });
    }

    /**
     * Generate unique form code
     */
    public static function generateFormCode()
    {
        $prefix = 'INSP-FORM-';
        $lastForm = self::where('form_code', 'LIKE', $prefix . '%')
            ->withTrashed()
            ->orderBy('form_code', 'desc')
            ->first();

        $sequence = 1;
        if ($lastForm) {
            $lastNo = str_replace($prefix, '', $lastForm->form_code);
            $sequence = intval($lastNo) + 1;
        }
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function sections()
    {
        return $this->hasMany(InspectionSection::class, 'form_id')->orderBy('section_order');
    }

    public function schedules()
    {
        return $this->hasMany(InspectionSchedule::class, 'form_id');
    }

    public function results()
    {
        return $this->hasMany(InspectionResult::class, 'form_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'PUBLISHED');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    public function scopeForUnitCategory($query, $category)
    {
        return $query->where('applicable_unit_category', $category)
            ->orWhereNull('applicable_unit_category');
    }

    /**
     * Business Methods
     */
    public function publish()
    {
        $this->status = 'PUBLISHED';
        $this->save();
        return $this;
    }

    public function archive()
    {
        $this->status = 'ARCHIVED';
        $this->save();
        return $this;
    }

    public function createRevision()
    {
        $newVersion = floatval($this->version) + 0.1;
        
        $newForm = $this->replicate();
        $newForm->version = number_format($newVersion, 1);
        $newForm->status = 'DRAFT';
        $newForm->uid = (string) Str::uuid();
        $newForm->form_code = self::generateFormCode();
        $newForm->save();

        // Copy sections and items
        foreach ($this->sections as $section) {
            $newSection = $section->replicate();
            $newSection->form_id = $newForm->id;
            $newSection->save();

            foreach ($section->items as $item) {
                $newItem = $item->replicate();
                $newItem->section_id = $newSection->id;
                $newItem->save();
            }
        }

        return $newForm;
    }
}
