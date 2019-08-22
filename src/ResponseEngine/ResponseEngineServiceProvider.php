<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class ResponseEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->app->bind(ResponseEngineServiceInterface::class, function () {
            $service = new ResponseEngineService();
            return $service;
        });
    }
}
