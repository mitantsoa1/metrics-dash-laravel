<?php

namespace MetricsDash\Laravel;

use Illuminate\Support\ServiceProvider;

class MetricsDashServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Enregistrement des routes web /metrics-dash
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Enregistrement des vues Blade du package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'metrics-dash');

        // Enregistrement des commandes et publication des assets
        if ($this->app->runningInConsole()) {
            $this->commands([
                \MetricsDash\Laravel\Console\Commands\SyncMappedMetricsCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/metrics-dash.php' => config_path('metrics-dash.php'),
            ], 'metrics-dash-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/metrics-dash'),
            ], 'metrics-dash-views');
        }

    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/metrics-dash.php',
            'metrics-dash'
        );

        $this->app->singleton(\MetricsDash\Laravel\Services\DatabaseMappingService::class, function ($app) {
            return new \MetricsDash\Laravel\Services\DatabaseMappingService();
        });
    }
}
