<?php

namespace OpenDialogAi\Core\Tests\Bot\Interpreters;

use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\Test\ExampleAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\Test\ExampleAbstractCompositeAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class TestInterpreterComposite extends BaseInterpreter
{
    protected static $name = 'interpreter.test.hello_bot_comp';

    public function interpret(UtteranceInterface $utterance): array
    {
        $text = $utterance->getText();
        if (strpos($text, 'Hello') !== false) {
            $intent = Intent::createIntentWithConfidence('intent.test.hello_bot_comp', 1);

            $intent->addAttribute(new ArrayAttribute('array_test', ['ok']));
            $intent->addAttribute(
                new ExampleAbstractCompositeAttribute(
                    'result_test',
                    new ExampleAbstractAttributeCollection(
                        ['id' => 'one', 'value' => 'go'],
                        ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY
                    )
                )
            );

            return [$intent];
        }

        return [];
    }
}
