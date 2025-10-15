<?php
use App\Http\Controllers\PetController;
use App\Http\Controllers\HealthRecordController;

Route::apiResource('pets', PetController::class);
Route::apiResource('health-records', HealthRecordController::class);
