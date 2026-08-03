<?php

use Illuminate\Support\Facades\Route;
use MetricsDash\Laravel\Http\Controllers\MetricsAdminController;

Route::group(['prefix' => 'metrics-dash'], function () {
    // API Introspection & Mappage BDD
    Route::get('/api/tables', [MetricsAdminController::class, 'getTables'])->name('metrics-dash.api.tables');
    Route::get('/api/columns', [MetricsAdminController::class, 'getColumns'])->name('metrics-dash.api.columns');
    Route::post('/api/mapping', [MetricsAdminController::class, 'saveMapping'])->name('metrics-dash.api.save-mapping');
    Route::post('/api/sync', [MetricsAdminController::class, 'triggerSync'])->name('metrics-dash.api.sync');

    // Interface d'Administration Embarquée de Mappage
    Route::get('/', [MetricsAdminController::class, 'index'])->name('metrics-dash.admin');
});

