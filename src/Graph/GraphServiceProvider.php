<?php

namespace OpenDialogAi\Core\Graph;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class GraphServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-graph-custom.php' => config_path('opendialog/graph.php')
        ], 'opendialog-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-graph.php', 'opendialog.graph');

        $this->app->singleton(DGraphClient::class, function () {
            $schema = config('opendialog.graph.schema');

            if (is_string(config('opendialog.graph.custom_schema'))) {
                $schema .= config('opendialog.graph.custom_schema');
            }

            if($dgraphAuthToken = config('opendialog.core.DGRAPH_AUTH_TOKEN')) {
                return new DGraphClient(
                    config('opendialog.core.DGRAPH_URL'),
                    config('opendialog.core.DGRAPH_PORT'),
                    $dgraphAuthToken,
                    $schema
                );
            } else {
                throw new MissingDGraphAuthTokenException("Missing value for opendialog.core.DGRAPH_AUTH_TOKEN. You MUST specify a value for DGRAPH_AUTH_TOKEN!");
            }

        });
    }
}
