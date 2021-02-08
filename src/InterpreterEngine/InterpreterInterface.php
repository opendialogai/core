<?php

namespace OpenDialogAi\InterpreterEngine;

use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

interface InterpreterInterface
{
    /**
     * Interprets an utterance and returns all matching intents in an array
     *
     * @param UtteranceInterface $utterance
     * @return Intent[]
     * @throws FieldNotSupported
     */
    public function interpret(UtteranceInterface $utterance) : array;
}
