<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC (no auth)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED (JWT required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // Auth utility
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN only
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | PETS
    |--------------------------------------------------------------------------
    */
    Route::apiResource('pets', PetController::class);
    Route::get('users/{userId}/pets', [PetController::class, 'index']);
    Route::post('users/{userId}/pets', [PetController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | HEALTH RECORDS
    |--------------------------------------------------------------------------
    */
    Route::apiResource('health-records', HealthRecordController::class);
    Route::get('pets/{petId}/health-records', [HealthRecordController::class, 'indexForPet']);
    Route::post('pets/{petId}/health-records', [HealthRecordController::class, 'storeForPet']);

    /*
    |--------------------------------------------------------------------------
    | PROCEDURES
    |--------------------------------------------------------------------------
    */
    Route::apiResource('procedures', ProcedureController::class);
    Route::get('health-records/{recordId}/procedures', [ProcedureController::class, 'indexForRecord']);
    Route::post('health-records/{recordId}/procedures', [ProcedureController::class, 'storeForRecord']);
});
