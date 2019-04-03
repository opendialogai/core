<?php
namespace OpenDialogAi\InterpreterEngine\Service;
use OpenDialogAi\Core\Intents\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

/**
 * Deals with registering and exposing registered interpreters and interpreting utterances into Intents
 */
interface InterpreterServiceInterface
{
    /**
     * Takes an utterance and returns the matching intents with their match score
     *
     * @param UtteranceInterface $utterance
     * @return Intent[]
     */
    public function interpret(UtteranceInterface $utterance) : array;
}
