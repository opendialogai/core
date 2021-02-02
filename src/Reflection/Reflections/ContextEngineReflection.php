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
}
