<?php

namespace OpenDialogAi\InterpreterEngine\Tests\Interpreters;

use OpenDialogAi\AttributeEngine\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

/**
 * Will always return an intent with label 'my_name_is' and confidence 1 and 2 attributes:
 * - first_name = first_name
 * - last_name = last_name
 */
class TestNameInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.test.name';

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        $intent = Intent::createIntentWithConfidence('my_name_is', 1);
        $intent->addAttribute(new StringAttribute('first_name', 'first_name'));
        $intent->addAttribute(new StringAttribute('last_name', 'last_name'));
        return [$intent];
    }
}
