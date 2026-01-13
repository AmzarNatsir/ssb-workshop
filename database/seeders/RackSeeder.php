<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\common\Racks;
use Illuminate\Support\Str;

class RackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Racks
        $racks = [
            ['code' => 'RACK-01', 'name' => 'Rack A', 'location' => 'Zone 1'],
            ['code' => 'RACK-02', 'name' => 'Rack B', 'location' => 'Zone 1'],
            ['code' => 'RACK-03', 'name' => 'Rack C', 'location' => 'Zone 2'],
        ];
        foreach ($racks as $rack) {
            Racks::firstOrCreate(
                ['rack_code' => $rack['code']],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $rack['name'],
                    'location' => $rack['location'],
                    'slug' => Str::slug($rack['code'] . ' ' . $rack['name'])
                ]
            );
        }
    }
}
