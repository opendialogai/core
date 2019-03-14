<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ActionEngine\Action;
use OpenDialogAi\Core\Tests\TestCase;

class ActionEngineTest extends TestCase
{
    public function testService()
    {
        $this->assertEquals(config('opendialog.action_engine.available_actions'), $this->app->make('action-engine-service')->getAvailableActions());
    }

    public function testActionsDb()
    {
        Action::create(['name' => 'test']);
        $this->assertEquals('test', Action::where('name', 'test')->first()->name);
    }
}
