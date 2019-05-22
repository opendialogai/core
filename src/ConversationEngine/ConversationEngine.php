<?php

namespace OpenDialogAi\ConversationEngine;

use Ds\Map;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Contexts\UserContext;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\OpeningIntent;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class ConversationEngine implements ConversationEngineInterface
{
    const NO_MATCH = 'intent.core.NoMatch';

    /* @var ConversationStoreInterface */
    private $conversationStore;

    /* @var InterpreterServiceInterface */
    private $interpreterService;

    /* @var OperationServiceInterface */
    private $operationService;

    /* @var ActionEngineInterface */
    private $actionEngine;

    /* @var AttributeResolver */
    private $attributeResolver;

    /* @var ContextService */
    private $contextService;

    /**
     * @param ConversationStoreInterface $conversationStore
     */
    public function setConversationStore(ConversationStoreInterface $conversationStore): void
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
    public function setInterpreterService(InterpreterServiceInterface $interpreterService): void
    {
        $this->interpreterService = $interpreterService;
    }

    /**
     * @param OperationServiceInterface $operationService
     */
    public function setOperationService(OperationServiceInterface $operationService): void
    {
        $this->operationService = $operationService;
    }

    /**
     * @param ActionEngineInterface $actionEngine
     */
    public function setActionEngine(ActionEngineInterface $actionEngine): void
    {
        $this->actionEngine = $actionEngine;
    }

    /**
     * @param ContextService $contextService
     */
    public function setContextService(ContextService $contextService): void
    {
        $this->contextService = $contextService;
    }

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Intent
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws FieldNotSupported
     * @throws ActionNotAvailableException
     */
    public function getNextIntent(UserContext $userContext, UtteranceInterface $utterance): Intent
    {
        /* @var Conversation $ongoingConversation */
        $ongoingConversation = $this->determineCurrentConversation($userContext, $utterance);
        Log::debug(sprintf('Ongoing conversation determined as %s', $ongoingConversation->getId()));

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
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws FieldNotSupported
     * @throws ActionNotAvailableException
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
                Log::debug(sprintf('Speaker was bot.'));
                $ongoingConversation = $this->updateConversationFollowingUserInput($userContext, $utterance);

                if (!isset($ongoingConversation)) {
                    Log::debug(sprintf('No intent for ongoing conversation matched, simulating a NoMatch.'));
                    $utterance->setCallbackId(self::NO_MATCH);
                    return self::determineCurrentConversation($userContext, $utterance);
                }
            }

            return $ongoingConversation;
        }

        $ongoingConversation = $this->setCurrentConversation($userContext, $utterance);
        if (!isset($ongoingConversation)) {
            Log::debug(sprintf('No opening conversation found for utterance, simulating a NoMatch.'));
            $utterance->setCallbackId(self::NO_MATCH);
            return self::determineCurrentConversation($userContext, $utterance);
        }

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
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     * @throws GuzzleException
     * @throws ActionNotAvailableException
     * @throws NodeDoesNotExistException
     */
    public function updateConversationFollowingUserInput(UserContext $userContext, UtteranceInterface $utterance): ?Conversation
    {
        $matchingIntents = new Map();

        /* @var Scene $currentScene */
        $currentScene = $userContext->getCurrentScene();

        $possibleNextIntents = $currentScene->getNextPossibleUserIntents($userContext->getCurrentIntent());
        Log::debug(sprintf('There are %s possible next intents.', count($possibleNextIntents)));

        $defaultIntent = $this->interpreterService->getDefaultInterpreter()->interpret($utterance)[0];
        Log::debug(sprintf('Default intent is %s', $defaultIntent->getId()));

        // Determine if there is an intent that matches the incoming utterance
        /* @var Intent $validIntent */
        foreach ($possibleNextIntents as $validIntent) {
            if ($validIntent->hasInterpreter()) {
                $interpretedIntents = $this->interpreterService
                    ->getInterpreter($validIntent->getInterpreter()->getId())
                    ->interpret($utterance);

                // Check to see if one of the interpreted intents matches the valid Intent.
                /* @var Intent $interpretedIntent */
                foreach ($interpretedIntents as $interpretedIntent) {
                    Log::debug(
                        sprintf('Comparing interpreted intent %s with confidence %s against valid intent %s with confidence %s',
                            $interpretedIntent->getId(),
                            $interpretedIntent->getConfidence(),
                            $validIntent->getId(),
                            $validIntent->getConfidence()
                        )
                    );
                    if ($interpretedIntent->getId() === $validIntent->getId() &&
                        $interpretedIntent->getConfidence() >= $validIntent->getConfidence()) {
                        // Pass attributes from the interpreted intent to the valid intent
                        foreach ($interpretedIntent->getNonCoreAttributes() as $attribute) {
                            $validIntent->addAttribute($attribute);
                        }
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
            Log::debug(sprintf('There are %s matching intents', count($matchingIntents)));

            /* @var Intent $nextIntent */
            $nextIntent = $possibleNextIntents->first()->value;
            Log::debug(sprintf('We found a matching intent %s', $nextIntent->getId()));
            $userContext->setCurrentIntent($nextIntent);
            // Check if the intent has non-core attributes and set those at the user context level
            $this->storeIntentEntities($nextIntent, $userContext);

            if ($nextIntent->causesAction()) {
                Log::debug(
                    sprintf(
                        'Current intent %s causes action %s',
                        $nextIntent->getId(),
                        $nextIntent->getAction()->getId()
                    )
                );

                /* @var ActionResult $actionResult */
                $actionResult = $this->actionEngine->performAction($nextIntent->getAction()->getId());
                $userContext->addActionResult($actionResult);
                Log::debug(sprintf('Adding action result to user context'));
            }

            return $userContext->getCurrentConversation();
        }

        // What the user says does not match anything expected in the current conversation so complete it and
        // pretend we received a no match intent.
        Log::debug('No matching intent, moving conversation to past state');
        $userContext->moveCurrentConversationToPast();
        Log::debug('No match found, dropping out of current conversation.');
        //@todo This should be an exception
        return null;
    }

    /**
     * There is no ongoing conversation for the current user so we will attempt to find
     * a matching new conversation or return a core-level NoMatch conversation if nothing else
     * works.
     *
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation | null
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws ActionNotAvailableException
     */
    private function setCurrentConversation(UserContext $userContext, UtteranceInterface $utterance): ?Conversation
    {
        $defaultIntent = $this->interpreterService->getDefaultInterpreter()->interpret($utterance)[0];
        Log::debug(sprintf('Default intent is %s', $defaultIntent->getId()));

        $openingIntents = $this->conversationStore->getAllOpeningIntents();
        Log::debug(sprintf('Found %s opening intents.', count($openingIntents)));

        $matchingIntents = $this->matchOpeningIntents($defaultIntent, $utterance, $openingIntents);
        Log::debug(sprintf('Found %s matching intents.', count($matchingIntents)));

        if (count($matchingIntents) === 0) {
            return null;
        }

        /* @var OpeningIntent $intent */
        $intent = $matchingIntents->last()->value;
        Log::debug(sprintf('Select %s as matching intent.', $intent->getIntentId()));

        if ($interpretedIntent = $intent->getInterpretedIntent()) {
            $this->storeIntentEntities($interpretedIntent, $userContext);
        }

        $conversation = $this->conversationStore->getConversation($intent->getConversationUid());
        $userContext->setCurrentConversation($conversation);
        $userContext->setCurrentIntent(
            $userContext->getUser()->getCurrentConversation()->getIntentByOrder($intent->getOrder())
        );

        /* @var Intent $currentIntent */
        $currentIntent = $userContext->getCurrentIntent();
        Log::debug(sprintf('Set current intent as %s', $currentIntent->getId()));

        if ($currentIntent->causesAction()) {
            Log::debug(
                sprintf(
                    'Current intent %s causes action %s',
                    $currentIntent->getId(),
                    $currentIntent->getAction()->getId()
                )
            );

            /* @var ActionResult $actionResult */
            $actionResult = $this->actionEngine->performAction($currentIntent->getAction()->getId());
            $userContext->addActionResult($actionResult);
            Log::debug(sprintf('Adding action result to user context'));
        }

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
            if ($validIntent->hasInterpreter()) {
                $intentsFromInterpreter = $this->interpreterService
                    ->getInterpreter($validIntent->getInterpreter())
                    ->interpret($utterance);

                // For each intent from the interpreter check to see if it matches the opening intent candidate.
                foreach ($intentsFromInterpreter as $interpretedIntent) {
                    $validIntent->setInterpretedIntent($interpretedIntent);

                    if ($this->intentHasEnoughConfidence($interpretedIntent, $validIntent)) {
                        $matchingIntents->put($validIntent->getConversationId(), $validIntent);
                    }
                }
            } else if ($this->intentHasEnoughConfidence($defaultIntent, $validIntent)) {
                $validIntent->setInterpretedIntent($defaultIntent);
                $matchingIntents->put($validIntent->getConversationId(), $validIntent);
            }
        }

        // Check conditions for each conversation
        $matchingIntents = $this->filterOpeningIntentsForConditions($matchingIntents);

        return $matchingIntents;
    }

    /**
     * @param Map $intentsToCheck
     * @return Map
     */
    private function filterOpeningIntentsForConditions(Map $intentsToCheck): Map
    {
        $matchingIntents = new Map();

        /* @var OpeningIntent $intent */
        foreach ($intentsToCheck as $intent) {
            if ($intent->hasConditions()) {
                $pass = true;
                $conditions = $intent->getConditions();

                /* @var Condition $condition */
                foreach ($conditions as $condition) {
                    if (!$this->operationService->checkCondition($condition)) {
                        $pass = false;
                    }
                }

                if ($pass) {
                    $matchingIntents->put($intent->getConversationId(), $intent);
                }
            } else {
                $matchingIntents->put($intent->getConversationId(), $intent);
            }
        }

        return $matchingIntents;
    }

    /**
     * @param Intent $intent
     * @param UserContext $context
     */
    private function storeIntentEntities(Intent $intent, UserContext $context): void
    {
        /** @var AttributeInterface $attribute */
        foreach ($intent->getNonCoreAttributes() as $attribute) {
            Log::debug(sprintf('Storing attribute %s for user', $attribute->getId()));
            $context->addAttribute($attribute);
        }

        $context->updateUser();
    }

    /**
     * @param Intent $interpretedIntent
     * @param OpeningIntent $validIntent
     * @return bool
     */
    private function intentHasEnoughConfidence(Intent $interpretedIntent, OpeningIntent $validIntent): bool
    {
        return $interpretedIntent->getId() === $validIntent->getIntentId() &&
            $interpretedIntent->getConfidence() >= $validIntent->getConfidence();
    }
}
