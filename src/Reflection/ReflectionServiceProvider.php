<?php

namespace OpenDialogAi\Core\Reflection;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\ContextEngine\Contracts\ContextService;
use OpenDialogAi\Core\Reflection\Helper\ReflectionHelper;
use OpenDialogAi\Core\Reflection\Helper\ReflectionHelperInterface;
use OpenDialogAi\Core\Reflection\Reflections\ActionEngineReflection;
use OpenDialogAi\Core\Reflection\Reflections\ActionEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\AttributeEngineReflection;
use OpenDialogAi\Core\Reflection\Reflections\AttributeEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\ContextEngineReflection;
use OpenDialogAi\Core\Reflection\Reflections\ContextEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\InterpreterEngineReflection;
use OpenDialogAi\Core\Reflection\Reflections\InterpreterEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\OperationEngineReflection;
use OpenDialogAi\Core\Reflection\Reflections\OperationEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\ResponseEngineReflection;
use OpenDialogAi\Core\Reflection\Reflections\ResponseEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\SensorEngineReflection;
use OpenDialogAi\Core\Reflection\Reflections\SensorEngineReflectionInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use OpenDialogAi\SensorEngine\Service\SensorServiceInterface;

class ReflectionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ReflectionHelperInterface::class, function () {
            return new ReflectionHelper();
        });

        $this->app->singleton(ActionEngineReflectionInterface::class, function () {
            $actionEngine = resolve(ActionEngineInterface::class);
            return new ActionEngineReflection($actionEngine);
        });

        $this->app->singleton(AttributeEngineReflectionInterface::class, function () {
            $attributeResolver = resolve(AttributeResolver::class);
            $attributeTypeService = resolve(AttributeTypeServiceInterface::class);
            return new AttributeEngineReflection($attributeResolver, $attributeTypeService);
        });

        $this->app->singleton(ContextEngineReflectionInterface::class, function () {
            $contextService = resolve(ContextService::class);
            return new ContextEngineReflection($contextService);
        });

        $this->app->singleton(InterpreterEngineReflectionInterface::class, function () {
            $interpreterService = resolve(InterpreterServiceInterface::class);
            $configurationKey = 'opendialog.interpreter_engine';
            return new InterpreterEngineReflection($interpreterService, $configurationKey);
        });

        $this->app->singleton(OperationEngineReflectionInterface::class, function () {
            $operationService = resolve(OperationServiceInterface::class);
            return new OperationEngineReflection($operationService);
        });

        $this->app->singleton(ResponseEngineReflectionInterface::class, function () {
            $responseEngineService = resolve(ResponseEngineServiceInterface::class);
            return new ResponseEngineReflection($responseEngineService);
        });

        $this->app->singleton(SensorEngineReflectionInterface::class, function () {
            $sensorService = resolve(SensorServiceInterface::class);
            return new SensorEngineReflection($sensorService);
        });
    }
}
