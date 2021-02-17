<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Facades\InterpreterService;

/**
 * The TurnSelector should evaluate conditions against turns to select
 * which turns can validly be considered for a user
 */
class StartingIntentSelector
{
    public static function selectStartingIntents($turns): IntentCollection
    {
        // These are all the possible intents that could start a conversation
        $intents = ConversationDataClient::getAllStartingIntents($turns);

        // Now we can pass each intent through interpreters and interpret given the utterance
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);
        $matchingIntents = IntentInterpreterFilter::filter($conditionPassingIntents, $utterance);

        // We first reduce the set to just those that have passing conditions
        $conditionPassingIntents = $matchingIntents->filter(function ($intent) {
            return ConditionFilter::checkConditions($intent);
        });

        return $conditionPassingIntents;
    }
}
