<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\CouldNotCreateUserFromUtteranceException;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Reasoners\ActionPerformer;
use OpenDialogAi\ConversationEngine\Reasoners\ConversationalStateReasoner;
use OpenDialogAi\ConversationEngine\Reasoners\MatchRequestIntentStartingFromConversationStrategy;
use OpenDialogAi\ConversationEngine\Reasoners\OpeningIntentSelectorStrategy;
use OpenDialogAi\ConversationEngine\Reasoners\ResponseIntentSelector;
use OpenDialogAi\ConversationEngine\Reasoners\UtteranceReasoner;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class ConversationEngine implements ConversationEngineInterface
{
    public const CONVERSATION_CONTEXT = 'conversation';

    /* @var InterpreterServiceInterface */
    private $interpreterService;

    /* @var OperationServiceInterface */
    private $operationService;

    /* @var ActionEngineInterface */
    private $actionEngine;

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

    public function getNextIntents(UtteranceAttribute $utterance): IntentCollection
    {
        // We start by setting the request intent to a no match and the possible response intents
        // to an empty collection. If all else fails this will be the default behavior.
        $requestIntent = Intent::createNoMatchIntent();
        $responseIntents = new IntentCollection();

        try {
            /** @var UserAttribute $currentUser */
            $currentUser = $this->getCurrentUser($utterance);

            // The ConversationStateReasoner updates the Conversation Context to reflect the current state of the user.
            ConversationalStateReasoner::determineConversationalStateForUser($currentUser);

            // If there is no defined scenario or there is no defined conversation we need to select an opening intent
            if ($this->currentScenarioId() == Scenario::UNDEFINED || $this->currentConversationId() == Conversation::UNDEFINED) {
                $requestIntent = OpeningIntentSelectorStrategy::selectOpeningIntent();
                $this->updateState($requestIntent);
            }

            // Instead if we do have a conversation then we need to match to a request intent from within the conversation
            if ($this->currentConversationId() != Conversation::UNDEFINED) {
                $requestIntent = MatchRequestIntentStartingFromConversationStrategy::matchRequestIntent(
                    $this->currentScenarioId(), $this->currentConversationId(), $this->currentTurnId(), $this->currentIntentId());
                $this->updateState($requestIntent);
            }

            ActionPerformer::performActionsForIntent($requestIntent);

            // With a requestIntent in place we now go to select a responseIntent (or intents)
            $responseIntent = ResponseIntentSelector::getResponseIntentForRequestIntent($requestIntent);
            isset($responseIntent) ? $responseIntents->addObject($responseIntent) : null;

            // If we got here and the response intents its a no-match
            if ($responseIntents->isEmpty()) {
                throw new NoMatchingIntentsException();
            }
        } catch (NoMatchingIntentsException $e) {
            $responseIntents = $this->createNoMatchIntentCollection();
        }

        ActionPerformer::performActionsForIntents($responseIntents);

        return $responseIntents;
    }


    /**
     * @param UtteranceAttribute $utterance
     * @return Attribute
     * @throws NoMatchingIntentsException
     */
    protected function getCurrentUser(UtteranceAttribute $utterance): Attribute
    {
        try {
            // The UtteranceReasoner uses the incoming utterance and returns an appropriate User attribute.
            return UtteranceReasoner::analyseUtterance($utterance);
        } catch (CouldNotCreateUserFromUtteranceException $e) {
            Log::error($e->getMessage());
            throw new NoMatchingIntentsException();
        }
    }

    protected function updateState(Intent $intent)
    {
        // Now that we are set with a final intent let us update the conversation context to reflect where we are
        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Scenario::CURRENT_SCENARIO,
            $intent->getScenario()->getODId()
        );
        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Conversation::CURRENT_CONVERSATION,
            $intent->getConversation()->getODId()
        );
        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Scene::CURRENT_SCENE,
            $intent->getScene()->getODId()
        );
        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Turn::CURRENT_TURN,
            $intent->getTurn()->getODId()
        );
        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Intent::CURRENT_INTENT,
            $intent->getODId()
        );
        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Intent::CURRENT_SPEAKER,
            $intent->getSpeaker()
        );
    }

    protected function createNoMatchIntentCollection(): IntentCollection
    {
        return new IntentCollection([Intent::createNoMatchIntent()]);
    }


    // Helper functions to improve code readability

    protected function currentScenarioId()
    {
        return ContextService::getAttribute(Scenario::CURRENT_SCENARIO, self::CONVERSATION_CONTEXT);
    }

    protected function currentConversationId()
    {
        return ContextService::getAttribute(Conversation::CURRENT_CONVERSATION, self::CONVERSATION_CONTEXT);
    }

    protected function currentTurnId()
    {
        return ContextService::getAttribute(Turn::CURRENT_TURN, self::CONVERSATION_CONTEXT);
    }

    protected function currentIntentId()
    {
        return ContextService::getAttribute(Intent::CURRENT_INTENT, self::CONVERSATION_CONTEXT);
    }

}
