<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;

class ResponseEngineReflection implements ResponseEngineReflectionInterface
{
    /**
     * @inheritDoc
     */
    public function getAvailableFormatters(): Map
    {
        return new Map();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "available_formatters" => $this->getAvailableFormatters()->toArray(),
        ];
    }
}
