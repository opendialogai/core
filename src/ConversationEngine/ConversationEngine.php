<?php

namespace OpenDialogAi\ConversationEngine;

use Ds\Map;
use Ds\Set;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;
use OpenDialogAi\ContextEngine\Contexts\Intent\IntentContext;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\Exceptions\NoConversationsException;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\VirtualIntent;
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
     * @param bool $isVirtual
     * @return Intent[]
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     */
    public function getNextIntents(UserContext $userContext, UtteranceInterface $utterance): array
    {
        /* @var Conversation $ongoingConversation */
        $ongoingConversation = $this->determineCurrentConversation($userContext, $utterance);
        Log::debug(sprintf('Ongoing conversation determined as %s', $ongoingConversation->getId()));

        ContextService::saveAttribute('conversation.next_intents', []);
        $followingIntents = $this->getAndHandleFollowingIntents($userContext, $utterance);

        return $followingIntents;
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
     * @throws NoConversationsException
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
                try {
                    $ongoingConversation = $this->updateConversationFollowingUserInput($userContext, $utterance);
                } catch (NoMatchingIntentsException $e) {
                    Log::debug(sprintf('No intent for ongoing conversation matched, simulating a NoMatch.'));
                    $utterance->setCallbackId(self::NO_MATCH);
                    return $this->determineCurrentConversation($userContext, $utterance);
                }
            }

            return $ongoingConversation;
        }

        $ongoingConversation = $this->setCurrentConversation($userContext, $utterance);
        if (!isset($ongoingConversation)) {
            if ($utterance->getCallbackId() == self::NO_MATCH) {
                Log::debug(sprintf('No conversation found for callback NoMatch.'));
                throw new NoConversationsException();
            } else {
                Log::debug(sprintf('No opening conversation found for utterance, simulating a NoMatch.'));
                $utterance->setCallbackId(self::NO_MATCH);
                return $this->determineCurrentConversation($userContext, $utterance);
            }
        }

        Log::debug(
            sprintf(
                'Got a matching conversation for user %s with id %s',
                $userContext->getUserId(),
                $ongoingConversation->getId()
            )
        );

        // Start from the top - we should eventually have a conversation.
        return $this->determineCurrentConversation($userContext, $utterance);
    }

    /**
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Conversation
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     */
    public function updateConversationFollowingUserInput(
        UserContext $userContext,
        UtteranceInterface $utterance
    ): Conversation {
        $possibleNextIntents = $this->getNextPossibleUserIntentsFromCurrentIntent($userContext);

        Log::debug(sprintf('There are %s possible next intents.', count($possibleNextIntents)));

        $defaultIntent = $this->interpreterService->interpretDefaultInterpreter($utterance)[0];

        Log::debug(sprintf('Default intent is %s', $defaultIntent->getId()));

        $matchingIntents = $this->getMatchingIntents($utterance, $possibleNextIntents, $defaultIntent);

        if (count($matchingIntents) >= 1) {
            Log::debug(sprintf('There are %s matching intents', count($matchingIntents)));

            $nextIntent = $matchingIntents->getBestMatch();
            Log::debug(sprintf('We found a matching intent %s', $nextIntent->getId()));
            $userContext->setCurrentIntent($nextIntent);

            ContextService::saveAttribute('conversation.interpreted_intent', $nextIntent->getId());

            $this->storeIntentAttributes($nextIntent);

            if ($nextIntent->causesAction()) {
                $this->performIntentAction($userContext, $nextIntent);
            }

            return $userContext->getCurrentConversation();
        }

        // What the user says does not match anything expected in the current conversation so complete it and
        // pretend we received a no match intent.
        Log::debug('No matching intent, moving conversation to past state');
        $userContext->moveCurrentConversationToPast();
        Log::debug('No match found, dropping out of current conversation.');

        throw new NoMatchingIntentsException();
    }

    /**
     * @param UserContext $userContext
     * @param VirtualIntent $virtualIntent
     * @return Conversation
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws NoMatchingIntentsException
     */
    public function updateConversationFollowingVirtualUserInput(
        UserContext $userContext,
        VirtualIntent $virtualIntent
    ): Conversation {
        $possibleNextIntents = $this->getNextPossibleUserIntentsFromCurrentIntent($userContext);

        $possibleNextIntents = $possibleNextIntents->filter(
            function ($key, Intent $possibleIntent) use ($virtualIntent) {
                return $possibleIntent->getId() === $virtualIntent->getId();
            }
        );

        Log::debug(sprintf('There are %s possible next intents.', count($possibleNextIntents)));

        $matchingIntents = $this->filterByConditions($possibleNextIntents);

        if (count($matchingIntents) >= 1) {
            Log::debug(sprintf('There are %s matching intents', count($matchingIntents)));

            /** @var Intent $nextIntent */
            $nextIntent = $matchingIntents->first()->value;
            Log::debug(sprintf('Intent chosen as %s', $nextIntent->getId()));
            $userContext->setCurrentIntent($nextIntent);

            if ($nextIntent->causesAction()) {
                $this->performIntentAction($userContext, $nextIntent);
            }

            return $userContext->getCurrentConversation();
        }

        // What the user says does not match anything expected in the current conversation so complete it and
        // pretend we received a no match intent.
        Log::debug('No matching intent, moving conversation to past state');
        $userContext->moveCurrentConversationToPast();
        Log::debug('No match found, dropping out of current conversation.');

        throw new NoMatchingIntentsException();
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
        $defaultIntent = $this->interpreterService->interpretDefaultInterpreter($utterance)[0];

        Log::debug(sprintf('Default intent is %s', $defaultIntent->getId()));

        $openingIntents = $this->conversationStore->getAllEIModelOpeningIntents();
        Log::debug(sprintf('Found %s opening intents.', count($openingIntents)));

        $matchingIntents = $this->matchOpeningIntents($defaultIntent, $utterance, $openingIntents->getIntents());
        Log::debug(sprintf('Found %s matching intents.', count($matchingIntents)));

        if (count($matchingIntents) === 0) {
            return null;
        }

        /* @var EIModelIntent $intent */
        $intent = $matchingIntents->first();
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
            $this->performIntentAction($userContext, $currentIntent);
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

        /* @var EIModelIntent $validIntent */
        foreach ($validOpeningIntents as $validIntent) {
            if ($validIntent->hasInterpreter()) {
                $interpreter = $validIntent->getInterpreterId();

                $intentsFromInterpreter = $this->interpreterService->interpret($interpreter, $utterance);

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

        // Check conditions for each conversation
        $matchingIntents = $this->filterOpeningIntentsForConditions($matchingIntents);

        $matchingIntents = $this->filterNoMatchIntents($matchingIntents);

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
        $matching = new Map();

        /* @var Intent $validIntent */
        foreach ($nextIntents as $validIntent) {
            if ($validIntent->hasInterpreter()) {
                $interpreter = $validIntent->getInterpreter()->getId();

                $interpretedIntents = $this->interpreterService->interpret($interpreter, $utterance);
            } else {
                $interpretedIntents = [$defaultIntent];
            }

            foreach ($interpretedIntents as $interpretedIntent) {
                if ($interpretedIntent->matches($validIntent)) {
                    $validIntent->copyNonCoreAttributes($interpretedIntent);
                    $matching->put($validIntent->hash(), $validIntent);
                }
            }
        }

        $filteredIntents = $this->filterByConditions($matching, true);

        $matchingIntents = new MatchingIntents();
        foreach ($filteredIntents as $matchingIntent) {
            $matchingIntents->addMatchingIntent($matchingIntent);
        }

        return $matchingIntents;
    }

    /**
     * Performs the action associated with the intent and stores the outcome against the user
     *
     * @param UserContext $userContext
     * @param Intent $nextIntent
     * @throws NodeDoesNotExistException
     */
    public function performIntentAction(UserContext $userContext, Intent $nextIntent): void
    {
        Log::debug(
            sprintf(
                'Current intent %s causes action %s',
                $nextIntent->getId(),
                $nextIntent->getAction()->getId()
            )
        );

        $action = $nextIntent->getAction();
        $inputActionAttributes = $nextIntent->getInputActionAttributeContexts();
        $outputActionAttributes = $nextIntent->getOutputActionAttributeContexts();

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
     * @param bool $useIntentContext
     * @return Map
     */
    private function filterByConditions(Map $intents, bool $useIntentContext = false): Map
    {
        if ($useIntentContext) {
            /** @var IntentContext $intentContext */
            $intentContext = ContextService::getContext(IntentContext::INTENT_CONTEXT);
        } else {
            $intentContext = null;
        }

        $filteredIntents = $intents->filter(function ($key, Intent $item) use ($intentContext) {
            if ($intentContext) {
                /** @var AttributeInterface $attribute */
                foreach ($item->getNonCoreAttributes() as $attribute) {
                    $intentContext->addAttribute($attribute->copy());
                }
            }

            $result = true;

            /** @var Condition $condition */
            foreach ($item->getAllConditions() as $condition) {
                if (!$this->operationService->checkCondition($condition)) {
                    $result = false;
                    break;
                }
            }

            if ($intentContext) {
                $intentContext->refresh();
            }

            return $result;
        });

        return $filteredIntents;
    }

    /**
     * @param Set $intents
     * @return Set
     */
    private function filterOpeningIntentsForConditions(Set $intents): Set
    {
        $intentContext = ContextService::getContext(IntentContext::INTENT_CONTEXT);

        $filteredIntents = $intents->filter(function (EIModelIntent $item) use ($intentContext) {
            foreach ($item->getInterpretedIntent()->getNonCoreAttributes() as $attribute) {
                $intentContext->addAttribute($attribute->copy());
            }

            $result = true;

            $intent = $this->getConversationStore()->getConversationConverter()->convertIntent($item);

            /** @var Condition $condition */
            foreach ($intent->getAllConditions() as $condition) {
                if (!$this->operationService->checkCondition($condition)) {
                    $result = false;
                    break;
                }
            }

            $intentContext->refresh();

            return $result;
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

    /**
     * @param UserContext $userContext
     * @return Intent
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     */
    private function determineNextIntent(UserContext $userContext): Intent
    {
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

        return $nextIntent;
    }

    /**
     * @param UserContext $userContext
     * @return Map
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     */
    private function getNextPossibleUserIntentsFromCurrentIntent(UserContext $userContext): Map
    {
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
        return $possibleNextIntents;
    }

    /**
     * Based on what user's current intent, determine the following bot intent, handle that intent and iterate for
     * following virtual intents.
     *
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Intent[]
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     */
    private function getAndHandleFollowingIntents(UserContext $userContext, UtteranceInterface $utterance): array
    {
        $nextIntent = $this->determineNextIntent($userContext);

        $this->handleIntent($userContext, $nextIntent);

        $this->updateUserCurrentIntent($userContext, $nextIntent);

        return $this->getVirtualIntents($nextIntent, $userContext, $utterance);
    }

    /**
     * Gets any following virtual intents of an intent and updates the conversation iteratively for each virtual intent
     *
     * @param Intent $nextIntent
     * @param UserContext $userContext
     * @param UtteranceInterface $utterance
     * @return Intent[]
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     */
    private function getVirtualIntents(Intent $nextIntent, UserContext $userContext, UtteranceInterface $utterance): array
    {
        $nextIntents = [$nextIntent];

        if ($nextIntent->getVirtualIntent()) {
            try {
                // If we are in a completing intent, we need to look back at all possible opening intents
                if ($nextIntent->completes()) {
                    $utterance->setCallbackId($nextIntent->getVirtualIntent()->getId());
                    $this->determineCurrentConversation($userContext, $utterance);
                } else {
                    $this->updateConversationFollowingVirtualUserInput($userContext, $nextIntent->getVirtualIntent());
                }

                $nextIntents = array_merge(
                    $nextIntents,
                    $this->getAndHandleFollowingIntents($userContext, $utterance)
                );
            } catch (NoMatchingIntentsException $e) {
                $utterance->setCallbackId(self::NO_MATCH);
                return $this->getAndHandleFollowingIntents($userContext, $utterance);
            }
        }

        return $nextIntents;
    }

    /**
     * Handle any operations that must be completed for each returned outgoing intent
     *
     * @param UserContext $userContext
     * @param Intent $nextIntent
     * @throws NodeDoesNotExistException
     */
    private function handleIntent(UserContext $userContext, Intent $nextIntent): void
    {
        /** @var array $nextIntents */
        $nextIntentsAttributeArray = ContextService::getConversationContext()->getAttributeValue('next_intents');
        $nextIntentsAttributeArray[] = $nextIntent->getId();
        ContextService::saveAttribute('conversation.next_intents', $nextIntentsAttributeArray);

        if ($nextIntent->causesAction()) {
            $this->performIntentAction($userContext, $nextIntent);
        }
    }

    /**
     * Checks whether the sent intent is either repeating meaning current intent of the user should point to the previous intent
     * or completing in which case the user's curent conversation is moved to the past and all conversations will be considered on
     * the next incoming utterance
     *
     * @param UserContext $userContext The user context applicable to the current user
     * @param Intent $intent The intent that has just been sent to the User
     * @throws EIModelCreatorException
     * @throws GuzzleException
     */
    protected function updateUserCurrentIntent(UserContext $userContext, Intent $intent): void
    {
        $isRepeating = $userContext->getCurrentIntent()->getRepeating();
        if ($isRepeating) {
            $precedingIntent = $this->getConversationStore()->getPrecedingIntent(
                $userContext->getCurrentIntent()->getIntentUid()
            );
            $userContext->setCurrentIntent($precedingIntent);
        } else {
            if ($intent->completes()) {
                $userContext->moveCurrentConversationToPast();
            } else {
                $userContext->setCurrentIntent($intent);
            }
        }
    }
}
