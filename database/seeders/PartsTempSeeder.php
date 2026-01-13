<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\wh\PartsTemp;

class PartsTempSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parts = [
            ['name' => 'Oil Filter', 'price' => 50000, 'discount' => 0, 'current_stock' => 100],
            ['name' => 'Air Filter Outer', 'price' => 125000, 'discount' => 5, 'current_stock' => 50],
            ['name' => 'Air Filter Inner', 'price' => 95000, 'discount' => 5, 'current_stock' => 50],
            ['name' => 'Fuel Filter', 'price' => 85000, 'discount' => 0, 'current_stock' => 100],
            ['name' => 'Water Separator', 'price' => 150000, 'discount' => 0, 'current_stock' => 30],
            ['name' => 'Hydraulic Oil (Drum)', 'price' => 4500000, 'discount' => 10, 'current_stock' => 5],
            ['name' => 'Engine Oil SAE 15W-40 (Drum)', 'price' => 3800000, 'discount' => 10, 'current_stock' => 8],
            ['name' => 'Chassis Grease (Pail)', 'price' => 450000, 'discount' => 0, 'current_stock' => 15],
            ['name' => 'Track Link Assembly', 'price' => 12000000, 'discount' => 0, 'current_stock' => 2],
            ['name' => 'Bucket Tooth', 'price' => 250000, 'discount' => 5, 'current_stock' => 40],
            ['name' => 'Tooth Pin', 'price' => 15000, 'discount' => 0, 'current_stock' => 200],
            ['name' => 'Hydraulic Hose 1/2"', 'price' => 350000, 'discount' => 0, 'current_stock' => 20],
            ['name' => 'O-Ring Kit', 'price' => 120000, 'discount' => 0, 'current_stock' => 10],
            ['name' => 'Fan Belt set', 'price' => 180000, 'discount' => 0, 'current_stock' => 30],
            ['name' => 'Turbocharger', 'price' => 8500000, 'discount' => 5, 'current_stock' => 3],
            ['name' => 'Alternator 24V', 'price' => 2200000, 'discount' => 0, 'current_stock' => 5],
            ['name' => 'Starter Motor 24V', 'price' => 3500000, 'discount' => 0, 'current_stock' => 4],
            ['name' => 'Main Valve Seal Kit', 'price' => 1500000, 'discount' => 0, 'current_stock' => 5],
            ['name' => 'Radiator Hose Upper', 'price' => 45000, 'discount' => 0, 'current_stock' => 15],
            ['name' => 'Radiator Hose Lower', 'price' => 55000, 'discount' => 0, 'current_stock' => 15],
        ];

        foreach ($parts as $part) {
            PartsTemp::create([
                'uid' => Str::uuid(),
                'name' => $part['name'],
                'price' => $part['price'],
                'discount' => $part['discount'],
                'current_stock' => $part['current_stock'],
            ]);
        }
    }
}
