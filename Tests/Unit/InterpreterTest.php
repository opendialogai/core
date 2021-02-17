<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Components\Exceptions\MissingRequiredComponentDataException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Unit\Interpreters\NoNameInterpreter;

class InterpreterTest extends TestCase
{
    public function testInterpreterNoName()
    {
        $this->expectException(MissingRequiredComponentDataException::class);
        NoNameInterpreter::getComponentId();
    }
}
