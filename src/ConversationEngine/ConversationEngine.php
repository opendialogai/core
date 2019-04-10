<?php


namespace OpenDialogAi\ConversationEngine;


use Ds\Map;
use Illuminate\Support\Facades\Log;
use InterpreterEngine\Service\InterpreterService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\ContextEngine\Contexts\UserContext;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class ConversationEngine implements ConversationEngineInterface
{
    /* @var ConversationStoreInterface */
    private $conversationStore;

    public function setConversationStore(ConversationStoreInterface $conversationStore)
    {
        $this->conversationStore = $conversationStore;
    }

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Intent
     */
    public function getNextIntent(UserContext $userContext, UtteranceInterface $utterance): Intent
    {
        /* @var Conversation $ongoingConversation */
        $ongoingConversation = $this->determineCurrentConversation($userContext, $utterance);

        /* @var Intent $intent */
        $intent = null;

        $cm = ConversationManager::createManagerForExistingConversation($ongoingConversation);

        return $intent;
    }

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     */
    public function determineCurrentConversation(UserContext $userContext, UtteranceInterface $utterance): Conversation
    {
        if ($userContext->isUserHavingConversation()) {
            $ongoingConversation = $userContext->getCurrentConversation();
            Log::debug(
                sprintf(
                    'User %s is having a conversation with id %s',
                    $userContext->getUserId(),
                    $ongoingConversation->getId()
                )
            );
            return $ongoingConversation;
        }

        $ongoingConversation = $this->getMatchingConversation($userContext, $utterance);
        Log::debug(
            sprintf(
                'Got a matching conversation for user %s with id %s',
                $userContext->getUserId(),
                $ongoingConversation->getId()
            )
        );

        return $ongoingConversation;
    }

    /**
     * There is no ongoing conversation for the current user so we will attempt to find
     * a matching new conversation or return a core-level NoMatch conversation if nothing else
     * works.
     *
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     */
    private function getMatchingConversation(UserContext $userContext, UtteranceInterface $utterance): Conversation
    {
        $matchingIntents = new Map();

        $defaultIntent = $this->app->make(InterpreterService::class)->

        $openingIntents = $this->conversationStore->getAllOpeningIntents();

        // Use the default interpreter unless a custom interpreter is defined.

        dd($openingIntents);
    }

    public function getConversationStore(): ConversationStoreInterface
    {
        return $this->conversationStore;
    }
}
