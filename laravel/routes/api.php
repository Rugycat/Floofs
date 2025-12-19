<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});

// Visi JWT apsaugoti route'ai
Route::middleware('auth:api')->group(function () {
    // ADMIN - Vartotojų valdymas
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // PET MANAGEMENT - Visi (su papildomais permission checkais kontroleryje)
    Route::apiResource('pets', PetController::class);

    // HEALTH RECORDS - Tik vets ir admins gali kurti
    Route::apiResource('health-records', HealthRecordController::class);
    Route::get('/pets/{petId}/health-records', [HealthRecordController::class, 'indexForPet']);
    Route::post('/pets/{petId}/health-records', [HealthRecordController::class, 'storeForPet']);

    // PROCEDURES - Tik vets ir admins gali kurti
    Route::apiResource('procedures', ProcedureController::class);
    Route::get('/health-records/{recordId}/procedures', [ProcedureController::class, 'indexForRecord']);
    Route::post('/health-records/{recordId}/procedures', [ProcedureController::class, 'storeForRecord']);
});