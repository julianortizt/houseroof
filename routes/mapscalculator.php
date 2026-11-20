<?php

use App\Http\Controllers\MapsCalculator\MapulatorController;
use App\Http\Controllers\MapsCalculator\MapulatorProjectController;
use App\Http\Controllers\MapsCalculator\QuotePdfController;
use Illuminate\Support\Facades\Route;

// MapsCalculator routes.
// El acceso lo da el middleware del módulo (admin/comercial/técnico); cada
// acción comprueba además su propia policy.
Route::middleware(['auth', 'verified', 'mapscalculator.access'])->prefix('mapscalculator')->name('mapscalculator.')->group(function () {
    // Dashboard
    Route::get('/', [MapulatorController::class, 'index'])->name('index');

    // Suscripción: el módulo es de uso interno, no hay planes de pago.
    // El enlace antiguo redirige al dashboard por compatibilidad.
    Route::get('/suscripcion', fn () => redirect()->route('mapscalculator.index'))->name('billing');

    // CRM de clientes (Módulo 3)
    Route::get('/clientes', [MapulatorController::class, 'clients'])->name('client.index');
    Route::get('/clientes/crear', [MapulatorController::class, 'clientCreate'])->name('client.create');
    Route::post('/clientes', [MapulatorController::class, 'clientStore'])->name('client.store');
    Route::get('/clientes/{client}', [MapulatorController::class, 'clientShow'])->name('client.show');

    // Project routes
    Route::get('/proyecto/crear', [MapulatorController::class, 'create'])->name('project.create');
    Route::post('/proyecto', [MapulatorController::class, 'store'])->name('project.store');
    Route::get('/proyecto/{project}', [MapulatorProjectController::class, 'show'])->name('project.show');
    Route::put('/proyecto/{project}', [MapulatorController::class, 'update'])->name('project.update');
    Route::delete('/proyecto/{project}', [MapulatorController::class, 'destroy'])->name('project.destroy');

    // Measurement routes
    Route::get('/proyecto/{project}/medir/exterior', [MapulatorController::class, 'measureExterior'])->name('measure.exterior');
    Route::get('/proyecto/{project}/medir/interior', [MapulatorController::class, 'measureInterior'])->name('measure.interior');
    Route::post('/proyecto/{project}/medicion', [MapulatorController::class, 'storeMeasurement'])->name('measurement.store');
    Route::put('/medicion/{measurement}', [MapulatorController::class, 'updateMeasurement'])->name('measurement.update');
    Route::delete('/medicion/{measurement}', [MapulatorController::class, 'destroyMeasurement'])->name('measurement.destroy');

    Route::middleware('module.enabled:quotes')->group(function () {
        Route::get('/proyecto/{project}/presupuesto', [MapulatorController::class, 'quote'])->name('quote.show');
        Route::post('/proyecto/{project}/presupuesto', [MapulatorController::class, 'storeQuote'])->name('quote.store');
        Route::put('/presupuesto/{quote}', [MapulatorController::class, 'updateQuote'])->name('quote.update');
        Route::post('/presupuesto/{quote}/enviar', [MapulatorController::class, 'sendQuote'])->name('quote.send');
        Route::post('/presupuesto/{quote}/aprobar', [MapulatorController::class, 'approveQuote'])->name('quote.approve');
        Route::post('/presupuesto/{quote}/rechazar', [MapulatorController::class, 'rejectQuote'])->name('quote.reject');
        Route::post('/presupuesto/{quote}/nueva-version', [MapulatorController::class, 'newQuoteVersion'])->name('quote.new-version');
        Route::get('/presupuesto/{quote}/pdf', [QuotePdfController::class, 'download'])->name('quote.pdf');
        Route::get('/presupuesto/{quote}/pdf/view', [QuotePdfController::class, 'view'])->name('quote.pdf.view');
    });

    Route::middleware('module.enabled:work_orders')->group(function () {
        Route::get('/proximas-obras', [MapulatorController::class, 'upcomingWorkOrders'])->name('work-order.upcoming');
        Route::get('/obra/{workOrder}/informe', [MapulatorController::class, 'workOrderReport'])->name('work-order.report');
        Route::get('/obra/{workOrder}', [MapulatorController::class, 'workOrder'])->name('work-order.show');
        Route::post('/obra', [MapulatorController::class, 'createWorkOrder'])->name('work-order.store');
        Route::put('/obra/{workOrder}', [MapulatorController::class, 'updateWorkOrder'])->name('work-order.update');
        Route::post('/obra/{workOrder}/log', [MapulatorController::class, 'addWorkLog'])->name('work-log.store');
        Route::post('/obra/{workOrder}/sync-bitacora', [MapulatorController::class, 'syncWorkLogs'])->name('work-order.sync-logs');
        Route::delete('/log/{workLog}', [MapulatorController::class, 'deleteWorkLog'])->name('work-log.destroy');
    });

});

// Portal privado del cliente: acceso por token en la URL, sin login.
// Antes vivía dentro del grupo ['auth', 'verified'] (pese al comentario), así que
// el enlace que se enviaba al cliente le redirigía a /login y nunca veía el portal.
Route::prefix('mapscalculator')->name('mapscalculator.')->group(function () {
    Route::get('/portal/{token}', [MapulatorController::class, 'clientPortal'])->name('client.portal');
});
