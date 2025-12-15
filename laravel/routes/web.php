<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return response()->json(['message' => 'Please use /auth/login (POST)'], 400);
})->name('login.web');

Route::get('/', function () {
    return view('home');
});
