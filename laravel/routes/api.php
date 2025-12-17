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
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Visi JWT apsaugoti route'ai
Route::middleware('auth:api')->group(function () {

    // Tik ADMIN gali valdyti vartotojus
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Visi naudotojai su galiojančiu JWT gali valdyti gyvūnus
    Route::apiResource('pets', PetController::class);
    Route::get('users/{userId}/pets', [PetController::class, 'index']);
    Route::post('users/{userId}/pets', [PetController::class, 'store']);

    Route::apiResource('health-records', HealthRecordController::class);
    Route::get('/pets/{petId}/health-records', [HealthRecordController::class, 'indexForPet']);
    Route::post('/pets/{petId}/health-records', [HealthRecordController::class, 'storeForPet']);

    Route::apiResource('procedures', ProcedureController::class);
    Route::get('/health-records/{recordId}/procedures', [ProcedureController::class, 'indexForRecord']);
    Route::post('/health-records/{recordId}/procedures', [ProcedureController::class, 'storeForRecord']);

});
