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
        if ($this->app->runningInConsole()) {
            $this->commands([
                XdebugToggle::class,
            ]);
        }
    }
}
