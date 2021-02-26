<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\CouldNotCreateUserFromUtteranceException;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Reasoners\ActionPerformer;
use OpenDialogAi\ConversationEngine\Reasoners\ConversationalStateReasoner;
use OpenDialogAi\ConversationEngine\Reasoners\IncomingIntentMatcher;
use OpenDialogAi\ConversationEngine\Reasoners\OutgoingIntentMatcher;
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
        $incomingIntent = Intent::createNoMatchIntent();
        $outgoingIntents = new IntentCollection();

        try {
            /** @var UserAttribute $currentUser */
            $currentUser = $this->getCurrentUser($utterance);

            // The ConversationStateReasoner updates the Conversation Context to reflect the current state of the user.
            ConversationalStateReasoner::determineConversationalStateForUser($currentUser);

            $incomingIntent = IncomingIntentMatcher::matchIncomingIntent();
            $this->updateState($incomingIntent);
        } catch (NoMatchingIntentsException $e) {
            Log::debug('No incoming intent matched, generating no-match intent');
        }

        ActionPerformer::performActionsForIntent($incomingIntent);

        try {
            $outgoingIntent = OutgoingIntentMatcher::matchOutgoingIntent();
            $outgoingIntents->addObject($outgoingIntent);
        } catch (NoMatchingIntentsException $e) {
            Log::debug('No outgoing intent matched');
        }

        if ($outgoingIntents->isNotEmpty()) {
            $outgoingIntent = $outgoingIntents->last();
            $this->updateState($outgoingIntent);
            ActionPerformer::performActionsForIntent($outgoingIntent);
        }

        return $outgoingIntents;
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
        $conversationContextId = ConversationContext::getComponentId();

        ContextService::saveAttribute(
            $conversationContextId .'.'.Scenario::CURRENT_SCENARIO,
            $intent->getScenario()->getODId()
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Conversation::CURRENT_CONVERSATION,
            $intent->getConversation()->getODId()
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Scene::CURRENT_SCENE,
            $intent->getScene()->getODId()
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Turn::CURRENT_TURN,
            $intent->getTurn()->getODId()
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_INTENT,
            $intent->getODId()
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            $intent->getSpeaker()
        );
    }
}
