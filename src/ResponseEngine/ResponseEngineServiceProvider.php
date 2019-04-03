<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

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

        $this->app->bind(self::RESPONSE_ENGINE_SERVICE, function () {
            $service = new ResponseEngineService();
            $service->setAttributeResolver(app()->make(AttributeResolverService::class));
            return $service;
        });
    }
}
