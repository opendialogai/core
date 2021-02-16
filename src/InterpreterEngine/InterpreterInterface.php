<?php

namespace OpenDialogAi\InterpreterEngine;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\IntentCollection;

interface InterpreterInterface
{
    /**
     * Interprets an utterance and returns all matching intents in an array
     *
     * @param UtteranceAttribute $utterance
     * @return IntentCollection
     */
    public function interpret(UtteranceAttribute $utterance) : IntentCollection;
}
