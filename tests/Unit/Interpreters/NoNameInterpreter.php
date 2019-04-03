<?php

namespace OpenDialogAi\Core\Tests\Unit\Interpreters;

use Intents\BaseInterpreter;
use OpenDialogAi\Core\Intents\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class NoNameInterpreter extends BaseInterpreter
{
    public function interpret(UtteranceInterface $utterance): array
    {
        return [new Intent('test', 0)];
    }
}