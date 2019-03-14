<?php


namespace OpenDialogAi\AttributeEngine;


use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\AttributeEngine\ContextManager\ContextService;

class AttributeEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-attributeengine.php' => base_path('config/opendialog-attributeengine.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-attributeengine.php', 'opendialog.attribute_engine');

        $this->app->singleton(AttributeResolverService::ATTRIBUTE_RESOLVER, function () {
            return new AttributeResolverService();
        });

        $this->app->singleton(ContextService::CONTEXT_SERVICE, function () {
            return new ContextService();
        });
    }
}
