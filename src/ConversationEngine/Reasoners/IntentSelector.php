<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\IntentCollection;

/**
 * The TurnSelector should evaluate conditions against turns to select
 * which turns can validly be considered for a user
 */
class IntentSelector
{
    public static function selectStartingIntents($turns): IntentCollection
    {
        // These are all the possible intents that could start a conversation
        /** @var IntentCollection $intents */
        $intents = ConversationDataClient::getAllStartingIntents($turns);

        // Now we can pass each intent through interpreters and interpret given the utterance
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);
        $matchingIntents = IntentInterpreterFilter::filter($intents, $utterance);

        // We first reduce the set to just those that have passing conditions
        /** @var IntentCollection $intentsWithPassingConditions */
        $intentsWithPassingConditions = ConditionFilter::filterObjects($matchingIntents);

        return $intentsWithPassingConditions;
    }
}
