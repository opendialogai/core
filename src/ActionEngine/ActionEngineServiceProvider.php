<?php

namespace OpenDialogAi\ActionEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngine;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;

class ActionEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-actionengine-custom.php' => config_path('opendialog/action_engine.php')
        ], 'opendialog-config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-actionengine.php', 'opendialog.action_engine');

        $this->app->bind(ActionEngineInterface::class, function () {
            $actionEngineService = new ActionEngine();
            $actionEngineService->setContextService(app()->make(ContextService::class));
            $actionEngineService->setAvailableActions(config('opendialog.action_engine.available_actions'));


            // Sets the custom actions if they have been published
            if (is_array(config('opendialog.action_engine.custom_actions'))) {
                $actionEngineService->setAvailableActions(config('opendialog.action_engine.custom_actions'));
            }

            return $actionEngineService;
        });
    }
}
