<?php

namespace OpenDialogAi\InterpreterEngine;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;

interface InterpreterInterface
{
    /**
     * Returns the name of the interpreter. The name should be in the format interpreter.{namespace}.{name}
     * eg interpreter.core.hello
     *
     * @return string
     * @throws InterpreterNameNotSetException
     */
    public static function getName() : string;

    /**
     * Interprets an utterance and returns all matching intents in an array
     *
     * @param UtteranceAttribute $utterance
     * @return IntentCollection
     */
    public function interpret(UtteranceAttribute $utterance) : IntentCollection;
}
