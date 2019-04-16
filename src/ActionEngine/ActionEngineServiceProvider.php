<?php

namespace OpenDialogAi\ActionEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngine;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;

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

        $this->app->bind(ActionEngineInterface::class, function () {
            $actionEngineService = new ActionEngine();
            $actionEngineService->setAttributeResolver(app()->make(AttributeResolver::class));
            $actionEngineService->setContextService(app()->make(ContextService::class));
            $actionEngineService->setAvailableActions(config('opendialog.action_engine.available_actions'));
            return $actionEngineService;
        });
    }
}
