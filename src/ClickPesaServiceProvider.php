<?php

namespace EmilKitua\ClickPesa;

use Illuminate\Support\ServiceProvider;

class ClickPesaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/clickpesa.php', 'clickpesa');

        $this->app->singleton(ClickPesa::class, function () {
            return new ClickPesa();
        });
    }

    public function boot()
    {
        
        // Load migrations directly (for automatic use)
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');

        // Optionally allow publishing
        $this->publishes([
            __DIR__ . '/Database/migrations' => database_path('migrations'),
        ], 'clickpesa-migrations');

        $this->publishes([
            __DIR__.'/Config/clickpesa.php' => config_path('clickpesa.php'),
        ], 'config');
    }
}
