<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\IntentCollection;

/**
 * The TurnSelector should evaluate conditions against turns to select
 * which turns can validly be considered for a user
 */
class StartingIntentSelector
{
    public static function selectStartingIntents($turns): IntentCollection
    {
        $intents = ConversationDataClient::getAllStartingIntents($turns);

        $conditionPassingIntents = $intents->filter(function ($intent) {
            ConditionFilter::checkConditions($intent);
        });

        $interpreterMatchingIntents = $intents->filter(function ($intent) {
            IntentFilter::matchIntent($intent);
        });

        return $interpreterMatchingIntents;
    }
}
