<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InspectionForm;
use App\Models\InspectionSection;
use App\Models\InspectionItem;
use Illuminate\Support\Facades\DB;

class InspectionFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Create Daily Inspection - Excavator
            $excavatorForm = InspectionForm::create([
                'form_title' => 'Daily Inspection - Excavator',
                'form_description' => 'Daily pre-operation inspection checklist for excavators',
                'applicable_unit_category' => 'Excavator',
                'created_by' => 1,
            ]);

            // Engine Section
            $engineSection = InspectionSection::create([
                'form_id' => $excavatorForm->id,
                'section_order' => 0,
                'section_title' => 'Engine & Fuel System',
                'section_description' => 'Check engine and fuel system components',
            ]);

            InspectionItem::create([
                'section_id' => $engineSection->id,
                'item_order' => 0,
                'item_code' => 'ENG-001',
                'item_name' => 'Engine Oil Level',
                'input_type' => 'GOOD_REPAIR_REPLACE_NA',
                'is_required' => true,
                'auto_action' => json_encode([
                    'action' => 'CREATE_WR',
                    'priority' => 'HIGH',
                    'trigger_on' => ['Repair', 'Replace']
                ]),
                'instruction' => 'Check oil level with dipstick when engine is cold',
            ]);

            InspectionItem::create([
                'section_id' => $engineSection->id,
                'item_order' => 1,
                'item_code' => 'ENG-002',
                'item_name' => 'Coolant Level',
                'input_type' => 'GOOD_REPAIR_REPLACE_NA',
                'is_required' => true,
                'auto_action' => json_encode([
                    'action' => 'CREATE_WR',
                    'priority' => 'CRITICAL',
                    'trigger_on' => ['Replace']
                ]),
            ]);

            InspectionItem::create([
                'section_id' => $engineSection->id,
                'item_order' => 2,
                'item_code' => 'ENG-003',
                'item_name' => 'Fuel Level (%)',
                'input_type' => 'NUMBER',
                'is_required' => true,
                'threshold_warning' => 25,
                'threshold_critical' => 10,
                'instruction' => 'Enter fuel level percentage from gauge',
            ]);

            // Hydraulic Section
            $hydraulicSection = InspectionSection::create([
                'form_id' => $excavatorForm->id,
                'section_order' => 1,
                'section_title' => 'Hydraulic System',
            ]);

            InspectionItem::create([
                'section_id' => $hydraulicSection->id,
                'item_order' => 0,
                'item_code' => 'HYD-001',
                'item_name' => 'Hydraulic Oil Level',
                'input_type' => 'GOOD_REPAIR_REPLACE_NA',
                'is_required' => true,
            ]);

            InspectionItem::create([
                'section_id' => $hydraulicSection->id,
                'item_order' => 1,
                'item_code' => 'HYD-002',
                'item_name' => 'Hydraulic Hoses Condition',
                'input_type' => 'GOOD_REPAIR_REPLACE_NA',
                'is_required' => true,
                'auto_action' => json_encode([
                    'action' => 'CREATE_WR',
                    'priority' => 'HIGH',
                    'trigger_on' => ['Repair', 'Replace']
                ]),
            ]);

            InspectionItem::create([
                'section_id' => $hydraulicSection->id,
                'item_order' => 2,
                'item_code' => 'HYD-003',
                'item_name' => 'Hydraulic Leaks Present',
                'input_type' => 'YES_NO_NA',
                'is_required' => true,
                'auto_action' => json_encode([
                    'action' => 'CREATE_WR',
                    'priority' => 'MEDIUM',
                    'trigger_on' => ['Yes']
                ]),
            ]);

            // Safety Section
            $safetySection = InspectionSection::create([
                'form_id' => $excavatorForm->id,
                'section_order' => 2,
                'section_title' => 'Safety Equipment',
            ]);

            InspectionItem::create([
                'section_id' => $safetySection->id,
                'item_order' => 0,
                'item_code' => 'SAF-001',
                'item_name' => 'Fire Extinguisher',
                'input_type' => 'OK_FAULTY_NA',
                'is_required' => true,
                'auto_action' => json_encode([
                    'action' => 'CREATE_WR',
                    'priority' => 'CRITICAL',
                    'trigger_on' => ['Faulty']
                ]),
            ]);

            InspectionItem::create([
                'section_id' => $safetySection->id,
                'item_order' => 1,
                'item_code' => 'SAF-002',
                'item_name' => 'Seat Belt Condition',
                'input_type' => 'GOOD_REPAIR_REPLACE_NA',
                'is_required' => true,
            ]);

            InspectionItem::create([
                'section_id' => $safetySection->id,
                'item_order' => 2,
                'item_code' => 'SAF-003',
                'item_name' => 'Warning Lights Functional',
                'input_type' => 'YES_NO_NA',
                'is_required' => true,
            ]);

            // Publish the form
            $excavatorForm->publish();

            // Create Daily Inspection - Dump Truck
            $truckForm = InspectionForm::create([
                'form_title' => 'Daily Inspection - Dump Truck',
                'form_description' => 'Daily pre-operation inspection for dump trucks',
                'applicable_unit_category' => 'Dump Truck',
                'created_by' => 1,
            ]);

            // Engine Section for Truck
            $truckEngineSection = InspectionSection::create([
                'form_id' => $truckForm->id,
                'section_order' => 0,
                'section_title' => 'Engine Check',
            ]);

            InspectionItem::create([
                'section_id' => $truckEngineSection->id,
                'item_order' => 0,
                'item_code' => 'TRK-ENG-001',
                'item_name' => 'Engine Oil Level',
                'input_type' => 'GOOD_REPAIR_REPLACE_NA',
                'is_required' => true,
            ]);

            InspectionItem::create([
                'section_id' => $truckEngineSection->id,
                'item_order' => 1,
                'item_code' => 'TRK-ENG-002',
                'item_name' => 'Engine Temperature (°C)',
                'input_type' => 'NUMBER',
                'is_required' => false,
                'threshold_warning' => 90,
                'threshold_critical' => 100,
            ]);

            // Tire Section
            $tireSection = InspectionSection::create([
                'form_id' => $truckForm->id,
                'section_order' => 1,
                'section_title' => 'Tires & Wheels',
            ]);

            InspectionItem::create([
                'section_id' => $tireSection->id,
                'item_order' => 0,
                'item_code' => 'TRK-TIRE-001',
                'item_name' => 'Tire Pressure Check',
                'input_type' => 'PASS_FAIL_NA',
                'is_required' => true,
                'auto_action' => json_encode([
                    'action' => 'CREATE_WR',
                    'priority' => 'MEDIUM',
                    'trigger_on' => ['Fail']
                ]),
            ]);

            InspectionItem::create([
                'section_id' => $tireSection->id,
                'item_order' => 1,
                'item_code' => 'TRK-TIRE-002',
                'item_name' => 'Tire Condition Photo',
                'input_type' => 'IMAGE',
                'is_required' => false,
                'instruction' => 'Take photo of all tires',
            ]);

            // Publish truck form
            $truckForm->publish();

            DB::commit();

            $this->command->info('✓ Created 2 sample inspection forms:');
            $this->command->info('  - Daily Inspection - Excavator (Published)');
            $this->command->info('  - Daily Inspection - Dump Truck (Published)');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Failed to seed inspection forms: ' . $e->getMessage());
            throw $e;
        }
    }
}
