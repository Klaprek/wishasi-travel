<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'tampilHalamanLogin'])
        ->name('login');

    Route::post('login', [AuthController::class, 'cekData']);
});

Route::middleware('auth')->group(function () {
    Route::put('password', [AuthController::class, 'ubahPassword'])->name('password.update');

    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout');
});
