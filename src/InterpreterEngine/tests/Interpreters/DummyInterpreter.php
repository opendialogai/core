<?php

namespace OpenDialogAi\InterpreterEngine\tests\Interpreters;

use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class DummyInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.core.dummy';

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        return [Intent::createIntentWithConfidence('dummy', 1)];
    }
}
