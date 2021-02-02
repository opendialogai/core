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

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "available_attributes" => $this->getAvailableAttributes()->toArray(),
            "available_attribute_types" => $this->getAvailableAttributeTypes()->toArray(),
        ];
    }
}
