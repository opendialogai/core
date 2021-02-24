<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * The ConversationSelector should evaluate conditions against conversations to select
 * which scenarios can validly be considered for the current user
 */
class ConversationSelector
{
    public static function selectStartingConversations(ScenarioCollection $scenarios): ConversationCollection
    {
        /** @var ConversationCollection $conversations */
        $conversations = ConversationDataClient::getAllStartingConversations($scenarios);

        /** @var ConversationCollection $conversationsWithPassingConditions */
        $conversationsWithPassingConditions = ConditionFilter::filterObjects($conversations);

        return $conversationsWithPassingConditions;
    }

}
