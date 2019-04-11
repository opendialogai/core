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
    const NO_MATCH = 'intent.core.NoMatch';

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function getNextIntent(UserContext $userContext, UtteranceInterface $utterance): Intent
    {
        /* @var Conversation $ongoingConversation */
        $ongoingConversation = $this->determineCurrentConversation($userContext, $utterance);

        /* @var Scene $currentScene */
        $currentScene = $userContext->getCurrentScene();
        $possibleNextIntents = $currentScene->getNextPossibleBotIntents($userContext->getCurrentIntent());

        /* @var Intent $nextIntent */
        $nextIntent = $possibleNextIntents->first()->value;

        if ($nextIntent->completes()) {
            $userContext->moveCurrentConversationToPast();
        } else {
            $userContext->setCurrentIntent($nextIntent);
        }

        return $nextIntent;
    }

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
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

            if ($userContext->currentSpeakerIsBot()) {
                $ongoingConversation = $this->updateConversationFollowingUserInput($userContext, $utterance);

                if (!isset($ongoingConversation)) {
                    // We couldn't find a conversation let's set the utterance to a NoMatch
                    $utterance->setCallbackId(self::NO_MATCH);
                    return self::determineCurrentConversation($userContext, $utterance);
                }
            }

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
     * @param Conversation $ongoingConversation
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function updateConversationFollowingUserInput(UserContext $userContext, UtteranceInterface $utterance)
    {
        $matchingIntents = new Map();

        /* @var Scene $currentScene */
        $currentScene = $userContext->getCurrentScene();

        $possibleNextIntents = $currentScene->getNextPossibleUserIntents($userContext->getCurrentIntent());
        $defaultIntent = $this->interpreterService->getDefaultInterpreter()->interpret($utterance)[0];

        //Determine if there is an intent that matches the incoming utterance
        /* @var Intent $intent */
        foreach ($possibleNextIntents as $intent) {
            if ($intent->hasInterpreter()) {
                $intents = $this->interpreterService
                    ->getInterpreter($intent->getInterpreter()->getId())
                    ->interpret($utterance);
                // Check to see if one of the interpreted intents matches the possible next intents
                /* @vat Intent $interpretedIntent */
                foreach ($intents as $interpretedIntent) {
                    $matchedIntents = array_filter($intents, function ($i) use ($interpretedIntent) {
                        if ($i->getId() === $interpretedIntent->getId()) {
                            return true;
                        }
                    });
                    $matchingIntents = $matchingIntents->merge($matchedIntents);
                }
            } else {
                if ($intent->getId() === $defaultIntent->getId()) {
                    $matchingIntents->put($intent->getId(), $intent);
                }
            }
        }
        // We can get a "matching" intent that is not part of the possible intents if we hit a NoMatch
        // So let's make another run through intents to ensure that it is a real match.
        $finalIntents = new Map();
        foreach ($matchingIntents as $matchedIntent) {
            foreach ($possibleNextIntents as $possibleIntent) {
                if ($matchedIntent->getId() === $possibleIntent->getId()) {
                    $finalIntents->put($possibleIntent->getId(), $possibleIntent);
                }
            }
        }

        if (count($finalIntents) >= 1) {
            /* @var Intent $nextIntent */
            $nextIntent = $possibleNextIntents->first()->value;
            $userContext->setCurrentIntent($nextIntent);
            return $userContext->getCurrentConversation();
        } else {
            // What the user says does not match anything expected in the current conversation so complete it and
            // pretend we received a no match intent.
            $userContext->moveCurrentConversationToPast();
            //@todo This should be an exception
            return null;
        }
    }

    /**
     * There is no ongoing conversation for the current user so we will attempt to find
     * a matching new conversation or return a core-level NoMatch conversation if nothing else
     * works.
     *
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     * @throws \GuzzleHttp\Exception\GuzzleException
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
                        $matchingIntents->put($openingIntent->getIntentId(), $openingIntent);
                    }
                }
            } else {
                // If we don't have a custom interpreter just check if it is a match to the default intent.
                if ($defaultIntent->getId() === $openingIntent->getIntentId()) {
                    $matchingIntents->put($openingIntent->getIntentId(), $openingIntent);
                }
            }
        }

        /* @var OpeningIntent $intent */
        $intent = $matchingIntents->last()->value;

        $conversation = $this->conversationStore->getConversation($intent->getConversationUid());
        $userContext->setCurrentConversation($conversation);
        $userContext->setCurrentIntent($userContext->getUser()->getCurrentConversation()->getIntentByOrder($intent->getOrder()));

        // For this intent get the matching conversation - we are pulling this back out from the user
        // so that we have the copy from the graph.
        return $this->conversationStore->getConversation($intent->getConversationUid());
    }

    public function getConversationStore(): ConversationStoreInterface
    {
        return $this->conversationStore;
    }
}
