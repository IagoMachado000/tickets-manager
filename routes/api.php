<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\TicketMessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Rotas de autenticação
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register')->name('api.v1.register');
        Route::post('/login', 'login')->name('api.v1.login');
        Route::post('/logout', 'logout')->name('api.v1.logout')->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        // Rotas protegidas para projetos
        Route::apiResource('projects', ProjectController::class);

        // Rotas protegidas para tickets
        Route::prefix('projects')
            ->controller(TicketController::class)
            ->group(function () {
                Route::get('/{project}/tickets', 'index')->name('api.v1.projects.tickets.index');
                Route::post('/{project}/tickets', 'store')->name('api.v1.projects.tickets.store');
            });

        Route::apiResource('tickets', TicketController::class)->except(['index', 'store']);

        // Rotas protegidas para mensagens de tickets
        Route::prefix('tickets/{ticket}')
            ->controller(TicketMessageController::class)
            ->group(function () {
                Route::post('/messages', 'store')->name('api.v1.tickets.messages.store');
            });
    });
});
