<?php

namespace OpenDialogAi\ConversationEngine;

use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

interface ConversationEngineInterface
{

    /**
     * @param InterpreterServiceInterface $interpreterService
     */
    public function setInterpreterService(InterpreterServiceInterface $interpreterService);

    /**
     * @param ActionEngineInterface $actionEngine
     */
    public function setActionEngine(ActionEngineInterface $actionEngine);

    /**
     * Given an utterance attribute.
     * @param UtteranceAttribute $utterance
     * @return Intent
     */
    public function getNextIntents(UtteranceAttribute $utterance): IntentCollection;

}
