<?php

namespace OpenDialogAi\Core\NlpEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\Core\NlpEngine\Service\MsNlpService;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

/**
 * Class NlpEngineServiceProvider
 *
 * @package OpenDialogAi\Core\NlpEngine
 */
class NlpEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-nlpengine.php' => config_path('opendialog/nlp_engine.php')
        ], 'opendialog-config');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            NlpServiceInterface::class,
            MsNlpService::class
        );
    }
}
