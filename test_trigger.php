<?php

require 'vendor/autoload.php';

// Mock InspectionItem
class MockItem {
    public $auto_action;

    public function __construct($data) {
        $this->auto_action = $data;
    }

    public function shouldTriggerAutoAction($value)
    {
        if (empty($this->auto_action)) {
            return false;
        }
        
        if (isset($this->auto_action['trigger_on'])) {
            $triggerValues = $this->auto_action['trigger_on'];
            
            // Normalize input value
            $normalizedValue = is_string($value) ? strtolower(trim($value)) : $value;
            
            // Normalize trigger values
            $normalizedTriggers = array_map(function($val) {
                return is_string($val) ? strtolower(trim($val)) : $val;
            }, $triggerValues);

            echo "Checking value '{$normalizedValue}' against triggers: " . json_encode($normalizedTriggers) . "\n";
            return in_array($normalizedValue, $normalizedTriggers);
        }

        return false;
    }
}

// Test Case 1: Standard
$data1 = [
    'action' => 'CREATE_WR',
    'priority' => 'HIGH',
    'trigger_on' => ['Repair', 'Replace']
];
$item1 = new MockItem($data1);
$result1 = $item1->shouldTriggerAutoAction('Replace');
echo "Test 1 (Replace): " . ($result1 ? 'PASS' : 'FAIL') . "\n";

// Test Case 2: Whitespace in trigger
$data2 = [
    'trigger_on' => ['Repair', 'Replace '] // Trailing space
];
$item2 = new MockItem($data2);
$result2 = $item2->shouldTriggerAutoAction('Replace');
echo "Test 2 (Whitespace): " . ($result2 ? 'PASS' : 'FAIL') . "\n";

// Test Case 3: Case sensitivity
$result3 = $item1->shouldTriggerAutoAction('replace');
echo "Test 3 (Case): " . ($result3 ? 'PASS' : 'FAIL') . "\n";

?>
