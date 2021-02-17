<?php

namespace OpenDialogAi\SensorEngine\Service;

use OpenDialogAi\SensorEngine\BaseSensor;
use OpenDialogAi\SensorEngine\SensorInterface;

/**
 * Deals with registering and exposing registered sensors
 */
interface SensorServiceInterface
{
    /**
     * Returns a list of all available sensors keyed by name
     *
     * @return SensorInterface[]
     */
    public function getAvailableSensors() : array;

    /**
     * Checks if an sensor with the given name has been registered
     *
     * @param string $sensorName Should be in the format sensor.{namespace}.{name}
     * @return bool
     */
    public function isSensorAvailable(string $sensorName) : bool;

    /**
     * Registers sensors and stores them ready for use
     */
    public function registerAvailableSensors() : void;

    /**
     * Gets the registered sensor by name if it is registered
     * Should be in the format sensor.{namespace}.{name}
     *
     * @param $sensorName
     * @return SensorInterface
     */
    public function getSensor($sensorName) : SensorInterface;

    /**
     * Register a single sensor
     *
     * @param BaseSensor $sensor
     * @param $force
     */
    public function registerSensor(BaseSensor $sensor, $force = false): void;
}
