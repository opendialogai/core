<?php

namespace OpenDialogAi\Core\Tests\Unit\Interpreters;

use Barryvdh\Reflection\DocBlock\Type\Collection;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class NoNameInterpreter extends BaseInterpreter
{
    public function interpret(UtteranceAttribute $utterance): IntentCollection
    {
        $collection = new Collection();
        $collection->add(Intent::createIntent('test', 0));
        return $collection;
    }
}
