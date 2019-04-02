<?php

namespace OpenDialogAi\SensorEngine\Service;

class SensorEngineService
{
    /**
     * @return SensorInterface[]
     */
    public function getAvailableSensors()
    {
        \Log::debug('Getting available sensors');
        return config('opendialog.sensor_engine.available_sensors');
    }

    /**
     * @return SensorInterface
     */
    public function getSensor($sensorName)
    {
        \Log::debug("Getting sensor: {$sensorName}");
    }
}
