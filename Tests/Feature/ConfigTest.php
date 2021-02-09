<?php


namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\Core\CoreServiceProvider;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\GraphServiceProvider;
use OpenDialogAi\Core\Graph\MissingDGraphAuthTokenException;

use Orchestra\Testbench\TestCase;

class ConfigTest extends TestCase {


    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
            GraphServiceProvider::class,
        ];
    }

    /**
     * Check that absence of DGRAPH_AUTH_TOKEN throws an exception.
     */
    public function testMissingDGraphAuthTokenConfig() {

        $this->app['config']->set('opendialog.core.DGRAPH_AUTH_TOKEN', null);

        $this->expectException(MissingDGraphAuthTokenException::class);
        resolve(DGraphClient::class);
    }
}
