<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Rotas de autenticação
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register')->name('api.v1.register');
        Route::post('/login', 'login')->name('api.v1.login');
        Route::post('/logout', 'logout')->name('api.v1.logout')->middleware('auth:sanctum');
    });
});
