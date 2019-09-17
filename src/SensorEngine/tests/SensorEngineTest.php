<?php

namespace OpenDialogAi\SensorEnging\Tests;

use OpenDialogAi\Core\SensorEngine\tests\Sensors\DummySensor;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\SensorEngine\Exceptions\SensorNotRegisteredException;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;
use OpenDialogAi\SensorEngine\Service\SensorService;
use OpenDialogAi\SensorEngine\Service\SensorServiceInterface;

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

    public function testBadlyNamedFormatter()
    {
        $this->app['config']->set('opendialog.sensor_engine.available_sensors', [DummySensor::class]);

        /** @var SensorServiceInterface $sensorService */
        $sensorService = $this->app->make(SensorService::class);

        $this->assertCount(0, $sensorService->getAvailableSensors());
    }
}
