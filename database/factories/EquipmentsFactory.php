<?php

namespace Database\Factories;

use App\Models\Equipments;
use App\Models\User;
use App\Models\common\Category;
use App\Models\common\Merk;
use App\Models\common\UnitType;
use App\Models\common\Status;
use App\Models\common\MeterReading;
use App\Models\common\OwnershipMode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipments>
 */
class EquipmentsFactory extends Factory
{
    protected $model = Equipments::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = Category::pluck('id')->toArray();
        $merks = Merk::pluck('id')->toArray();
        $unitTypes = UnitType::pluck('id')->toArray();
        $statuses = Status::pluck('id')->toArray();
        $meterReadings = MeterReading::pluck('id')->toArray();
        $ownershipModes = OwnershipMode::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();

        return [
            'uid' => (string) Str::uuid(),
            'code' => 'EQ-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'engine_no' => strtoupper($this->faker->bothify('ENG-####-????')),
            'chassis_no' => strtoupper($this->faker->bothify('CHS-####-????')),
            'plate_number' => strtoupper($this->faker->bothify('? #### ??')),
            'capacity' => $this->faker->numberBetween(5, 50) . ' Tons',
            'prodution_year' => $this->faker->year(),
            'warranty_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'purchase_price' => $this->faker->randomFloat(2, 50000, 500000),
            'internal_estimated_price' => $this->faker->randomFloat(2, 40000, 450000),
            'market_price' => $this->faker->randomFloat(2, 45000, 480000),
            'equipment_status_id' => $this->faker->randomElement($statuses),
            'status_information' => $this->faker->randomElement(['Excellent', 'Good', 'Service Needed', 'Under Maintenance']),
            'project_id' => null,
            'project_status' => $this->faker->randomElement(['Idle', 'On Project', 'Standby']),
            'meter_reading_id' => $this->faker->randomElement($meterReadings),
            'supplier_id' => null,
            'pic_unit' => $this->faker->randomElement($users),
            'ownership_mode_id' => $this->faker->randomElement($ownershipModes),
            'category_id' => $this->faker->randomElement($categories),
            'merk_id' => $this->faker->randomElement($merks),
            'unit_type_id' => $this->faker->randomElement($unitTypes),
            'image' => null,
        ];
    }
}
