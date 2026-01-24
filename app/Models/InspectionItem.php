<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    use HasFactory;

    protected $table = 'inspection_items';

    protected $fillable = [
        'section_id',
        'item_order',
        'item_code',
        'item_name',
        'item_description',
        'input_type',
        'is_required',
        'threshold_warning',
        'threshold_critical',
        'conditional_logic',
        'auto_action',
        'instruction',
        'reference_image',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'threshold_warning' => 'decimal:2',
        'threshold_critical' => 'decimal:2',
        'conditional_logic' => 'array',
        'auto_action' => 'array',
    ];

    /**
     * Relationships
     */
    public function section()
    {
        return $this->belongsTo(InspectionSection::class, 'section_id');
    }

    public function resultItems()
    {
        return $this->hasMany(InspectionResultItem::class, 'item_id');
    }

    /**
     * Business Methods
     */
    
    /**
     * Evaluate if this item should be shown based on conditional logic
     * @param array $answers - Array of item_id => value pairs
     * @return bool
     */
    public function evaluateConditionalLogic($answers)
    {
        if (empty($this->conditional_logic)) {
            return true; // No condition, always show
        }

        // Example conditional_logic format:
        // {"show_if": {"item_id": 5, "value": "Good"}}
        
        if (isset($this->conditional_logic['show_if'])) {
            $condition = $this->conditional_logic['show_if'];
            $requiredItemId = $condition['item_id'];
            $requiredValue = $condition['value'];

            if (!isset($answers[$requiredItemId])) {
                return false; // Required item not answered yet
            }

            return $answers[$requiredItemId] == $requiredValue;
        }

        return true;
    }

    /**
     * Validate if value exceeds threshold
     * @param float $value
     * @return string|null - 'WARNING', 'CRITICAL', or null
     */
    public function validateThreshold($value)
    {
        if ($this->input_type !== 'NUMBER') {
            return null;
        }

        if ($this->threshold_critical !== null && $value >= $this->threshold_critical) {
            return 'CRITICAL';
        }

        if ($this->threshold_warning !== null && $value >= $this->threshold_warning) {
            return 'WARNING';
        }

        return null;
    }

    /**
     * Check if auto-action should be triggered
     * @param mixed $value
     * @return bool
     */
    public function shouldTriggerAutoAction($value)
    {
        if (empty($this->auto_action)) {
            return false;
        }

        // Example auto_action format:
        // {"trigger_on": ["Repair", "Replace"], "action": "CREATE_WR", "priority": "HIGH"}
        
        if (isset($this->auto_action['trigger_on'])) {
            $triggerValues = $this->auto_action['trigger_on'];
            
            // Normalize input value
            $normalizedValue = is_string($value) ? strtolower(trim($value)) : $value;
            
            // Normalize trigger values
            $normalizedTriggers = array_map(function($val) {
                return is_string($val) ? strtolower(trim($val)) : $val;
            }, $triggerValues);

            return in_array($normalizedValue, $normalizedTriggers);
        }

        return false;
    }

    /**
     * Get auto-action configuration
     * @return array|null
     */
    public function getAutoActionConfig()
    {
        return $this->auto_action;
    }
}
