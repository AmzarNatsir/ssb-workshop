<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\common\Category;
use App\Models\common\Merk;
use App\Models\common\UnitType;
use App\Models\common\Status;
use App\Models\common\MeterReading;
use App\Models\common\OwnershipMode;
use App\Models\common\Documents;
use App\Models\common\Racks;
use Illuminate\Support\Str;

class CommonDataSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = ['Excavator', 'Bulldozer', 'Crane', 'Truck', 'Loader', 'Compactor'];
        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Description for ' . $name
                ]
            );
        }

        // Merks
        $merks = ['Caterpillar', 'Komatsu', 'Volvo', 'Hitachi', 'Kobelco', 'Sany', 'Hyundai'];
        foreach ($merks as $name) {
            Merk::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Description for ' . $name
                ]
            );
        }

        // Unit Types
        $unitTypes = ['Heavy Equipment', 'Light Equipment', 'Vehicle', 'Power Tools'];
        foreach ($unitTypes as $name) {
            UnitType::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Description for ' . $name
                ]
            );
        }

        // Statuses
        $statuses = ['Available', 'In Use', 'Maintenance', 'Broken', 'Disposed'];
        foreach ($statuses as $name) {
            Status::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Description for ' . $name
                ]
            );
        }

        // Meter Readings
        $meterReadings = ['Hour Meter (HM)', 'Kilometer (KM)'];
        foreach ($meterReadings as $name) {
            MeterReading::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Description for ' . $name
                ]
            );
        }

        // Ownership Modes
        $ownershipModes = ['Owned', 'Leased', 'Rented'];
        foreach ($ownershipModes as $name) {
            OwnershipMode::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Description for ' . $name
                ]
            );
        }

        // Documents
        $documents = ['Insurance', 'STNK', 'KIR', 'BPKB', 'Manual Book', 'Service History'];
        foreach ($documents as $name) {
            Documents::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Document type for ' . $name
                ]
            );
        }

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
