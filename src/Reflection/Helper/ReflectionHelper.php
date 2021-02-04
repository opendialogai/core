<?php

namespace OpenDialogAi\Core\Reflection\Helper;


use OpenDialogAi\Core\Reflection\Reflections\ActionEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\AttributeEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\ContextEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\InterpreterEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\OperationEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\ResponseEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\SensorEngineReflectionInterface;

class ReflectionHelper implements ReflectionHelperInterface
{
    /**
     * @inheritDoc
     */
    public function getActionEngineReflection(): ActionEngineReflectionInterface
    {
        return resolve(ActionEngineReflectionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeEngineReflection(): AttributeEngineReflectionInterface
    {
        return resolve(AttributeEngineReflectionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getContextEngineReflection(): ContextEngineReflectionInterface
    {
        return resolve(ContextEngineReflectionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getInterpreterEngineReflection(): InterpreterEngineReflectionInterface
    {
        return resolve(InterpreterEngineReflectionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getOperationEngineReflection(): OperationEngineReflectionInterface
    {
        return resolve(OperationEngineReflectionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getResponseEngineReflection(): ResponseEngineReflectionInterface
    {
        return resolve(ResponseEngineReflectionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getSensorEngineReflection(): SensorEngineReflectionInterface
    {
        return resolve(SensorEngineReflectionInterface::class);
    }
}
