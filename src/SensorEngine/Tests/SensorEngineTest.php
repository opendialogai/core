<?php

namespace OpenDialogAi\SensorEnging\Tests;

use OpenDialogAi\Core\SensorEngine\Tests\Sensors\DummySensor;
use OpenDialogAi\Core\SensorEngine\Tests\Sensors\TestSensor;
use OpenDialogAi\Core\SensorEngine\Tests\Sensors\TestSensor2;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\SensorEngine\Exceptions\SensorNotRegisteredException;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;
use OpenDialogAi\SensorEngine\Service\SensorServiceInterface;

class SensorServiceTest extends TestCase
{
    public function testService()
    {
        $sensors = $this->app->make(SensorServiceInterface::class)->getAvailableSensors();
        $this->assertCount(1, $sensors);
        $this->assertContains('sensor.core.webchat', array_keys($sensors));
    }

    public function testUnknownService()
    {
        $sensorService = $this->app->make(SensorServiceInterface::class);
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
        $this->app['config']->set(
            'opendialog.sensor_engine.available_sensors',
            [DummySensor::class]
        );

        /** @var SensorServiceInterface $sensorService */
        $sensorService = $this->app->make(SensorServiceInterface::class);

        $this->assertCount(0, $sensorService->getAvailableSensors());
    }

    public function testRegisteringSingleSensor()
    {
        $sensorService = $this->app->make(SensorServiceInterface::class);
        $this->assertCount(1, $sensorService->getAvailableSensors());

        $testSensor = new TestSensor();
        $sensorService->registerSensor($testSensor);

        $this->assertCount(2, $sensorService->getAvailableSensors());
        $this->assertEquals($testSensor, $sensorService->getSensor(TestSensor::getName()));
    }

    public function testRegisteringSingleSensorAlreadyRegistered()
    {
        $this->app['config']->set(
            'opendialog.sensor_engine.available_sensors',
            [TestSensor::class]
        );

        $sensorService = $this->app->make(SensorServiceInterface::class);

        $this->assertCount(1, $sensorService->getAvailableSensors());

        $testSensor = new TestSensor2();
        $sensorService->registerSensor($testSensor);

        $this->assertCount(1, $sensorService->getAvailableSensors());
        $this->assertEquals(TestSensor::class, get_class($sensorService->getSensor(TestSensor::getName())));
    }

    public function testForcingSingleSensorAlreadyRegistered()
    {
        $this->app['config']->set(
            'opendialog.sensor_engine.available_sensors',
            [TestSensor::class]
        );

        $sensorService = $this->app->make(SensorServiceInterface::class);

        $this->assertCount(1, $sensorService->getAvailableSensors());

        $sensorService->registerSensor(new TestSensor2(), true);

        $this->assertCount(1, $sensorService->getAvailableSensors());
        $this->assertEquals(TestSensor2::class, get_class($sensorService->getSensor(TestSensor::getName())));
    }
}
