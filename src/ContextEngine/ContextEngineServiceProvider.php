<?php


namespace OpenDialogAi\ContextEngine;


use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class ContextEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-contextengine.php' => base_path('config/opendialog-contextengine.php'),

        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-contextengine.php', 'opendialog.context_engine');

        $this->app->singleton(ContextService::class, function () {
            $contextService = new ContextService();
            $contextService->setUserService($this->app->make(UserService::class));
            return $contextService;
        });

        $this->app->singleton(AttributeResolver::class, function () {
            return new AttributeResolver();
        });

        $this->app->singleton(UserService::class, function () {
            $userService = new UserService(
                new DGraphClient(
                    config('opendialog.core.DGRAPH_URL'),
                    config('opendialog.core.DGRAPH_PORT')
                )
            );
            $userService->setAttributeResolver($this->app->make(AttributeResolver::class));
            return $userService;
        });
    }
}
