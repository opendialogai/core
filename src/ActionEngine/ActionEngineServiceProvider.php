<?php

namespace OpenDialogAi\ActionEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngineService;
use OpenDialogAi\ActionEngine\Service\ActionEngineServiceInterface;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolverService;

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

        $this->app->bind(ActionEngineServiceInterface::class, function () {
            $actionEngineService = new ActionEngineService();
            $actionEngineService->setAttributeResolver(app()->make(AttributeResolverService::ATTRIBUTE_RESOLVER));

            return $actionEngineService;
        });
    }
}
