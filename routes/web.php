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

    // Unit Request
    Route::get('unit-request/datatables', [\App\Http\Controllers\UnitRequestController::class, 'datatables'])
        ->name('unit-request.datatables');
    Route::get('unit-request/project-search', [\App\Http\Controllers\UnitRequestController::class, 'projectSearch'])
        ->name('unit-request.project-search');
    Route::post('unit-request/sync', [\App\Http\Controllers\UnitRequestController::class, 'sync'])->name('unit-request.sync');
    Route::post('unit-request/{id}/submit', [\App\Http\Controllers\UnitRequestController::class, 'submitToGA'])->name('unit-request.submit');
    Route::post('unit-request/{id}/validate-ga', [\App\Http\Controllers\UnitRequestController::class, 'validateGA'])->name('unit-request.validate-ga');
    Route::post('unit-request/{id}/approve-om', [\App\Http\Controllers\UnitRequestController::class, 'approveOM'])->name('unit-request.approve-om');
    Route::post('unit-request/{id}/finalize-validation', [\App\Http\Controllers\UnitRequestController::class, 'validateFinalized'])->name('unit-request.finalize-validation');
    Route::resource('unit-request', \App\Http\Controllers\UnitRequestController::class);

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

    // Inspection Forms (Admin)
    Route::get('inspection-forms/datatables', [\App\Http\Controllers\InspectionFormController::class, 'datatables'])
        ->name('inspection-forms.datatables');
    Route::post('inspection-forms/{id}/publish', [\App\Http\Controllers\InspectionFormController::class, 'publish'])
        ->name('inspection-forms.publish');
    Route::post('inspection-forms/{id}/archive', [\App\Http\Controllers\InspectionFormController::class, 'archive'])
        ->name('inspection-forms.archive');
    Route::post('inspection-forms/{id}/duplicate', [\App\Http\Controllers\InspectionFormController::class, 'duplicate'])
        ->name('inspection-forms.duplicate');
    Route::get('inspection-forms/{id}/preview', [\App\Http\Controllers\InspectionFormController::class, 'preview'])
        ->name('inspection-forms.preview');
    Route::post('inspection-forms/upload-image', [\App\Http\Controllers\InspectionFormController::class, 'uploadImage'])
        ->name('inspection-forms.upload-image');
    Route::resource('inspection-forms', \App\Http\Controllers\InspectionFormController::class);

    // Inspection Schedules (Admin)
    Route::get('inspection-schedules/datatables', [\App\Http\Controllers\InspectionScheduleController::class, 'datatables'])
        ->name('inspection-schedules.datatables');
    Route::post('inspection-schedules/{id}/activate', [\App\Http\Controllers\InspectionScheduleController::class, 'activate'])
        ->name('inspection-schedules.activate');
    Route::post('inspection-schedules/{id}/deactivate', [\App\Http\Controllers\InspectionScheduleController::class, 'deactivate'])
        ->name('inspection-schedules.deactivate');
    Route::resource('inspection-schedules', \App\Http\Controllers\InspectionScheduleController::class);

    // Inspection Execution (Inspector/Mechanic)
    Route::get('inspections/datatables', [\App\Http\Controllers\InspectionExecutionController::class, 'datatables'])
        ->name('inspections.datatables');
    Route::get('inspections/{id}/execute', [\App\Http\Controllers\InspectionExecutionController::class, 'show'])
        ->name('inspections.execute');
    Route::post('inspections/{id}/submit', [\App\Http\Controllers\InspectionExecutionController::class, 'submit'])
        ->name('inspections.submit');
    Route::get('inspections/history/{unitId}', [\App\Http\Controllers\InspectionExecutionController::class, 'history'])
        ->name('inspections.history');
    Route::get('inspections/result/{resultId}', [\App\Http\Controllers\InspectionExecutionController::class, 'result'])
        ->name('inspections.result');
    Route::post('inspections/upload-image', [\App\Http\Controllers\InspectionExecutionController::class, 'uploadImage'])
        ->name('inspections.upload-image');
    Route::get('inspections', [\App\Http\Controllers\InspectionExecutionController::class, 'index'])
        ->name('inspections.index');

    // Tool Cards
    Route::get('tool-cards/datatables', [\App\Http\Controllers\ToolCardController::class, 'datatables'])
        ->name('tool-cards.datatables');
    Route::post('tool-cards/{id}/submit', [\App\Http\Controllers\ToolCardController::class, 'submit'])
        ->name('tool-cards.submit');
    Route::post('tool-cards/{id}/approve', [\App\Http\Controllers\ToolCardController::class, 'approve'])
        ->name('tool-cards.approve');
    Route::post('tool-cards/{id}/reject', [\App\Http\Controllers\ToolCardController::class, 'reject'])
        ->name('tool-cards.reject');
    Route::get('tool-cards/{id}/print', [\App\Http\Controllers\ToolCardController::class, 'print'])
        ->name('tool-cards.print');
    Route::resource('tool-cards', \App\Http\Controllers\ToolCardController::class);

    // Tool Lending Module Routes
    Route::prefix('tool-lending')->middleware(['auth'])->name('tool-lending.')->group(function () {
        
        // Tool Card Scanning API
        Route::get('scan/tool-card/{barcode}', [\App\Http\Controllers\ToolCardScanController::class, 'scanToolCard'])
            ->name('scan.tool-card');
        
        // Tool Scanning & Availability API
        Route::get('scan/tool/{barcode}', [\App\Http\Controllers\LoanTransactionController::class, 'scanTool'])
            ->name('scan.tool');
        Route::get('tools/available/{accessLevel}', [\App\Http\Controllers\LoanTransactionController::class, 'getAvailableTools'])
            ->name('tools.available');
        
        // Loan Transactions
        Route::get('loans', [\App\Http\Controllers\LoanTransactionController::class, 'index'])
            ->name('loans.index');
        Route::get('loans/create', [\App\Http\Controllers\LoanTransactionController::class, 'create'])
            ->name('loans.create');
        Route::post('loans', [\App\Http\Controllers\LoanTransactionController::class, 'store'])
            ->name('loans.store');
        Route::get('loans/history', [\App\Http\Controllers\LoanTransactionController::class, 'history'])
            ->name('loans.history');
        Route::get('loans/{loanNumber}', [\App\Http\Controllers\LoanTransactionController::class, 'show'])
            ->name('loans.show');
        Route::get('loans/{loanNumber}/return', [\App\Http\Controllers\LoanTransactionController::class, 'returnForm'])
            ->name('loans.return');
        Route::post('loans/process-return', [\App\Http\Controllers\LoanTransactionController::class, 'processReturn'])
            ->name('loans.process-return');
        
        // System Settings
        Route::get('settings', [\App\Http\Controllers\SystemSettingController::class, 'index'])
            ->name('settings.index');
        Route::put('settings', [\App\Http\Controllers\SystemSettingController::class, 'update'])
            ->name('settings.update');

        // Tool Incident/Report Module
        Route::get('incidents', [\App\Http\Controllers\ToolIncidentController::class, 'index'])
            ->name('incidents.index');
        Route::get('incidents/{id}/edit', [\App\Http\Controllers\ToolIncidentController::class, 'edit'])
            ->name('incidents.edit');
        Route::put('incidents/{id}', [\App\Http\Controllers\ToolIncidentController::class, 'update'])
            ->name('incidents.update');
        Route::get('incidents/{id}', [\App\Http\Controllers\ToolIncidentController::class, 'show'])
             ->name('incidents.show');
        Route::post('incidents/{id}/submit', [\App\Http\Controllers\ToolIncidentController::class, 'submit'])
            ->name('incidents.submit');
        Route::post('incidents/{id}/approve-mtn', [\App\Http\Controllers\ToolIncidentController::class, 'approveMtn'])
            ->name('incidents.approve-mtn');
        Route::post('incidents/{id}/approve-hr', [\App\Http\Controllers\ToolIncidentController::class, 'approveHr'])
            ->name('incidents.approve-hr');
        Route::get('incidents/create-from-opname', [\App\Http\Controllers\ToolIncidentController::class, 'createFromOpname'])
            ->name('incidents.create-from-opname');
        Route::post('incidents/{id}/reject', [\App\Http\Controllers\ToolIncidentController::class, 'reject'])
            ->name('incidents.reject');

        // Tool Stock Opname
        Route::get('stock-opname', [\App\Http\Controllers\ToolStockOpnameController::class, 'index'])
            ->name('stock-opname.index');
        Route::get('stock-opname/create', [\App\Http\Controllers\ToolStockOpnameController::class, 'create'])
            ->name('stock-opname.create');
        Route::post('stock-opname', [\App\Http\Controllers\ToolStockOpnameController::class, 'store'])
            ->name('stock-opname.store');
        Route::get('stock-opname/{id}', [\App\Http\Controllers\ToolStockOpnameController::class, 'show'])
            ->name('stock-opname.show');
        Route::post('stock-opname/{id}/update-detail', [\App\Http\Controllers\ToolStockOpnameController::class, 'updateDetail'])
            ->name('stock-opname.update-detail');
        Route::get('stock-opname/{id}/pdf', [\App\Http\Controllers\ToolStockOpnameController::class, 'exportPdf'])
            ->name('stock-opname.pdf');
        Route::post('stock-opname/{id}/add-finding', [\App\Http\Controllers\ToolStockOpnameController::class, 'addFinding'])
            ->name('stock-opname.add-finding');
        Route::post('stock-opname/{id}/finalize', [\App\Http\Controllers\ToolStockOpnameController::class, 'finalize'])
            ->name('stock-opname.finalize');

        // Tool Transaction Report Module
        Route::get('reports', [\App\Http\Controllers\ToolReportController::class, 'index'])
            ->name('reports.index');
        Route::get('reports/history', [\App\Http\Controllers\ToolReportController::class, 'history'])
            ->name('reports.history');
        Route::get('reports/chart-data', [\App\Http\Controllers\ToolReportController::class, 'getChartData'])
            ->name('reports.chart-data');
        Route::get('reports/export', [\App\Http\Controllers\ToolReportController::class, 'export'])
            ->name('reports.export');
    });

});
