<?php

namespace OpenDialogAi\InterpreterEngine\tests\Interpreters;

use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class NoNameInterpreter extends BaseInterpreter
{
    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        return [Intent::createIntentWithConfidence('dummy', 1)];
    }
}
