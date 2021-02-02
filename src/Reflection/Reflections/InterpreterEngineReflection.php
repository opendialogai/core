<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;

class InterpreterEngineReflection implements InterpreterEngineReflectionInterface
{
    /**
     * @inheritDoc
     */
    public function getAvailableInterpreters(): Map
    {
        return new Map();
    }

    /**
     * @inheritDoc
     */
    public function getEngineConfiguration(): InterpreterEngineConfiguration
    {
        return new InterpreterEngineConfiguration([]);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "available_interpreters" => $this->getAvailableInterpreters()->toArray(),
            "engine_configuration" => $this->getEngineConfiguration()
        ];
    }
}
