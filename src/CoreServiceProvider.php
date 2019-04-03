<?php

namespace OpenDialogAi\Core;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\Core\Controllers\OpenDialogController;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/opendialog.php' => base_path('config/opendialog.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/opendialog.php', 'opendialog.core');

        $this->app->singleton(OpenDialogController::class, function () {
            return new OpenDialogController();
        });
    }
}
