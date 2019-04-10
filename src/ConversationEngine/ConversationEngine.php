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
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\OpeningIntent;

class ConversationEngine implements ConversationEngineInterface
{
    /* @var ConversationStoreInterface */
    private $conversationStore;

    /* @var InterpreterServiceInterface */
    private $interpreterService;

    /**
     * @param ConversationStoreInterface $conversationStore
     */
    public function setConversationStore(ConversationStoreInterface $conversationStore)
    {
        $this->conversationStore = $conversationStore;
    }

    /**
     * @param InterpreterServiceInterface $interpreterService
     */
    public function setInterpreterService(InterpreterServiceInterface $interpreterService)
    {
        $this->interpreterService = $interpreterService;
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

        /* @var Scene $currentScene */
        $currentScene = $userContext->getCurrentScene();
        $possibleNextIntents = $currentScene->getNextPossibleBotIntents($userContext->getCurrentIntent());

        // Pull the top one
        $possibleNextIntents->getFirst();

        /* @var Intent $intent */
        $intent = null;
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

        $ongoingConversation = $this->setCurrentConversation($userContext, $utterance);
        Log::debug(
            sprintf(
                'Got a matching conversation for user %s with id %s',
                $userContext->getUserId(),
                $ongoingConversation->getId()
            )
        );

        // Start from the top - we should eventually have a conversation.
        return self::determineCurrentConversation($userContext, $utterance);
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
    private function setCurrentConversation(UserContext $userContext, UtteranceInterface $utterance): Conversation
    {
        $matchingIntents = new Map();

        $defaultIntent = $this->interpreterService->getDefaultInterpreter()->interpret($utterance)[0];

        $openingIntents = $this->conversationStore->getAllOpeningIntents();

        /* @var OpeningIntent $openingIntent */
        foreach ($openingIntents as $key => $openingIntent) {
            // If we have an interpreter use that to interpret.
            if ($openingIntent->hasInterpreter()) {
                $intents = $this->interpreterService
                    ->getInterpreter($openingIntent->getInterpreter())
                    ->interpret($utterance);

                // For each intent from the interpreter check to see if it matches the opening intent candidate.
                foreach ($intents as $interpretedIntent) {
                    if ($interpretedIntent->getId() === $openingIntent->getIntentId()) {
                        // If it is a match add it to the matching intents.
                        $matchingIntents->put($key, $openingIntent);
                    }
                }
            } else {
                // If we don't have a custom interpreter just check if it is a match to the default intent.
                if ($defaultIntent->getId() === $openingIntent->getIntentId()) {
                    $matchingIntents->put($key, $openingIntent);
                }
            }
        }

        /* @var OpeningIntent $intent */
        $intent = $matchingIntents->last()->value;

        $conversation = $this->conversationStore->getConversation($intent->getConversationUid());
        $userContext->setCurrentConversation($conversation);
        $userContext->setCurrentIntent($conversation->getIntentByUid($intent->getIntentUid()));

        // For this intent get the matching conversation
        return $this->conversationStore->getConversation($intent->getConversationUid());
    }

    public function getConversationStore(): ConversationStoreInterface
    {
        return $this->conversationStore;
    }
}
