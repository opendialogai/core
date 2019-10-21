<?php

namespace OpenDialogAi\SensorEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ResponseEngine\LinkClickInterface;
use OpenDialogAi\ResponseEngine\MySqlLinkClick;
use OpenDialogAi\SensorEngine\Service\SensorService;
use OpenDialogAi\SensorEngine\Service\SensorServiceInterface;

class SensorEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-sensorengine.php', 'opendialog.sensor_engine');

        $this->app->singleton(SensorServiceInterface::class, function () {
            $sensorEngine = new SensorService();
            $sensorEngine->registerAvailableSensors();
            return $sensorEngine;
        });

        $this->app->singleton(LinkClickInterface::class, function () {
            $mysqlLinkClick = new MySqlLinkClick();
            return $mysqlLinkClick;
        });
    }
}
