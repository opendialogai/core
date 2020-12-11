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

            return new DGraphClient(
                config('opendialog.core.DGRAPH_URL'),
                config('opendialog.core.DGRAPH_PORT'),
                $schema
            );
        });
    }
}
