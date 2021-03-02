<?php

namespace OpenDialogAi\GraphQLClient;

use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\GraphQLClient\Exceptions\GraphQLClientConfigException;

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

            $baseUrl = config('opendialog.graphql.DGRAPH_BASE_URL');
            $port = config('opendialog.graphql.DGRAPH_PORT');
            if (!$baseUrl) {
                throw new GraphQLClientConfigException("opendialog.graphql.DGRAPH_BASE_URL config value is missing. You must set a value, e.g https://example.com/");
            }
            if (!$port) {
                throw new GraphQLClientConfigException("opendialog.graphql.DGRAPH_PORT config value is missing. You must set a a port number, e.g 443");
            }

            $instanceType = config('opendialog.graphql.DGRAPH_INSTANCE_TYPE');
            if (!$instanceType) {
                throw new GraphQLClientConfigException("opendialog.graphql.DGRAPH_INSTANCE_TYPE config value is missing. If you are connecting to your own DGraph instance, set it to 'DGRAPH'. If you are connecting to a SlashGraphQL instance, use 'SLASH_GRAPHQL'.");
            }

            $headers = [];
            if ($instanceType === 'DGRAPH') {
                if ($authToken = config('opendialog.graphql.DGRAPH_AUTH_TOKEN')) {
                    $headers['X-Dgraph-AuthToken'] = $authToken;
                } else {
                    throw new GraphQLClientConfigException("opendialog.graphql.DGRAPH_AUTH_TOKEN config value is missing. You must set this value when using 'DGRAPH' for the opendialog.graphql.DGRAPH_INSTANCE_TYPE");
                }
            }
            if ($instanceType === 'SLASH_GRAPHQL') {
                if ($apiKey = config('opendialog.graphql.SLASH_GRAPHQL_API_KEY')) {
                    $headers['X-Auth-Token'] = $apiKey;
                } else {
                    throw new GraphQLClientConfigException("opendialog.graphql.SLASH_GRAPHQL_API_KEY config value is missing. You must set this value when using 'SLASH_GRAPHQL' for the opendialog.graphql.DGRAPH_INSTANCE_TYPE");
                }
            }

            return new DGraphGraphQLClient($baseUrl, $port, $headers);
        });
    }
}

