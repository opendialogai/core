<?php

namespace OpenDialogAi\SensorEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\SensorEngine\BaseSensor;
use OpenDialogAi\SensorEngine\Exceptions\SensorNotRegisteredException;
use OpenDialogAi\SensorEngine\SensorInterface;

class SensorService implements SensorServiceInterface
{
    /** @var string A regex pattern for a valid sensor name */
    private $validNamePattern = "/^sensor\.[a-z]*\.[a-z_]*$/";

    /**
     * A place to store a cache of available sensors
     * @var SensorInterface[]
     */
    private $availableSensors = [];

    /**
     * @inheritdoc
     */
    public function getAvailableSensors(): array
    {
        if (empty($this->availableSensors)) {
            Log::debug('Getting available sensors');
            $this->registerAvailableSensors();
        }

        return $this->availableSensors;
    }

    /**
     * @param string $sensorName
     * @return SensorInterface
     */
    public function getSensor($sensorName): SensorInterface
    {
        Log::debug("Getting sensor: {$sensorName}");
        if ($this->isSensorAvailable($sensorName)) {
            Log::debug(sprintf("Getting sensor with name %s", $sensorName));
            return $this->availableSensors[$sensorName];
        }

        throw new SensorNotRegisteredException("Sensor with name $sensorName is not available");
    }

    /**
     * @inheritdoc
     */
    public function isSensorAvailable(string $sensorName): bool
    {
        if (in_array($sensorName, array_keys($this->getAvailableSensors()))) {
            Log::debug(sprintf("Sensor with name %s is available", $sensorName));
            return true;
        }

        Log::debug(sprintf("Sensor with name %s is not available", $sensorName));
        return false;
    }

    /**
     * Loops through all available sensors from config, and creates a local array keyed by the name of the
     * sensor
     */
    public function registerAvailableSensors() : void
    {
        /** @var BaseSensor $sensor */
        foreach ($this->getAvailableSensorConfig() as $sensor) {
            $id = $sensor::getComponentId();

            if ($this->isValidName($id)) {
                $sensor::getComponentData();
                $this->availableSensors[$id] = new $sensor();
            } else {
                Log::warning(
                    sprintf("Not adding sensor with ID %s. ID is in wrong format", $id)
                );
            }
        }
    }

    /**
     * Checks if the name of the sensor is in the right format
     *
     * @param string $name
     * @return bool
     */
    private function isValidName(string $name) : bool
    {
        return preg_match($this->validNamePattern, $name) === 1;
    }

    /**
     * Returns the list of available sensors as registered in the available_sensors config
     *
     * @return SensorInterface[]
     */
    private function getAvailableSensorConfig()
    {
        return config('opendialog.sensor_engine.available_sensors');
    }

    /**
     * Register a single sensor.
     * Will not register if a sensor of that name is already registered
     *
     * @param BaseSensor $sensor
     * @param bool $force
     */
    public function registerSensor(BaseSensor $sensor, $force = false): void
    {
        $id = $sensor::getComponentId();

        if ($force || !isset($this->availableSensors[$id])) {
            $sensor::getComponentData();
            Log::debug(sprintf('Adding a single sensor %s %s', $id, get_class($sensor)));
            $this->availableSensors[$id] = $sensor;
        } else {
            Log::warning(
                sprintf(
                    'Not registering sensor with ID %s - already registered as %s',
                    $id,
                    get_class($this->getSensor($id))
                )
            );
        }
    }
}
