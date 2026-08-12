<?php

use Illuminate\Support\Facades\Route;
use MetricsDash\Laravel\Http\Controllers\MetricsAdminController;

Route::prefix('metrics-dash')->group(function () {
    // Introspection & Mappage BDD
    Route::get('/tables', [MetricsAdminController::class, 'getTables'])->name('metrics-dash.api.tables');
    Route::get('/columns', [MetricsAdminController::class, 'getColumns'])->name('metrics-dash.api.columns');
    Route::post('/mapping', [MetricsAdminController::class, 'saveMapping'])->name('metrics-dash.api.save-mapping');
    Route::post('/sync', [MetricsAdminController::class, 'triggerSync'])->name('metrics-dash.api.sync');
    Route::match(['get', 'post'], '/metrics', [MetricsAdminController::class, 'getAnalyticsMetrics'])->name('metrics-dash.api.metrics');

    // Aliases avec /api/ pour rétrocompatibilité
    Route::get('/api/tables', [MetricsAdminController::class, 'getTables']);
    Route::get('/api/columns', [MetricsAdminController::class, 'getColumns']);
    Route::post('/api/mapping', [MetricsAdminController::class, 'saveMapping']);
    Route::post('/api/sync', [MetricsAdminController::class, 'triggerSync']);
    Route::match(['get', 'post'], '/api/metrics', [MetricsAdminController::class, 'getAnalyticsMetrics']);

    // Interface d'Administration Embarquée de Mappage
    Route::get('/', [MetricsAdminController::class, 'index'])->name('metrics-dash.admin');
    Route::get('', [MetricsAdminController::class, 'index']);
});
