<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class ResponseEngineServiceProvider extends ServiceProvider
{
    const RESPONSE_ENGINE_SERVICE = 'response-engine-service';

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

        $this->app->bind(ResponseEngineServiceInterface::class, function () {
            $service = new ResponseEngineService();
            $service->setAttributeResolver(app()->make(AttributeResolver::class));
            $service->setContextService(app()->make(ContextService::class));
            return $service;
        });
    }
}
