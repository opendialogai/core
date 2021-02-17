<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\SensorEngine\BaseSensor;
use OpenDialogAi\SensorEngine\Service\SensorServiceInterface;

class SensorEngineReflection implements SensorEngineReflectionInterface
{
    /** @var SensorServiceInterface */
    private $sensorService;

    /**
     * SensorEngineReflection constructor.
     * @param SensorServiceInterface $sensorService
     */
    public function __construct(SensorServiceInterface $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableSensors(): Map
    {
        return new Map($this->sensorService->getAvailableSensors());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $sensors = $this->getAvailableSensors();

        $sensorsWithData = array_map(function ($sensor) {
            /** @var $sensor BaseSensor */
            return [
                'component_data' => (array) $sensor::getComponentData(),
            ];
        }, $sensors->toArray());

        return [
            "available_sensors" => $sensorsWithData,
        ];
    }
}
