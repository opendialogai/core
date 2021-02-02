<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;

class SensorEngineReflection implements SensorEngineReflectionInterface
{
    /**
     * @inheritDoc
     */
    public function getAvailableSensors(): Map
    {
        return new Map();
    }
}
