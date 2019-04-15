<?php


namespace OpenDialogAi\ConversationEngine;


use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\InterpreterEngine\Service\InterpreterService;
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

    /* @var ActionEngineInterface $actionEngine */
    private $actionEngine;

    /**
     * @param ConversationStoreInterface $conversationStore
     */
    public function setConversationStore(ConversationStoreInterface $conversationStore)
    {
        $this->conversationStore = $conversationStore;
    }

    /**
     * @return ConversationStoreInterface
     */
    public function getConversationStore(): ConversationStoreInterface
    {
        return $this->conversationStore;
    }

    /**
     * @param InterpreterServiceInterface $interpreterService
     */
    public function setInterpreterService(InterpreterServiceInterface $interpreterService)
    {
        $this->interpreterService = $interpreterService;
    }

    /**
     * @param ActionEngineInterface $actionEngine
     */
    public function setActionEngine(ActionEngineInterface $actionEngine)
    {
        $this->actionEngine = $actionEngine;
    }


    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Intent
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
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
        /* @var Intent $validIntent */
        foreach ($possibleNextIntents as $validIntent) {
            if ($validIntent->hasInterpreter()) {
                $interpretedIntents = $this->interpreterService
                    ->getInterpreter($validIntent->getInterpreter()->getId())
                    ->interpret($utterance);
                // Check to see if one of the interpreted intents matches the valid Intent.
                /* @var Intent $interpretedIntent */
                foreach ($interpretedIntents as $interpretedIntent) {
                    if ($interpretedIntent->getId() === $validIntent->getId() &&
                        $interpretedIntent->getConfidence() >= $validIntent->getConfidence()) {
                        $matchingIntents->put($validIntent->getId(), $validIntent);
                    }
                }
            } else {
                if ($validIntent->getId() === $defaultIntent->getId() &&
                    $validIntent->getConfidence() >= $defaultIntent->getConfidence()) {
                    $matchingIntents->put($validIntent->getId(), $validIntent);
                }
            }
        }

        if (count($matchingIntents) >= 1) {
            /* @var Intent $nextIntent */
            $nextIntent = $possibleNextIntents->first()->value;
            $userContext->setCurrentIntent($nextIntent);
            // @todo perform action associated with incoming intent.

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
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    private function setCurrentConversation(UserContext $userContext, UtteranceInterface $utterance): Conversation
    {
        $defaultIntent = $this->interpreterService->getDefaultInterpreter()->interpret($utterance)[0];

        $openingIntents = $this->conversationStore->getAllOpeningIntents();

        $matchingIntents = $this->matchOpeningIntents($defaultIntent, $utterance, $openingIntents);

        // @todo check conditions for each conversation/scene associated with each intent and remove
        // the ones that don't match

        /* @var OpeningIntent $intent */
        $intent = $matchingIntents->last()->value;

        $conversation = $this->conversationStore->getConversation($intent->getConversationUid());
        $userContext->setCurrentConversation($conversation);
        $userContext->setCurrentIntent($userContext->getUser()->getCurrentConversation()->getIntentByOrder($intent->getOrder()));

        /* @var Intent $currentIntent */
        $currentIntent = $userContext->getCurrentIntent();
        /* @var ActionResult $actionResult */
        $actionResult = $this->actionEngine->performAction($currentIntent->getAction()->getId());
        $userContext->addActionResult($actionResult);

        // For this intent get the matching conversation - we are pulling this back out from the user
        // so that we have the copy from the graph.
        return $this->conversationStore->getConversation($intent->getConversationUid());
    }

    /**
     * @param Intent $defaultIntent
     * @param UtteranceInterface $utterance
     * @param Map $validOpeningIntents
     * @return Map
     */
    private function matchOpeningIntents(Intent $defaultIntent, UtteranceInterface $utterance, Map $validOpeningIntents): Map
    {
        $matchingIntents = new Map();

        /* @var OpeningIntent $validIntent */
        foreach ($validOpeningIntents as $key => $validIntent) {
            // If we have an interpreter use that to interpret.
            if ($validIntent->hasInterpreter()) {
                $intentsFromInterpreter = $this->interpreterService
                    ->getInterpreter($validIntent->getInterpreter())
                    ->interpret($utterance);

                // For each intent from the interpreter check to see if it matches the opening intent candidate.
                foreach ($intentsFromInterpreter as $interpretedIntent) {
                    // For an intent to "pass" it has to match and have a higher or equal confidence to the interpreted one
                    if (
                        $interpretedIntent->getId() === $validIntent->getIntentId() &&
                        $interpretedIntent->getConfidence() >= $validIntent->getConfidence()) {
                        // If it is a match add it to the matching intents.
                        $matchingIntents->put($validIntent->getIntentId(), $validIntent);
                    }
                }
            } else {
                // If we don't have a custom interpreter just check if it is a match to the default intent.
                if ($defaultIntent->getId() === $validIntent->getIntentId() &&
                    $defaultIntent->getConfidence() >= $validIntent->getConfidence()) {
                    $matchingIntents->put($validIntent->getIntentId(), $validIntent);
                }
            }
        }

        return $matchingIntents;
    }
}
