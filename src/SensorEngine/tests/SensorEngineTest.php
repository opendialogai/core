<?php

namespace OpenDialogAi\SensorEnging\Tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;
use OpenDialogAi\SensorEngine\Service\SensorEngine;

class SensorEngineTest extends TestCase
{
    public function testService()
    {
        $this->assertEquals(config('opendialog.sensor_engine.available_sensors'), $this->app->make(SensorEngine::class)->getAvailableSensors());
    }

    public function testWebchatSensor()
    {
        $webchatSensor = new WebchatSensor();
        $this->assertEquals('webchat', $webchatSensor->getName());
    }
}
