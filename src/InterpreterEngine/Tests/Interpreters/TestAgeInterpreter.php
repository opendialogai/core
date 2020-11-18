<?php

namespace OpenDialogAi\InterpreterEngine\Tests\Interpreters;

use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

/**
 * Will always return an intent with label 'my_age_is' and confidence 1 and 1 attribute:
 * - age = 21
 */
class TestAgeInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.test.age';

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        $intent = Intent::createIntentWithConfidence('my_age_is', 1);
        $intent->addAttribute(new IntAttribute('age', 21));
        $intent->addAttribute(new IntAttribute('dob_year', 1994));
        return [$intent];
    }
}
