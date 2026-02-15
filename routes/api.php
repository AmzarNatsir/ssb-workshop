<?php

use App\Http\Controllers\Api\EquipmentApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/units', [EquipmentApiController::class, 'allUnits'])->name('api.units.index');
    Route::get('/units/{id}', [EquipmentApiController::class, 'show'])->name('api.units.show')->where('id', '[0-9]+');
    Route::get('/units/uid/{uid}', [EquipmentApiController::class, 'showByUid'])->name('api.units.show-by-uid');
});
