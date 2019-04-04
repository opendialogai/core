<?php

namespace OpenDialogAi\SensorEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\SensorEngine\Service\SensorEngineService;

class SensorEngineServiceProvider extends ServiceProvider
{
    const SENSOR_ENGINE_SERVICE = 'sensor-engine-service';

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-sensorengine.php'
                => base_path('config/opendialog-sensorengine.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-sensorengine.php', 'opendialog.sensor_engine');

        $this->app->bind(self::SENSOR_ENGINE_SERVICE, function () {
            $sensorEngine = new SensorEngineService();
            $sensorEngine->registerSensors();
            return $sensorEngine;
        });
    }
}
