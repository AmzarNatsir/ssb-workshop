<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\MerkController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\OperatingSheetController;
use App\Http\Controllers\OwnershipModeController;
use App\Http\Controllers\PlanServiceController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UnitTypeController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MasterActivityController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('home');
    });

    Route::get('home', function () {
        return view('index');
    })->name('home');

    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // User Management
    Route::get('users/datatables', [UserController::class, 'datatables'])
    ->name('users.datatables');
    Route::resource('users', UserController::class);

    // Role Management
     Route::get('roles/datatables', [RoleController::class, 'datatables'])
    ->name('roles.datatables');
    Route::resource('roles', RoleController::class);

    // Common Modules - Category
    Route::get('category/datatables', [CategoryController::class, 'datatables'])
        ->name('category.datatables');
    Route::resource('category', CategoryController::class);

    // Common Modules - Merk
    Route::get('merk/datatables', [MerkController::class, 'datatables'])
        ->name('merk.datatables');
    Route::resource('merk', MerkController::class);

    // Common Modules - Status
    Route::get('status/datatables', [StatusController::class, 'datatables'])
        ->name('status.datatables');
    Route::resource('status', StatusController::class);

    // Common Modules - Unit Type
    Route::get('unit-type/datatables', [UnitTypeController::class, 'datatables'])
        ->name('unit-type.datatables');
    Route::resource('unit-type', UnitTypeController::class);

    // Common Modules - Documents
    Route::get('documents/datatables', [DocumentController::class, 'datatables'])
        ->name('documents.datatables');
    Route::resource('documents', DocumentController::class);

    // Common Modules - Meter Reading
    Route::get('meter-reading/datatables', [MeterReadingController::class, 'datatables'])
        ->name('meter-reading.datatables');
    Route::resource('meter-reading', MeterReadingController::class);

    // Common Modules - Ownership Mode
    Route::get('ownership-mode/datatables', [OwnershipModeController::class, 'datatables'])
        ->name('ownership-mode.datatables');
    Route::resource('ownership-mode', OwnershipModeController::class);

    // Common Modules - Racks
    Route::get('racks/datatables', [RackController::class, 'datatables'])
        ->name('racks.datatables');
    Route::resource('racks', RackController::class);

    // Common Modules - Tool Type
    Route::get('tool-type/datatables', [\App\Http\Controllers\ToolTypeController::class, 'datatables'])
        ->name('tool-type.datatables');
    Route::resource('tool-type', \App\Http\Controllers\ToolTypeController::class);

    // Periodic Service Type
    Route::get('periodic-service-type/datatables', [\App\Http\Controllers\PeriodicServiceTypeController::class, 'datatables'])
        ->name('periodic-service-type.datatables');
    Route::resource('periodic-service-type', \App\Http\Controllers\PeriodicServiceTypeController::class);

    //Common - Legacy routes
    Route::group(['prefix' => 'common'], function () {
        // Keeping prefix for structure but individual routes moved to resource
    });
    // Supplier
    Route::get('supplier/datatables', [\App\Http\Controllers\SupplierController::class, 'datatables'])
        ->name('supplier.datatables');
    Route::resource('supplier', \App\Http\Controllers\SupplierController::class);

    Route::get('tools/monitoring', [\App\Http\Controllers\ToolController::class, 'monitoring'])
        ->name('tools.monitoring');
    Route::post('tools/print-labels', [\App\Http\Controllers\ToolController::class, 'printLabels'])
        ->name('tools.print-labels');
    Route::get('tools/datatables', [\App\Http\Controllers\ToolController::class, 'datatables'])
        ->name('tools.datatables');
    Route::resource('tools', \App\Http\Controllers\ToolController::class);

    Route::get('parts-temp', [\App\Http\Controllers\PartsTempController::class, 'index'])->name('parts-temp.index');

    // Equipment Management
    Route::get('equipment/datatables', [EquipmentController::class, 'datatables'])
        ->name('equipment.datatables');
    Route::resource('equipment', EquipmentController::class);
    Route::get('equipment/{id}/documents', [EquipmentController::class, 'documents'])->name('equipment.documents');
    Route::post('equipment/documents/upload', [EquipmentController::class, 'uploadDocument'])->name('equipment.documents.upload');
    Route::delete('equipment/documents/{id}', [EquipmentController::class, 'deleteDocument'])->name('equipment.documents.destroy');
    // Operationg Sheet
    Route::group(['prefix' => 'operating-sheet'], function () {
        Route::get('/', [OperatingSheetController::class, 'index'])->name('operating-sheet');
    });

    // Part Requirements
    Route::get('part-requirements/datatables', [\App\Http\Controllers\PartRequirementController::class, 'datatables'])
        ->name('part-requirements.datatables');
    Route::resource('part-requirements', \App\Http\Controllers\PartRequirementController::class);
    // Plan Service
    Route::prefix('plan-service')->group(function () {
        Route::get('/', [PlanServiceController::class, 'index'])->name('plan-service.index');
        Route::get('/create', [PlanServiceController::class, 'create'])->name('plan-service.create');
        Route::post('/', [PlanServiceController::class, 'store'])->name('plan-service.store');
        Route::get('/{id}/edit', [PlanServiceController::class, 'edit'])->name('plan-service.edit');
        Route::put('/{id}', [PlanServiceController::class, 'update'])->name('plan-service.update');
        Route::delete('/{id}', [PlanServiceController::class, 'destroy'])->name('plan-service.destroy');
        Route::post('/{id}/complete', [PlanServiceController::class, 'complete'])->name('plan-service.complete');
        Route::post('/{id}/cancel', [PlanServiceController::class, 'cancel'])->name('plan-service.cancel');
        Route::post('/calculate', [PlanServiceController::class, 'calculate'])->name('plan-service.calculate');
        Route::get('/datatables', [PlanServiceController::class, 'datatables'])->name('plan-service.datatables');
        Route::get('/equipment/{id}', [PlanServiceController::class, 'getEquipmentDetails'])->name('plan-service.equipment-details');
    });

    // Work Request
    Route::get('work-request/datatables', [WorkRequestController::class, 'datatables'])
        ->name('work-request.datatables');
    Route::get('work-request/asset/{id}', [WorkRequestController::class, 'getAssetDetails'])
        ->name('work-request.asset-details');
    Route::get('work-request/parts/search', [WorkRequestController::class, 'searchParts'])
        ->name('work-request.parts-search');
    Route::post('work-request/{id}/create-work-order', [WorkRequestController::class, 'createWorkOrder'])->name('work-request.create-work-order');
    Route::resource('work-request', WorkRequestController::class);

    // Approval Center
    Route::get('approval-center/work-request', [\App\Http\Controllers\WorkRequestApprovalController::class, 'index'])->name('approval-center.work-request.index');
    Route::get('approval-center/work-request/datatables', [\App\Http\Controllers\WorkRequestApprovalController::class, 'datatables'])->name('approval-center.work-request.datatables');
    Route::post('approval-center/work-request/{id}/approve', [\App\Http\Controllers\WorkRequestApprovalController::class, 'approve'])->name('approval-center.work-request.approve');
    Route::post('approval-center/work-request/{id}/reject', [\App\Http\Controllers\WorkRequestApprovalController::class, 'reject'])->name('approval-center.work-request.reject');

    // Approval Matrix (Settings)
    Route::get('approval-matrix/work-request/datatables', [\App\Http\Controllers\WorkRequestApprovalRuleController::class, 'datatables'])->name('approval-matrix.work-request.datatables');
    Route::resource('approval-matrix/work-request', \App\Http\Controllers\WorkRequestApprovalRuleController::class)
        ->names('approval-matrix.work-request')
        ->parameters(['work-request' => 'id']);

    // Work Order Routes
    Route::get('work-order/datatables', [WorkOrderController::class, 'datatables'])->name('work-order.datatables');
    Route::prefix('work-order')->name('work-order.')->group(function () {
        Route::post('/from-plan/{id}', [WorkOrderController::class, 'storeFromPlan'])->name('store-from-plan');
        Route::post('/{id}/close', [WorkOrderController::class, 'close'])->name('close');
        
        // Spare Part Workflow
        Route::post('/spare-part/{partId}/validate', [WorkOrderController::class, 'validateSparePart'])->name('spare-part-validate');
        Route::post('/spare-part/{partId}/approve', [WorkOrderController::class, 'approveSparePart'])->name('spare-part-approve');
        Route::post('/spare-part/{partId}/issue', [WorkOrderController::class, 'issueSparePart'])->name('spare-part-issue');
        Route::post('/spare-part/{partId}/return', [WorkOrderController::class, 'returnSparePart'])->name('spare-part-return');
        Route::post('/spare-part/{partId}/validate-return', [WorkOrderController::class, 'validateReturn'])->name('spare-part-validate-return');
        Route::post('/spare-part/{partId}/approve-return', [WorkOrderController::class, 'approveReturn'])->name('spare-part-approve-return');
        
        Route::post('/{id}/spare-part', [WorkOrderController::class, 'requestSparePart'])->name('spare-part-request');
        Route::post('/{id}/activity', [WorkOrderController::class, 'logActivity'])->name('log-activity');
    });
    Route::resource('work-order', WorkOrderController::class)->except(['store']);

    // Master Mechanical Activities
    Route::get('/master-activities/datatables', [MasterActivityController::class, 'datatables'])->name('master-activities.datatables');
    Route::resource('master-activities', MasterActivityController::class);

    // Master Component Troubles
    Route::get('/master-component-troubles/datatables', [\App\Http\Controllers\MasterComponentTroubleController::class, 'datatables'])->name('master-component-troubles.datatables');
    Route::resource('master-component-troubles', \App\Http\Controllers\MasterComponentTroubleController::class);


    // Mechanic Jobs
    Route::get('mechanic-jobs/datatables', [\App\Http\Controllers\MechanicJobController::class, 'index'])->name('mechanic-job.datatables');
    Route::get('mechanic-jobs/{id}', [\App\Http\Controllers\MechanicJobController::class, 'show'])->name('mechanic-job.show');
    Route::post('mechanic-jobs/{id}/checklist', [\App\Http\Controllers\MechanicJobController::class, 'storeChecklist'])->name('mechanic-job.store-checklist');
    Route::post('mechanic-jobs/{id}/component-check', [\App\Http\Controllers\MechanicJobController::class, 'storeComponentCheck'])->name('mechanic-job.store-component-check');
    Route::post('mechanic-jobs/{id}/finish', [\App\Http\Controllers\MechanicJobController::class, 'finishJob'])->name('mechanic-job.finish');
    Route::get('mechanic-jobs/{id}/summary', [\App\Http\Controllers\MechanicJobController::class, 'summary'])->name('mechanic-job.summary');
    Route::get('mechanic-jobs', [\App\Http\Controllers\MechanicJobController::class, 'index'])->name('mechanic-job.index');

});
