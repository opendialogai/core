<?php

namespace OpenDialogAi\Core\Intents;

use Intents\InterpreterNameNotSetException;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

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
     * @param UtteranceInterface $utterance
     * @return array
     */
    public function interpret(UtteranceInterface $utterance) : array;
}
