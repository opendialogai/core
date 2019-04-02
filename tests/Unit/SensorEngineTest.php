<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;

class SensorEngineTest extends TestCase
{
    public function testService()
    {
        $this->assertEquals(config('opendialog.sensor_engine.available_sensors'), $this->app->make('sensor-engine-service')->getAvailableSensors());
    }
}
