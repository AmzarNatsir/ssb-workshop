<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Equipments;
use App\Models\UnitRequest;
use App\Models\UnitRequestItem;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UnitRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_create_unit_request()
    {
        $equipment = Equipments::factory()->create();

        $response = $this->postJson('/api/unit-requests', [
            'project_id' => 1,
            'remarks' => 'Test Request',
            'items' => [
                ['equipment_id' => $equipment->id]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('unit_requests', [
            'project_id' => 1,
            'status' => 'DRAFT'
        ]);
    }

    public function test_workflow_transitions()
    {
        $equipment = Equipments::factory()->create();
        $unitRequest = UnitRequest::create([
            'uid' => 'test-uid',
            'request_no' => 'UR000001',
            'project_id' => 1,
            'requested_by' => $this->user->id,
            'status' => 'DRAFT',
        ]);
        $item = $unitRequest->items()->create([
            'equipment_id' => $equipment->id,
            'status' => 'PENDING',
        ]);

        // Submit to GA
        $this->postJson("/api/unit-requests/{$unitRequest->id}/submit")
            ->assertStatus(200);
        $this->assertEquals('SUBMITTED', $unitRequest->fresh()->status);

        // Validate GA
        $this->postJson("/api/unit-requests/{$unitRequest->id}/validate-ga")
            ->assertStatus(200);
        $this->assertEquals('GA_VALIDATED', $unitRequest->fresh()->status);

        // Approve OM
        $this->postJson("/api/unit-requests/{$unitRequest->id}/approve-om", ['action' => 'APPROVE'])
            ->assertStatus(200);
        $this->assertEquals('APPROVED', $unitRequest->fresh()->status);

        // Assign Mechanic
        $this->postJson("/api/unit-requests/items/{$item->id}/assign-mechanic", [
            'mechanic_id' => $this->user->id
        ])->assertStatus(200);
        
        $this->assertEquals('ASSIGNED', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->work_request_id);

        // Mark RFU
        $this->postJson("/api/unit-requests/items/{$item->id}/rfu")
            ->assertStatus(200);
        $this->assertEquals('RFU', $item->fresh()->status);

        // Finalize Mobilization
        $employee = Employee::factory()->create();
        $this->postJson("/api/unit-requests/items/{$item->id}/finalize", [
            'operator_id' => $employee->id,
            'hm_start' => 100,
            'km_start' => 200,
            'fuel_level' => 'FULL',
            'refuel_status' => 'DONE',
        ])->assertStatus(200);

        $this->assertEquals('FINALIZED', $item->fresh()->status);
        $this->assertEquals('RFU', $unitRequest->fresh()->status); // Header becomes RFU when all items are finalized

        // Final Validation
        $this->postJson("/api/unit-requests/{$unitRequest->id}/finalize-validation")
            ->assertStatus(200);
        $this->assertEquals('FINALIZED', $unitRequest->fresh()->status);
    }
}
