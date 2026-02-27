<?php

use App\Http\Controllers\Api\EquipmentApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/units', [EquipmentApiController::class, 'allUnits'])->name('api.units.index');
    Route::get('/units/{id}', [EquipmentApiController::class, 'show'])->name('api.units.show')->where('id', '[0-9]+');
    Route::get('/units/uid/{uid}', [EquipmentApiController::class, 'showByUid'])->name('api.units.show-by-uid');

    // Unit Requests
    Route::prefix('unit-requests')->group(function () {
        Route::get('/', [\App\Http\Controllers\UnitRequestController::class, 'getAll']);
        Route::post('/', [\App\Http\Controllers\UnitRequestController::class, 'store']);
        Route::get('/{uid}', [\App\Http\Controllers\UnitRequestController::class, 'show']);
        Route::post('/{id}/submit', [\App\Http\Controllers\UnitRequestController::class, 'submitToGA']);
        Route::post('/{id}/validate-ga', [\App\Http\Controllers\UnitRequestController::class, 'validateGA']);
        Route::post('/{id}/approve-om', [\App\Http\Controllers\UnitRequestController::class, 'approveOM']);
        Route::post('/items/{itemId}/assign-mechanic', [\App\Http\Controllers\UnitRequestController::class, 'assignMechanic']);
        Route::post('/items/{itemId}/rfu', [\App\Http\Controllers\UnitRequestController::class, 'markRFU']);
        Route::post('/items/{itemId}/finalize', [\App\Http\Controllers\UnitRequestController::class, 'finalizeMobilization']);
        Route::post('/{id}/finalize-validation', [\App\Http\Controllers\UnitRequestController::class, 'validateFinalized']);
    });
});
