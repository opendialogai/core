<?php

namespace OpenDialogAi\InterpreterEngine\Tests\Interpreters;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;

class NoNameInterpreter extends BaseInterpreter
{
    /**
     * @inheritdoc
     */
    public function interpret(UtteranceAttribute $utterance): IntentCollection
    {
        $collection = new IntentCollection();
        $collection->add(Intent::createIntent('dummy', 1));
        return $collection;
    }

    public static function getName(): string
    {
        // TODO: Implement getName() method.
    }
}
