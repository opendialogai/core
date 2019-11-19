<?php

namespace OpenDialogAi\Core\Tests\Bot\Interpreters;

use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractCompositeAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class TestInterpreterComposite extends BaseInterpreter
{
    protected static $name = 'interpreter.test.hello_bot_comp';

    public function interpret(UtteranceInterface $utterance): array
    {
        try {
            $text = $utterance->getText();

            if (strpos($text, 'Hello') !== false) {
                $intent = Intent::createIntentWithConfidence('intent.test.hello_bot_comp', 1);

                $intent->addAttribute(new StringAttribute('intent_test', 'test'));
                $intent->addAttribute(new ArrayAttribute('array_test', ['ok']));
                $intent->addAttribute(
                    new ExampleAbstractCompositeAttribute(
                        'result_test',
                        new ExampleAbstractAttributeCollection(
                            array(['id' => 'one', 'value' => 'go']),
                            'array'
                        )
                    )
                );

                return [$intent];
            }
        } catch (FieldNotSupported $e) {
            //
        }

        return [];
    }
}
