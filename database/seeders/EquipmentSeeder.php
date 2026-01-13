<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipments;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Equipments::factory()->count(30)->create();
    }
}
