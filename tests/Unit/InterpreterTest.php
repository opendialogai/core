<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Unit\Interpreters\NoNameInterpreter;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;

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
