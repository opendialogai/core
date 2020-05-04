<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\ResponseEngine\Observers\MessageTemplateObserver;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class ResponseEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        // Handle conversation life-cycle events.
        MessageTemplate::observe(MessageTemplateObserver::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/opendialog-responseengine.php',
            'opendialog.response_engine'
        );

        $this->app->singleton(ResponseEngineServiceInterface::class, function () {
            $service = new ResponseEngineService();

            $operationService = $this->app->make(OperationServiceInterface::class);
            $service->setOperationService($operationService);

            return $service;
        });
    }
}
