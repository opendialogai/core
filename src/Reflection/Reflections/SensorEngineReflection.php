<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
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
        return [
            "available_sensors" => $this->getAvailableSensors()->toArray(),
        ];
    }
}
