<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // arba tiesiog atidaryti failą
});

// Fallback - Visos nuorodos (pvz /pets, /health) grąžina HTML
Route::fallback(function () {
    return file_get_contents(public_path('index.html'));
});