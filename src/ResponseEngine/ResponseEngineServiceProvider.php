<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

class ResponseEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-responseengine.php' => base_path('config/opendialog-responseengine.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-responseengine.php', 'opendialog.response_engine');

        $this->app->bind(ResponseEngine::RESPONSE_ENGINE_SERVICE, function () {
            return new ResponseEngineService();
        });
    }
}
