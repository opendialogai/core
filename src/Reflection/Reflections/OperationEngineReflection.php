<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;

class OperationEngineReflection implements OperationEngineReflectionInterface
{
    /**
     * @inheritDoc
     */
    public function getAvailableOperations(): Map
    {
        return new Map();
    }
}
