<?php

namespace OpenDialogAi\SensorEngine;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ResponseEngine\MySqlLinkClick;
use OpenDialogAi\ResponseEngine\LinkClickInterface;
use OpenDialogAi\SensorEngine\Http\Requests\IncomingWebchatMessage;
use OpenDialogAi\SensorEngine\Service\SensorService;
use OpenDialogAi\SensorEngine\Contracts\IncomingMessageInterface;

class SensorEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-sensorengine.php', 'opendialog.sensor_engine');

        $this->app->singleton(SensorInterface::class, function () {
            $sensorEngine = new SensorService();
            $sensorEngine->registerAvailableSensors();
            return $sensorEngine;
        });

        $this->app->bind(LinkClickInterface::class, function () {
            $mysqlLinkClick = new MySqlLinkClick();
            return $mysqlLinkClick;
        });

        $this->app->bind(IncomingMessageInterface::class, IncomingWebchatMessage::class);
    }
}
