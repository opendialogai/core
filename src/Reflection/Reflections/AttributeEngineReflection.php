<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;

class AttributeEngineReflection implements AttributeEngineReflectionInterface
{
    /**
     * @inheritDoc
     */
    public function getAvailableAttributes(): Map
    {
        return new Map();
    }

    /**
     * @inheritDoc
     */
    public function getAvailableAttributeTypes(): Map
    {
        return new Map();
    }
}
