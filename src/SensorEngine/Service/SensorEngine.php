<?php

namespace OpenDialogAi\SensorEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\SensorEngine\Sensors\SensorInterface;

class SensorEngine
{
    const WEBCHAT_SENSOR = 'webchat-sensor';

    /**
     * @return SensorInterface[]
     */
    public function getAvailableSensors()
    {
        Log::debug('Getting available sensors');
        return config('opendialog.sensor_engine.available_sensors');
    }

    /**
     * @param string $sensorName
     * @return SensorInterface
     */
    public function getSensor($sensorName)
    {
        Log::debug("Getting sensor: {$sensorName}");
        return $this->{$sensorName};
    }

    /**
     * Register all available sensors.
     */
    public function registerSensors()
    {
        foreach ($this->getAvailableSensors() as $sensorName => $sensor) {
            Log::debug("Registering sensor: {$sensor}");
            $this->{$sensorName} = new $sensor();
        }
    }
}
