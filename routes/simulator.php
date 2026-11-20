<?php

use App\Http\Controllers\Simulator\SimulatorApiController;
use App\Http\Controllers\Simulator\SimulatorPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin.user', 'module.enabled:simulator'])->group(function () {
    Route::get('/simulador', [SimulatorPageController::class, 'index'])
        ->name('simulator.index');

    Route::prefix('api/simulador')->middleware('throttle:60,1')->group(function () {
        Route::get('/room-templates', [SimulatorApiController::class, 'roomTemplates']);
        Route::get('/finishes', [SimulatorApiController::class, 'finishes']);
        Route::post('/quotes', [SimulatorApiController::class, 'storeQuote']);
    });
});
