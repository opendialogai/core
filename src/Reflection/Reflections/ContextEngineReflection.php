<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;

class ContextEngineReflection implements ContextEngineReflectionInterface
{
    /**
     * @inheritDoc
     */
    public function getAvailableContexts(): Map
    {
        return new Map();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "available_contexts" => $this->getAvailableContexts()->toArray(),
        ];
    }
}
