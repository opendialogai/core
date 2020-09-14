<?php

namespace OpenDialogAi\OperationEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\OperationEngine\Service\OperationService;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class OperationEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-operationengine-custom.php' => config_path('opendialog/operation_engine.php')
        ], 'opendialog-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-operationengine.php', 'opendialog.operation_engine');

        $this->app->singleton(OperationServiceInterface::class, function () {
            $operationService = new OperationService();
            $operationService->registerAvailableOperations(config('opendialog.operation_engine.available_operations'));


            // Sets the custom operations if they have been published
            if (is_array(config('opendialog.operation_engine.custom_operations'))) {
                $operationService->registerAvailableOperations(config('opendialog.operation_engine.custom_operations'));
            }

            return $operationService;
        });
    }
}
