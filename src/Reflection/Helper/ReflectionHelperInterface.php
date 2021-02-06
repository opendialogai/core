<?php

namespace OpenDialogAi\Core\Reflection\Helper;

use OpenDialogAi\Core\Reflection\Reflections\ActionEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\AttributeEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\ContextEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\InterpreterEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\OperationEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\ResponseEngineReflectionInterface;
use OpenDialogAi\Core\Reflection\Reflections\SensorEngineReflectionInterface;

interface ReflectionHelperInterface
{
    /**
     * @return ActionEngineReflectionInterface
     */
    public function getActionEngineReflection(): ActionEngineReflectionInterface;

    /**
     * @return AttributeEngineReflectionInterface
     */
    public function getAttributeEngineReflection(): AttributeEngineReflectionInterface;

    /**
     * @return ContextEngineReflectionInterface
     */
    public function getContextEngineReflection(): ContextEngineReflectionInterface;

    /**
     * @return InterpreterEngineReflectionInterface
     */
    public function getInterpreterEngineReflection(): InterpreterEngineReflectionInterface;

    /**
     * @return OperationEngineReflectionInterface
     */
    public function getOperationEngineReflection(): OperationEngineReflectionInterface;

    /**
     * @return ResponseEngineReflectionInterface
     */
    public function getResponseEngineReflection(): ResponseEngineReflectionInterface;

    /**
     * @return SensorEngineReflectionInterface
     */
    public function getSensorEngineReflection(): SensorEngineReflectionInterface;
}
