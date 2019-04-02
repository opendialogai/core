<?php


namespace OpenDialogAi\ContextEngine;


use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;

class ContextEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-contextengine.php' => base_path('config/opendialog-contextengine.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-contextengine.php', 'opendialog.context_engine');

        $this->app->singleton(ContextService::CONTEXT_SERVICE, function () {
            return new ContextService();
        });
    }
}
