<?php

namespace OpenDialogAi\Core\Tests\Bot\Interpreters;

use OpenDialogAi\AttributeEngine\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class TestInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.test.hello_bot';

    public function interpret(UtteranceInterface $utterance): array
    {
        $text = $utterance->getText();
        if (strpos($text, 'Hello') !== false) {
            $intent = Intent::createIntentWithConfidence('intent.test.hello_bot', 1);
            $intent->addAttribute(new StringAttribute('intent_test', 'test'));
            return [$intent];
        }

        return [];
    }
}
