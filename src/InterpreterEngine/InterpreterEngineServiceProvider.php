<?php

namespace OpenDialogAi\InterpreterEngine;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use InterpreterEngine\Service\InterpreterService;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class InterpreterEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-interpreterengine.php' => base_path('config/opendialog-interpreterengine.php')
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-interpreterengine.php', 'opendialog.interpreter_engine');

        $this->app->bind(LuisClient::class, function () {
            $config = config('opendialog.interpreter_engine.luis_config');
            return new LuisClient(new Client(), $config);
        });

        $this->app->bind(InterpreterServiceInterface::class, function () {
            $interpreterService = new InterpreterService();
            $interpreterService->registerAvailableInterpreters();

            return $interpreterService;
        });
    }
}
