<?php

namespace OpenDialogAi\SensorEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\SensorEngine\Service\SensorService;

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

        $this->app->bind(SensorInterface::class, function () {
            $sensorEngine = new SensorService();
            $sensorEngine->registerAvailableSensors();
            return $sensorEngine;
        });
    }
}
