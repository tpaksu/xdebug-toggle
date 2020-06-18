<?php

namespace Tpaksu\XdebugToggle;

use Illuminate\Support\ServiceProvider;
use Tpaksu\XdebugToggle\Commands\XdebugToggle;

class XdebugToggleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/config/xdebug-toggle.php" => config_path('xdebug-toggle.php'),
        ], "config");

        if ($this->app->runningInConsole()) {
            $this->commands([
                XdebugToggle::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/xdebug-toggle.php", "xdebugtoggle");
    }
}
