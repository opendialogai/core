<?php

namespace OpenDialogAi\SensorEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\SensorEngine\Service\SensorEngine;

class SensorEngineServiceProvider extends ServiceProvider
{
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

        $this->app->bind(SensorEngine::class, function () {
            $sensorEngine = new SensorEngine();
            $sensorEngine->registerSensors();
            return $sensorEngine;
        });
    }
}
