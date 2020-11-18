<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;

class ODControllerTest extends TestCase
{
    public function testBinding()
    {
        $this->assertEquals(OpenDialogController::class, get_class($this->app->make(OpenDialogController::class)));
    }

    //@todo Add OD Controller tests.
}
