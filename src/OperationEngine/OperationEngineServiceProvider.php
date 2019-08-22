<?php

namespace OpenDialogAi\OperationEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\OperationEngine\Service\OperationService;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class OperationEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-operationengine-custom.php' => config_path('opendialog/operation_engine.php')
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-operationengine.php', 'opendialog.operation_engine');

        $this->app->singleton(OperationServiceInterface::class, function () {
            $operationService = new OperationService();
            $operationService->registerAvailableOperations(config('opendialog.operation_engine.available_operations'));

            $attributeResolver = $this->app->make(AttributeResolver::class);
            $operationService->setAttributeResolver($attributeResolver);

            return $operationService;
        });
    }
}
