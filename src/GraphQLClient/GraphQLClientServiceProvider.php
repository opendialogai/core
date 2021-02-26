<?php

namespace OpenDialogAi\GraphQLClient;

use Carbon\Laravel\ServiceProvider;

class GraphQLClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/opendialog-graphql-custom.php' => config_path('opendialog/graphql.php')
        ], 'opendialog-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/opendialog-graphql.php', 'opendialog.graphql');
        $this->app->singleton(GraphQLClientInterface::class, function () {

            return new DGraphGraphQLClient(config('opendialog.graphql.DGRAPH_URL'), config('opendialog.graphql.DGRAPH_PORT'), [
                    'X-Dgraph-AuthToken' => config('opendialog.graphql.DGRAPH_AUTH_TOKEN')
                ]);

        });
    }
}

