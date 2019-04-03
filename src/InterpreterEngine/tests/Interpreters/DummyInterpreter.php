<?php

namespace OpenDialogAi\InterpreterEngine\tests\Interpreters;

use Intents\BaseInterpreter;
use OpenDialogAi\Core\Intents\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class DummyInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.core.dummy';

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        return [new Intent('dummy', 1)];
    }
}