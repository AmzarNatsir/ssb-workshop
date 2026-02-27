<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Equipments;
use App\Models\UnitRequest;
use App\Services\ProjectIntegrationService;

$u = User::first();
$e = Equipments::first();

if (!$u || !$e) {
    echo "Error: Need at least one user and one equipment in the database.\n";
    exit(1);
}

// Create sample Unit Request
$ur = UnitRequest::create([
    'project_id' => 4, // ID 4 corresponds to Sambera project in the API results I saw
    'remarks' => 'Verification request created by assistant',
    'requested_by' => $u->id,
    'status' => 'DRAFT',
    'total_units' => 1
]);

$ur->items()->create([
    'equipment_id' => $e->id,
    'status' => 'PENDING'
]);

echo "Created Unit Request ID: " . $ur->id . " (Request No: " . $ur->request_no . ")\n";

// Test Project Service
$service = app(ProjectIntegrationService::class);
$names = $service->getProjectNames([4]);
echo "Project Name resolved for ID 4: " . ($names[4] ?? 'FAILED') . "\n";
