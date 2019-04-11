<?php


namespace OpenDialogAi\ConversationEngine;


use OpenDialogAi\ContextEngine\Contexts\UserContext;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

interface ConversationEngineInterface
{
    /**
     * Given a user context and an utterance determine what the next intent should be.
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return mixed
     */
    public function getNextIntent(UserContext $userContext, UtteranceInterface $utterance): Intent;
}
