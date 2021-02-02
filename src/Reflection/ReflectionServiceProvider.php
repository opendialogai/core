<?php

namespace OpenDialogAi\Core\Reflection;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
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
            $reflection = new AttributeEngineReflection();

            return $reflection;
        });

        $this->app->singleton(ContextEngineReflectionInterface::class, function () {
            $reflection = new ContextEngineReflection();

            return $reflection;
        });

        $this->app->singleton(InterpreterEngineReflectionInterface::class, function () {
            $reflection = new InterpreterEngineReflection();

            return $reflection;
        });

        $this->app->singleton(OperationEngineReflectionInterface::class, function () {
            $reflection = new OperationEngineReflection();

            return $reflection;
        });

        $this->app->singleton(ResponseEngineReflectionInterface::class, function () {
            $reflection = new ResponseEngineReflection();

            return $reflection;
        });

        $this->app->singleton(SensorEngineReflectionInterface::class, function () {
            $reflection = new SensorEngineReflection();

            return $reflection;
        });
    }
}
