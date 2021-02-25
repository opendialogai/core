<?php

namespace OpenDialogAi\GraphQLClient;

use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\GraphQLClient\GraphQLClient;

class GraphQLClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
//        $this->publishes([
//            __DIR__ . '/config/opendialog-attributeengine-custom.php' => config_path('opendialog/attribute_engine.php'),
//        ], 'opendialog-config');
    }

    public function register()
    {
        $this->app->singleton(GraphQLClient::class, function () {
            if ($dgraphAuthToken = config('opendialog.core.DGRAPH_AUTH_TOKEN')) {
                return new GraphQLClient(config('opendialog.core.DGRAPH_URL'), config('opendialog.core.DGRAPH_PORT'),
                    [
                        'X-Dgraph-AuthToken' => $dgraphAuthToken
                    ]);
            } else {
                throw new MissingDGraphAuthTokenException("Missing value for opendialog.core. DGRAPH_AUTH_TOKEN.".
                    " You MUST specify a value for DGRAPH_AUTH_TOKEN!");
            }
        });
    }
}

