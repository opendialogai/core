<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\Facades\Log;
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
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class ConversationEngine implements ConversationEngineInterface
{
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
        } catch (NoMatchingIntentsException $e) {
            Log::debug('No incoming intent matched, generating no-match intent');
        }

        self::updateState($incomingIntent);
        ActionPerformer::performActionsForIntent($incomingIntent);

        try {
            $outgoingIntent = OutgoingIntentMatcher::matchOutgoingIntent();
            $outgoingIntents->addObject($outgoingIntent);
        } catch (NoMatchingIntentsException $e) {
            Log::debug('No outgoing intent matched');
        }

        if ($outgoingIntents->isNotEmpty()) {
            $outgoingIntent = $outgoingIntents->last();
            self::updateState($outgoingIntent);
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

    public static function updateState(Intent $intent)
    {
        $conversationContextId = ConversationContext::getComponentId();

        $conversationId = $intent->getConversation()->getODId();
        $sceneId = $intent->getScene()->getODId();
        $turnId = $intent->getTurn()->getODId();
        $intentId = $intent->getODId();

        if ($intent->getBehaviors()->hasBehavior(Behavior::COMPLETING_BEHAVIOR)) {
            $conversationId = Conversation::UNDEFINED;
            $sceneId = Scene::UNDEFINED;
            $turnId = Turn::UNDEFINED;
            $intentId = Intent::UNDEFINED;
        }

        ContextService::saveAttribute(
            $conversationContextId .'.'.Scenario::CURRENT_SCENARIO,
            $intent->getScenario()->getODId()
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Conversation::CURRENT_CONVERSATION,
            $conversationId
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Scene::CURRENT_SCENE,
            $sceneId
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Turn::CURRENT_TURN,
            $turnId
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_INTENT,
            $intentId
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::INTENT_IS_REQUEST,
            $intent->isRequestIntent()
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            $intent->getSpeaker()
        );
    }
}
