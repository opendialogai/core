<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class ResponseEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->app->bind(ResponseEngineServiceInterface::class, function () {
            $responseEngineService = new ResponseEngineService();

            $attributeResolver = $this->app->make(AttributeResolver::class);
            $responseEngineService->setAttributeResolver($attributeResolver);

            $contextService = $this->app->make(ContextService::class);
            $responseEngineService->setContextService($contextService);

            $operationService = $this->app->make(OperationServiceInterface::class);
            $responseEngineService->setOperationService($operationService);

            return $responseEngineService;
        });
    }
}
