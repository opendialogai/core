<?php

namespace OpenDialogAi\SensorEnging\Tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\SensorEngine\Exceptions\SensorNotRegisteredException;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;
use OpenDialogAi\SensorEngine\Service\SensorService;

class SensorServiceTest extends TestCase
{
    public function testService()
    {
        $sensors = $this->app->make(SensorService::class)->getAvailableSensors();
        $this->assertCount(1, $sensors);
        $this->assertContains('sensor.core.webchat', array_keys($sensors));
    }

    public function testUnknownService()
    {
        $sensorService = $this->app->make(SensorService::class);
        $this->expectException(SensorNotRegisteredException::class);
        $sensorService->getSensor('sensor.core.unknown');
    }

    public function testWebchatSensor()
    {
        $webchatSensor = new WebchatSensor();
        $this->assertEquals('sensor.core.webchat', $webchatSensor->getName());
    }
}
