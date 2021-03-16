<?php

namespace OpenDialogAi\ContextEngine;

use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\ContextEngine\Contexts\User\UserDataClient;
use OpenDialogAi\ContextEngine\ContextService\CoreContextService;
use OpenDialogAi\ContextEngine\Contracts\ContextService;
use OpenDialogAi\ContextEngine\DataClients\GraphAttributeDataClient;

class ContextEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-contextengine-custom.php' => config_path('opendialog/context_engine.php'),
        ], 'opendialog-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-contextengine.php', 'opendialog.context_engine');

        $this->app->singleton(ContextService::class, function () {
            $contextService = new CoreContextService();

            $contextService->loadContexts(config('opendialog.context_engine.supported_contexts'));

            if (is_array(config('opendialog.context_engine.custom_contexts'))) {
                $contextService->loadContexts(config('opendialog.context_engine.custom_contexts'));
            }

            return $contextService;
        });

        $this->app->singleton(GraphAttributeDataClient::class, function () {
            return new GraphAttributeDataClient();
        });
    }
}
