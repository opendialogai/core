<?php

namespace OpenDialogAi\ConversationEngine;

use Ds\Map;
use Ds\Set;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelCondition;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
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
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Intent
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws EIModelCreatorException
     * @throws CurrentIntentNotSetException
     */
    public function getNextIntent(UserContext $userContext, UtteranceInterface $utterance): Intent
    {
        /* @var Conversation $ongoingConversation */
        $ongoingConversation = $this->determineCurrentConversation($userContext, $utterance);
        Log::debug(sprintf('Ongoing conversation determined as %s', $ongoingConversation->getId()));

        /* @var Scene $currentScene */
        $currentScene = $userContext->getCurrentScene();

        /** @var EIModelIntent $currentIntent */
        $currentIntent = $this->conversationStore->getEIModelIntentByUid($userContext->getUser()->getCurrentIntentUid());

        // If the current intent is said across scenes, we use 0 - otherwise we use its order in its scene.
        $currentOrder = $currentIntent->getNextScene() ? 0 : $currentIntent->getOrder();

        $possibleNextIntents = $currentScene->getNextPossibleBotIntents($currentOrder);
        $filteredIntents = $this->filterByConditions($possibleNextIntents);

        /* @var Intent $nextIntent */
        $nextIntent = $filteredIntents->first()->value;
        ContextService::saveAttribute('conversation.next_intent', $nextIntent->getId());

        if ($nextIntent->causesAction()) {
            $inputActionAttributes = $nextIntent->getInputActionAttributeContexts();
            $outputActionAttributes = $nextIntent->getOutputActionAttributeContexts();

            $this->performIntentAction($userContext, $nextIntent, $inputActionAttributes, $outputActionAttributes);
        }

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
     * @throws EIModelCreatorException
     * @throws CurrentIntentNotSetException
     */
    public function determineCurrentConversation(UserContext $userContext, UtteranceInterface $utterance): Conversation
    {
        if ($userContext->isUserHavingConversation()) {
            $ongoingConversation = $userContext->getCurrentConversation();

            ContextService::saveAttribute('conversation.current_conversation', $ongoingConversation->getId());

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
     * @throws ConversationStore\EIModelCreatorException
     * @throws CurrentIntentNotSetException
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws EIModelCreatorException
     */
    public function updateConversationFollowingUserInput(
        UserContext $userContext,
        UtteranceInterface $utterance
    ): ?Conversation {
        /* @var Scene $currentScene */
        $currentScene = $userContext->getCurrentScene();

        /* @var EIModelIntent $currentIntent */
        $currentIntent = $userContext->getCurrentIntent();

        if (!ContextService::hasContext('conversation')) {
            ContextService::createContext('conversation');
        }

        ContextService::saveAttribute('conversation.current_scene', $currentScene->getId());
        ContextService::saveAttribute('conversation.current_intent', $currentIntent->getIntentId());

        // If the current intent is said across scenes, we use 0 - otherwise we use its order in its scene.
        $currentOrder = $currentIntent->getNextScene() ? 0 : $currentIntent->getOrder();
        $possibleNextIntents = $currentScene->getNextPossibleUserIntents($currentOrder);
        Log::debug(sprintf('There are %s possible next intents.', count($possibleNextIntents)));

        $defaultIntent = $this->interpreterService->getDefaultInterpreter()->interpret($utterance)[0];
        Log::debug(sprintf('Default intent is %s', $defaultIntent->getId()));

        $filteredIntents = $this->filterByConditions($possibleNextIntents);
        $matchingIntents = $this->getMatchingIntents($utterance, $filteredIntents, $defaultIntent);

        if (count($matchingIntents) >= 1) {
            Log::debug(sprintf('There are %s matching intents', count($matchingIntents)));

            $nextIntent = $matchingIntents->getBestMatch();
            Log::debug(sprintf('We found a matching intent %s', $nextIntent->getId()));
            $userContext->setCurrentIntent($nextIntent);

            ContextService::saveAttribute('conversation.interpreted_intent', $nextIntent->getId());

            $this->storeIntentAttributes($nextIntent);

            if ($nextIntent->causesAction()) {
                $inputActionAttributes = $nextIntent->getInputActionAttributeContexts();
                $outputActionAttributes = $nextIntent->getOutputActionAttributeContexts();

                $this->performIntentAction($userContext, $nextIntent, $inputActionAttributes, $outputActionAttributes);
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
     * @throws EIModelCreatorException
     */
    private function setCurrentConversation(UserContext $userContext, UtteranceInterface $utterance): ?Conversation
    {
        $defaultIntent = $this->interpreterService->getDefaultInterpreter()->interpret($utterance)[0];
        Log::debug(sprintf('Default intent is %s', $defaultIntent->getId()));

        $openingIntents = $this->conversationStore->getAllEIModelOpeningIntents();
        Log::debug(sprintf('Found %s opening intents.', count($openingIntents)));

        $matchingIntents = $this->matchOpeningIntents($defaultIntent, $utterance, $openingIntents->getIntents());
        Log::debug(sprintf('Found %s matching intents.', count($matchingIntents)));

        if (count($matchingIntents) === 0) {
            return null;
        }

        /* @var EIModelIntent $intent */
        $intent = $matchingIntents->last();
        Log::debug(sprintf('Select %s as matching intent.', $intent->getIntentId()));

        $this->storeIntentAttributesFromOpeningIntent($intent);

        // We specify the conversation to be cloned as we will be re-persisting it as a user conversation next

        /** @var Conversation $conversationForCloning */
        $conversationForCloning = $this->conversationStore->getConversation($intent->getConversationUid(), true);

        /** @var Conversation $conversationForConnecting */
        $conversationForConnecting = $this->conversationStore->getConversation($intent->getConversationUid(), false);

        // TODO can we avoid building, cloning and re-persisting the conversation here. EG clone directly in DGRAPH
        // TODO and store the resulting ID against the user

        $userContext->setCurrentConversation($conversationForCloning, $conversationForConnecting);

        /** @var Intent $currentIntent */
        $currentIntent = $this->conversationStore->getOpeningIntentByConversationIdAndOrder(
            $userContext->getUser()->getCurrentConversationUid(),
            $intent->getOrder()
        );

        $userContext->setCurrentIntent($currentIntent);

        /* @var Intent $currentIntent */
        Log::debug(sprintf('Set current intent as %s', $currentIntent->getId()));
        ContextService::saveAttribute('conversation.interpreted_intent', $currentIntent->getId());
        ContextService::saveAttribute('conversation.current_scene', 'opening_scene');

        if ($currentIntent->causesAction()) {
            $inputActionAttributes = $intent->getInputActionAttributeContexts();
            $outputActionAttributes = $intent->getOutputActionAttributeContexts();

            $this->performIntentAction($userContext, $currentIntent, $inputActionAttributes, $outputActionAttributes);
        }

        // For this intent get the matching conversation - we are pulling this back out from the user
        // so that we have the copy from the graph.
        // TODO do we need to get the entire conversation again?
        return $this->conversationStore->getConversation($intent->getConversationUid());
    }

    /**
     * @param Intent $defaultIntent
     * @param UtteranceInterface $utterance
     * @param Map $validOpeningIntents
     * @return Set
     */
    private function matchOpeningIntents(
        Intent $defaultIntent,
        UtteranceInterface $utterance,
        Map $validOpeningIntents
    ): Set {
        $matchingIntents = new Set();

        // Check conditions for each conversation
        $filteredIntents = $this->filterOpeningIntentsForConditions($validOpeningIntents);

        /* @var EIModelIntent $validIntent */
        foreach ($filteredIntents as $validIntent) {
            if ($validIntent->hasInterpreter()) {
                $intentsFromInterpreter = $this->interpreterService
                    ->getInterpreter($validIntent->getInterpreterId())
                    ->interpret($utterance);

                // For each intent from the interpreter check to see if it matches the opening intent candidate.
                foreach ($intentsFromInterpreter as $interpretedIntent) {
                    $validIntent->setInterpretedIntent($interpretedIntent);

                    if ($this->intentHasEnoughConfidence($interpretedIntent, $validIntent)) {
                        $matchingIntents->add($validIntent);
                    }
                }
            } elseif ($this->intentHasEnoughConfidence($defaultIntent, $validIntent)) {
                $validIntent->setInterpretedIntent($defaultIntent);
                $matchingIntents->add($validIntent);
            }
        }

        $matchingIntents = $this->filterNoMatchIntents($matchingIntents);

        return $matchingIntents;
    }

    /**
     * @param Map $intentsToCheck
     * @return Set
     */
    private function filterOpeningIntentsForConditions(Map $intentsToCheck): Set
    {
        $matchingIntents = new Set();

        /* @var EIModelIntent $intent */
        foreach ($intentsToCheck as $intent) {
            if ($intent->hasConditions()) {
                $pass = true;
                $conditions = $intent->getConditions();

                /* @var EIModelCondition $condition */
                foreach ($conditions as $conditionModel) {
                    /* @var Condition $condition */
                    $condition = $this->conversationStore->getConversationConverter()->convertCondition($conditionModel);

                    if (!$this->operationService->checkCondition($condition)) {
                        $pass = false;
                    }
                }

                if ($pass) {
                    $matchingIntents->add($intent);
                }
            } else {
                $matchingIntents->add($intent);
            }
        }

        return $matchingIntents;
    }

    /**
     * Filters out no match intents if we have more than 1 intent.
     * Any non-no match intent should be considered more valid.
     *
     * @param Set $matchingIntents
     * @return mixed
     */
    private function filterNoMatchIntents($matchingIntents)
    {
        if ($matchingIntents->count() === 1) {
            return $matchingIntents;
        }

        return $matchingIntents->filter(function (EIModelIntent $intent) {
            return $intent->getIntentId() !== NoMatchIntent::NO_MATCH;
        });
    }

    /**
     * Stores the Intent entities from an opening intent by pulling out the interpreted intent which contains the
     * interpreted attributes and the expected attributes that are set against the Opening Intent
     *
     * @param EIModelIntent $intent
     */
    public function storeIntentAttributesFromOpeningIntent(EIModelIntent $intent): void
    {
        $this->storeIntentAttributes($intent->getInterpretedIntent(), $intent->getExpectedAttributeContexts());
    }

    /**
     * Stores the non-core attributes from an Intent to a context.
     * Expected attributes are passed into the function or retrieved from the Intent to determine which context each
     * attribute should be saved to. If one is not defined for the attribute, it is saved to the session context
     *
     * @param Intent $intent
     * @param Map|null $expectedAttributes
     */
    private function storeIntentAttributes(Intent $intent, Map $expectedAttributes = null): void
    {
        if ($expectedAttributes === null) {
            $expectedAttributes = $intent->getExpectedAttributeContexts();
        }

        $attributes = $intent->getNonCoreAttributes();
        $context = ContextService::getSessionContext();

        $this->storeAttributes($attributes, $context, $expectedAttributes);
    }

    /**
     * Persists the contexts given.
     *
     * @param array $contexts Array of context IDs to be persisted
     */
    private function persistContexts(array $contexts)
    {
        foreach ($contexts as $contextId) {
            try {
                $context = ContextService::getContext($contextId);
                $context->persist();
            } catch (ContextDoesNotExistException $e) {
            }
        }
    }

    /**
     * @param Intent $interpretedIntent
     * @param EIModelIntent $validIntent
     * @return bool
     */
    private function intentHasEnoughConfidence(Intent $interpretedIntent, EIModelIntent $validIntent): bool
    {
        return $interpretedIntent->getId() === $validIntent->getIntentId() &&
            $interpretedIntent->getConfidence() >= $validIntent->getConfidence();
    }

    /**
     * Returns a map of matching intents
     *
     * @param UtteranceInterface $utterance
     * @param Map $nextIntents
     * @param Intent $defaultIntent
     * @return MatchingIntents
     * @throws NodeDoesNotExistException
     */
    private function getMatchingIntents(
        UtteranceInterface $utterance,
        Map $nextIntents,
        Intent $defaultIntent
    ): MatchingIntents {
        $matchingIntents = new MatchingIntents();

        /* @var Intent $validIntent */
        foreach ($nextIntents as $validIntent) {
            if ($validIntent->hasInterpreter()) {
                $interpretedIntents = $this->interpreterService->getInterpreter($validIntent->getInterpreter()->getId())
                    ->interpret($utterance);
            } else {
                $interpretedIntents = [$defaultIntent];
            }

            foreach ($interpretedIntents as $interpretedIntent) {
                if ($interpretedIntent->matches($validIntent)) {
                    $validIntent->copyNonCoreAttributes($interpretedIntent);
                    $matchingIntents->addMatchingIntent($validIntent);
                }
            }
        }

        return $matchingIntents;
    }

    /**
     * Performs the action associated with the intent and stores the outcome against the user
     *
     * @param UserContext $userContext
     * @param Intent $nextIntent
     * @param Map $inputActionAttributes
     * @param Map $outputActionAttributes
     * @throws NodeDoesNotExistException
     */
    public function performIntentAction(
        UserContext $userContext,
        Intent $nextIntent,
        Map $inputActionAttributes,
        Map $outputActionAttributes
    ): void {
        Log::debug(
            sprintf(
                'Current intent %s causes action %s',
                $nextIntent->getId(),
                $nextIntent->getAction()->getId()
            )
        );

        $action = $nextIntent->getAction();

        try {
            /* @var ActionResult $actionResult */
            $actionResult = $this->actionEngine->performAction($action->getId(), $inputActionAttributes);

            if ($actionResult) {
                $this->storeActionResult($actionResult, $userContext, $outputActionAttributes);
                Log::debug(sprintf('Adding action result to the right context'));
            }
        } catch (ActionNotAvailableException $e) {
            Log::warning(sprintf('Action %s has not been bound.', $action->getId()));
        }
    }

    /**
     * @param Map $intents
     * @return Map
     */
    private function filterByConditions(Map $intents): Map
    {
        $filteredIntents = $intents->filter(function ($key, Intent $item) {
            /** @var Condition $condition */
            foreach ($item->getAllConditions() as $condition) {
                if (!$this->operationService->checkCondition($condition)) {
                    return false;
                }
            }

            return true;
        });

        return $filteredIntents;
    }

    /**
     * Stores the attributes from an Action to a context.
     * Expected action attributes are retrieved from the Intent to determine which context each
     * attribute should be saved to.
     * If one is not defined for the attribute, it is saved to the user context
     *
     * @param ActionResult $actionResult
     * @param UserContext $userContext
     * @param Map $outputActionAttributes
     */
    private function storeActionResult(
        ActionResult $actionResult,
        UserContext $userContext,
        Map $outputActionAttributes
    ) {
        $attributes = $actionResult->getResultAttributes()->getAttributes();

        $this->storeAttributes($attributes, $userContext, $outputActionAttributes);
    }

    /**
     * Store attributes values to the right context.
     *
     * @param Map $attributes
     * @param ContextInterface $defaultContext
     * @param Map $expectedAttributes
     */
    private function storeAttributes(
        Map $attributes,
        ContextInterface $defaultContext,
        Map $expectedAttributes
    ) {
        $contextsUpdated = [];

        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getId();

            $context = $defaultContext;

            if ($expectedAttributes->hasKey($attributeName)) {
                $contextId = $expectedAttributes->get($attributeName);
                if ($context->getId() != $contextId) {
                    try {
                        $context = ContextService::getContext($contextId);
                    } catch (ContextDoesNotExistException $e) {
                        Log::error(sprintf('Attribute context %s does not exist, using user context', $contextId));
                    }
                }
            }

            Log::debug(sprintf('Storing attribute %s in %s context', $attribute->getId(), $context->getId()));
            $context->addAttribute($attribute);

            $contextsUpdated[$context->getId()] = $context->getId();
        }

        $this->persistContexts($contextsUpdated);
    }
}
