<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\TurnCollection;

/**
 * The TurnSelector should evaluate conditions against turns to select
 * which turns can validly be considered for a user
 */
class TurnSelector
{
    public static function selectStartingTurns($scenes): TurnCollection
    {
        /** @var TurnCollection $turns */
        $turns = ConversationDataClient::getAllStartingTurns($scenes);

        /** @var TurnCollection $turnsWithPassingConditions */
        $turnsWithPassingConditions = ConditionFilter::filterObjects($turns);

        return $turnsWithPassingConditions;
    }
}
