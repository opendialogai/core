<?php

namespace OpenDialogAi\InterpreterEngine\tests\Interpreters;

use Intents\BaseInterpreter;
use OpenDialogAi\Core\Intents\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class NoNameInterpreter extends BaseInterpreter
{
    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        return [new Intent('dummy', 1)];
    }
}