<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkRequestApprovalRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Project Manager' => \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Project Manager'])->id,
            'Maintenance Head' => \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Maintenance Head'])->id,
            'Warehouse Head' => \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Warehouse Head'])->id,
            'Finance Manager' => \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Finance Manager'])->id,
            'Dept Head' => \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Dept Head'])->id,
        ];

        $rules = [
            // On-Project - Repair Request
            ['category' => 'On-Project – Operation', 'wr_type' => 'Repair Request', 'role_id' => $roles['Project Manager'], 'step_order' => 1],
            ['category' => 'On-Project – Operation', 'wr_type' => 'Repair Request', 'role_id' => $roles['Maintenance Head'], 'step_order' => 2],

            // On-Project - Goods Request
            ['category' => 'On-Project – Operation', 'wr_type' => 'Goods Request', 'role_id' => $roles['Warehouse Head'], 'step_order' => 1],
            ['category' => 'On-Project – Operation', 'wr_type' => 'Goods Request', 'role_id' => $roles['Project Manager'], 'step_order' => 2],

            // Non-Project - Repair Request
            ['category' => 'Non-Project – Operation', 'wr_type' => 'Repair Request', 'role_id' => $roles['Maintenance Head'], 'step_order' => 1],
            ['category' => 'Non-Project – Operation', 'wr_type' => 'Repair Request', 'role_id' => $roles['Finance Manager'], 'step_order' => 2],

            // Department - Goods Request
            ['category' => 'Department', 'wr_type' => 'Goods Request', 'role_id' => $roles['Dept Head'], 'step_order' => 1],
            ['category' => 'Department', 'wr_type' => 'Goods Request', 'role_id' => $roles['Warehouse Head'], 'step_order' => 2],
        ];

        foreach ($rules as $rule) {
            \App\Models\WorkRequestApprovalRule::updateOrCreate(
                [
                    'category' => $rule['category'],
                    'wr_type' => $rule['wr_type'],
                    'step_order' => $rule['step_order']
                ],
                ['role_id' => $rule['role_id']]
            );
        }
    }
}
