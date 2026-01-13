<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\MasterActivity;

class MasterActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            ['code' => 'ENG-001', 'description' => 'Change Engine Oil', 'category' => 'Engine'],
            ['code' => 'ENG-002', 'description' => 'Replace Oil Filter', 'category' => 'Engine'],
            ['code' => 'ENG-003', 'description' => 'Inspect Fan Belts', 'category' => 'Engine'],
            
            ['code' => 'HYD-001', 'description' => 'Check Hydraulic Fluid Level', 'category' => 'Hydraulic'],
            ['code' => 'HYD-002', 'description' => 'Replace Hydraulic Filter', 'category' => 'Hydraulic'],
            ['code' => 'HYD-003', 'description' => 'Inspect Hydraulic Hoses', 'category' => 'Hydraulic'],
            
            ['code' => 'ELE-001', 'description' => 'Battery Inspection', 'category' => 'Electrical'],
            ['code' => 'ELE-002', 'description' => 'Replace Alternator', 'category' => 'Electrical'],
            
            ['code' => 'UNC-001', 'description' => 'Inspect Track Tension', 'category' => 'Undercarriage'],
            ['code' => 'UNC-002', 'description' => 'Replace Track Shoe', 'category' => 'Undercarriage'],
        ];

        foreach ($activities as $activity) {
            MasterActivity::firstOrCreate(
                ['code' => $activity['code']],
                $activity
            );
        }
    }
}
