<?php

namespace OpenDialogAi\ActionEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Facades\ActionEngine;
use OpenDialogAi\ActionEngine\Service\ActionEngineService;

class ActionEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-actionengine.php' => base_path('config/opendialog-actionengine.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-actionengine.php', 'opendialog.action_engine');

        $this->app->bind(ActionEngine::ACTION_ENGINE_SERVICE, function () {
            return new ActionEngineService();
        });
    }
}
