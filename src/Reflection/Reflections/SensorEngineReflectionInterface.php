<?php


namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\SensorEngine\SensorInterface;

interface SensorEngineReflectionInterface
{
    /**
     * @return Map|SensorInterface[]
     */
    public function getAvailableSensors(): Map;
}
