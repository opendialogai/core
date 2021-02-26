<?php

namespace OpenDialogAi\GraphQLClient;

use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\GraphQLClient\Exceptions\MissingDGraphAuthTokenException;

class GraphQLClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-graphql-custom.php' => config_path('opendialog/graphql.php')
        ], 'opendialog-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-graphql.php', 'opendialog.graphql');
        $this->app->singleton(GraphQLClientInterface::class, function () {
            if ($dgraphAuthToken = config('opendialog.core.DGRAPH_AUTH_TOKEN')) {
                return new DGraphGraphQLClient(config('opendialog.core.DGRAPH_URL'), config('opendialog.core.DGRAPH_PORT'),
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

