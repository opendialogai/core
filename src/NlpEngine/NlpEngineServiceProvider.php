<?php

namespace OpenDialogAi\Core\NlpEngine;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\Core\NlpEngine\Service\MsNlpService;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

class NlpEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/config/opendialog-nlpengine.php' => config_path('opendialog/nlp_engine.php'),
            ],
            'opendialog-config'
        );
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'MsClient',
            function () {
                $client = new Client(
                    [
                        'base_uri' => config('opendialog.nlp_engine.ms_api_url'),
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Ocp-Apim-Subscription-Key' => config('opendialog.nlp_engine.ms_api_key'),
                        ],
                    ]
                );

                return $client;
            }
        );
        $this->app->singleton(
            'MsNlpServiceBind',
            function ($app, $params) {
                $client = new MsNlpService($params['text'], new MsClient());
                return $client;
            }
        );

        $this->app->bind(
            NlpServiceInterface::class,
            function ($app) {
                $app()->make('MsNlpService');
            }
        );
    }
}
