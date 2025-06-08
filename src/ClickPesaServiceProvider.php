<?php

namespace YourVendor\ClickPesa;

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
        $this->publishes([
            __DIR__.'/Config/clickpesa.php' => config_path('clickpesa.php'),
        ], 'config');
    }
}
