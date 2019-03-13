<?php

namespace OpenDialogAi\Core\Tests;

use OpenDialogAi\ActionEngine\ActionEngineServiceProvider;
use OpenDialogAi\ActionEngine\Facades\ActionEngine;
use OpenDialogAi\ResponseEngine\Facades\ResponseEngine;
use OpenDialogAi\ResponseEngine\ResponseEngineServiceProvider;
use OpenDialogAi\Core\CoreServiceProvider;

/**
 * Base TestCase class for setting up all package tests
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp() :void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'testbench'
        ]);
    }

    public function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
            ActionEngineServiceProvider::class,
            ResponseEngineServiceProvider::class,
        ];
    }

    public function getPackageAliases($app)
    {
        return [
            ActionEngine::class,
            ResponseEngine::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        # Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'Acme\Testbench\Http\Kernel');
    }
}
