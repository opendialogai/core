<?php

namespace OpenDialogAi\Core\Tests\Unit;

use Intents\InterpreterNameNotSetException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Unit\Interpreters\NoNameInterpreter;

class InterpreterTest extends TestCase
{
    public function testInterpreterNoName()
    {
        try {
            NoNameInterpreter::getName();
            $this->fail('Should have thrown an exception');
        } catch (InterpreterNameNotSetException $e) {
            $this->assertNotNull($e);
        }
    }
}
