<?php

namespace OpenDialogAi\Core\Tests\Unit\Interpreters;

use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class NoNameInterpreter extends BaseInterpreter
{
    public function interpret(UtteranceInterface $utterance): array
    {
        return [Intent::createIntentWithConfidence('test', 0)];
    }
}
