<?php

namespace OpenDialogAi\InterpreterEngine\Tests\Interpreters;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class DummyInterpreter extends BaseInterpreter
{
    protected static string $componentId = 'interpreter.core.dummy';

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceAttribute $utterance): IntentCollection
    {
        $collection = new IntentCollection();
        $collection->add(Intent::createIntent('dummy', 1));
        return $collection;
    }
}
