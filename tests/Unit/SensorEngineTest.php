<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;

class SensorEngineTest extends TestCase
{
    public function testService()
    {
        $this->assertEquals(config('opendialog.sensor_engine.available_sensors'), $this->app->make('sensor-engine-service')->getAvailableSensors());
    }

    public function testWebchatSensor()
    {
        $webchatSensor = new WebchatSensor();
        $this->assertEquals('webchat', $webchatSensor->getName());
    }
}
