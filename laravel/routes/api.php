<?php
use App\Http\Controllers\PetController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\ProcedureController;

// pets (CRUD + list)
Route::apiResource('pets', PetController::class);

// hierarchical health-records per pet
Route::get('/pets/{petId}/health-records', [HealthRecordController::class, 'indexForPet']);
Route::post('/pets/{petId}/health-records', [HealthRecordController::class, 'storeForPet']);

// health-records (single-level CRUD)
Route::apiResource('health-records', HealthRecordController::class);

// hierarchical procedures per health-record
Route::get('/health-records/{recordId}/procedures', [ProcedureController::class, 'indexForRecord']);
Route::post('/health-records/{recordId}/procedures', [ProcedureController::class, 'storeForRecord']);

// procedures CRUD (single)
Route::apiResource('procedures', ProcedureController::class);
